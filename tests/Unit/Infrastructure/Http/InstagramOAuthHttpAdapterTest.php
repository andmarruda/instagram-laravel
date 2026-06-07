<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Infrastructure\Http;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramOAuthException;
use Andmarruda\InstagramLaravel\Infrastructure\Http\InstagramOAuthHttpAdapter;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class InstagramOAuthHttpAdapterTest extends TestCase
{
    public function test_exchange_code_converts_missing_access_token_to_oauth_exception(): void
    {
        $adapter = $this->adapterReturning([
            'user_id' => '123',
        ]);

        $this->expectException(InstagramOAuthException::class);
        $this->expectExceptionMessage('access_token');

        $adapter->exchangeCodeForToken('code', 'https://example.com/callback');
    }

    public function test_exchange_code_converts_missing_user_id_to_oauth_exception(): void
    {
        $adapter = $this->adapterReturning([
            'access_token' => 'short-token',
        ]);

        $this->expectException(InstagramOAuthException::class);
        $this->expectExceptionMessage('user_id');

        $adapter->exchangeCodeForToken('code', 'https://example.com/callback');
    }

    public function test_exchange_code_converts_invalid_json_to_oauth_exception(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->method('request')->willReturn(new Response(body: '{invalid'));

        $adapter = new InstagramOAuthHttpAdapter($client, 'client-id', 'client-secret');

        $this->expectException(InstagramOAuthException::class);
        $this->expectExceptionMessage('Failed to exchange code for token');

        $adapter->exchangeCodeForToken('code', 'https://example.com/callback');
    }

    public function test_get_long_lived_token_preserves_short_token_metadata(): void
    {
        $shortToken = AccessToken::fromShortLivedResponse([
            'access_token' => 'short-token',
            'user_id'      => '123',
            'permissions'  => ['instagram_business_basic'],
        ]);
        $adapter = $this->adapterReturning([
            'access_token' => 'long-token',
            'token_type'   => 'bearer',
            'expires_in'   => 5183944,
        ]);

        $longToken = $adapter->getLongLivedToken($shortToken);

        $this->assertSame('long-token', $longToken->token);
        $this->assertSame('123', $longToken->userId);
        $this->assertSame(['instagram_business_basic'], $longToken->permissions);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function adapterReturning(array $body): InstagramOAuthHttpAdapter
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->method('request')
            ->willReturn(new Response(body: json_encode($body, JSON_THROW_ON_ERROR)));

        return new InstagramOAuthHttpAdapter($client, 'client-id', 'client-secret');
    }
}
