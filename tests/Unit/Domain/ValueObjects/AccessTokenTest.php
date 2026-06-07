<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Domain\ValueObjects;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    public function test_from_short_lived_response_with_data_wrapper(): void
    {
        $response = [
            'data' => [[
                'access_token' => 'EAACEdEose0abc',
                'user_id'      => '102012345',
                'permissions'  => 'instagram_business_basic,instagram_business_content_publish',
            ]],
        ];

        $token = AccessToken::fromShortLivedResponse($response);

        $this->assertSame('EAACEdEose0abc', $token->token);
        $this->assertSame('102012345', $token->userId);
        $this->assertSame('bearer', $token->tokenType);
        $this->assertNull($token->expiresIn);
        $this->assertSame(
            ['instagram_business_basic', 'instagram_business_content_publish'],
            $token->permissions
        );
    }

    public function test_from_short_lived_response_without_data_wrapper(): void
    {
        $response = [
            'access_token' => 'EAACEdEose0xyz',
            'user_id'      => '999',
            'permissions'  => 'instagram_business_basic',
        ];

        $token = AccessToken::fromShortLivedResponse($response);

        $this->assertSame('EAACEdEose0xyz', $token->token);
        $this->assertSame('999', $token->userId);
    }

    public function test_from_short_lived_response_with_permissions_array(): void
    {
        $token = AccessToken::fromShortLivedResponse([
            'access_token' => 'token',
            'user_id'      => '123',
            'permissions'  => [
                'instagram_business_basic',
                '',
                null,
                'instagram_business_content_publish',
            ],
        ]);

        $this->assertSame(
            ['instagram_business_basic', 'instagram_business_content_publish'],
            $token->permissions
        );
    }

    public function test_from_short_lived_response_trims_csv_permissions(): void
    {
        $token = AccessToken::fromShortLivedResponse([
            'access_token' => 'token',
            'user_id'      => '123',
            'permissions'  => 'instagram_business_basic, , instagram_business_content_publish',
        ]);

        $this->assertSame(
            ['instagram_business_basic', 'instagram_business_content_publish'],
            $token->permissions
        );
    }

    public function test_from_short_lived_response_with_missing_or_invalid_permissions(): void
    {
        $withoutPermissions = AccessToken::fromShortLivedResponse([
            'access_token' => 'token',
            'user_id'      => '123',
        ]);
        $withInvalidPermissions = AccessToken::fromShortLivedResponse([
            'access_token' => 'token',
            'user_id'      => '123',
            'permissions'  => 123,
        ]);

        $this->assertSame([], $withoutPermissions->permissions);
        $this->assertSame([], $withInvalidPermissions->permissions);
    }

    public function test_from_short_lived_response_requires_access_token(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('access_token');

        AccessToken::fromShortLivedResponse(['user_id' => '123']);
    }

    public function test_from_short_lived_response_requires_user_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('user_id');

        AccessToken::fromShortLivedResponse(['access_token' => 'token']);
    }

    public function test_from_long_lived_response(): void
    {
        $response = [
            'access_token' => 'EAACEdEose0long',
            'token_type'   => 'bearer',
            'expires_in'   => 5183944,
        ];

        $token = AccessToken::fromLongLivedResponse($response, '123', ['instagram_business_basic']);

        $this->assertSame('EAACEdEose0long', $token->token);
        $this->assertSame(5183944, $token->expiresIn);
        $this->assertSame(['instagram_business_basic'], $token->permissions);
        $this->assertFalse($token->isExpired());
    }

    public function test_token_is_not_expired_when_expires_in_is_null(): void
    {
        $token = AccessToken::fromShortLivedResponse([
            'access_token' => 'abc',
            'user_id'      => '1',
        ]);

        $this->assertNull($token->expiresIn);
        $this->assertNull($token->expiresAt());
        $this->assertFalse($token->isExpired());
    }

    public function test_expires_at_is_correctly_calculated(): void
    {
        $response = [
            'access_token' => 'abc',
            'token_type'   => 'bearer',
            'expires_in'   => 3600,
        ];

        $before = new \DateTimeImmutable();
        $token  = AccessToken::fromLongLivedResponse($response);
        $after  = new \DateTimeImmutable();

        $expiresAt = $token->expiresAt();

        $this->assertNotNull($expiresAt);
        $this->assertGreaterThanOrEqual($before->modify('+3600 seconds'), $expiresAt);
        $this->assertLessThanOrEqual($after->modify('+3600 seconds'), $expiresAt);
    }
}
