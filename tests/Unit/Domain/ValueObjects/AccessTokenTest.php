<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Domain\ValueObjects;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
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
