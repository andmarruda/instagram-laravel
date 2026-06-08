<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Infrastructure\Http;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramOAuthException;
use Andmarruda\InstagramLaravel\Infrastructure\Http\InstagramOAuthHttpAdapter;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class InstagramOAuthHttpAdapterTest extends TestCase
{
    public function test_build_authorization_url_uses_instagram_business_login_parameters(): void
    {
        $adapter = new InstagramOAuthHttpAdapter($this->createMock(ClientInterface::class), 'client-id', 'client-secret');

        $url = $adapter->buildAuthorizationUrl(
            'https://example.com/callback',
            [Scope::Basic, Scope::ContentPublish],
            ['state' => 'csrf-token'],
        );

        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);

        $this->assertSame('https', $parts['scheme']);
        $this->assertSame('www.instagram.com', $parts['host']);
        $this->assertSame('/oauth/authorize', $parts['path']);
        $this->assertSame('0', $query['enable_fb_login']);
        $this->assertSame('1', $query['force_authentication']);
        $this->assertSame('client-id', $query['client_id']);
        $this->assertSame('https://example.com/callback', $query['redirect_uri']);
        $this->assertSame('code', $query['response_type']);
        $this->assertSame('instagram_business_basic,instagram_business_content_publish', $query['scope']);
        $this->assertSame('csrf-token', $query['state']);
    }

    public function test_exchange_code_uses_form_post_and_only_strips_exact_fragment_suffix(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.instagram.com/oauth/access_token',
                [
                    'form_params' => [
                        'client_id'     => 'client-id',
                        'client_secret' => 'client-secret',
                        'grant_type'    => 'authorization_code',
                        'redirect_uri'  => 'https://example.com/callback',
                        'code'          => 'code_',
                    ],
                ],
            )
            ->willReturn(new Response(body: '{"access_token":"short-token","user_id":"123"}'));

        $adapter = new InstagramOAuthHttpAdapter($client, 'client-id', 'client-secret');

        $token = $adapter->exchangeCodeForToken('code_#_', 'https://example.com/callback');

        $this->assertSame('short-token', $token->token);
        $this->assertSame('123', $token->userId);
    }

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

    public function test_get_long_lived_token_uses_instagram_graph_exchange_endpoint(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://graph.instagram.com/access_token',
                [
                    'query' => [
                        'grant_type'    => 'ig_exchange_token',
                        'client_secret' => 'client-secret',
                        'access_token'  => 'short-token',
                    ],
                ],
            )
            ->willReturn(new Response(body: '{"access_token":"long-token","token_type":"bearer","expires_in":5183944}'));

        $adapter = new InstagramOAuthHttpAdapter($client, 'client-id', 'client-secret');

        $token = $adapter->getLongLivedToken('short-token');

        $this->assertSame('long-token', $token->token);
    }

    public function test_refresh_long_lived_token_uses_instagram_graph_refresh_endpoint(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://graph.instagram.com/refresh_access_token',
                [
                    'query' => [
                        'grant_type'   => 'ig_refresh_token',
                        'access_token' => 'long-token',
                    ],
                ],
            )
            ->willReturn(new Response(body: '{"access_token":"refreshed-token","token_type":"bearer","expires_in":5183944}'));

        $adapter = new InstagramOAuthHttpAdapter($client, 'client-id', 'client-secret');

        $token = $adapter->refreshLongLivedToken('long-token');

        $this->assertSame('refreshed-token', $token->token);
    }

    public function test_graph_style_error_response_uses_nested_message(): void
    {
        $adapter = $this->adapterReturning([
            'error' => [
                'message' => 'Invalid OAuth 2.0 Access Token',
                'code'    => 190,
            ],
        ]);

        $this->expectException(InstagramOAuthException::class);
        $this->expectExceptionMessage('Invalid OAuth 2.0 Access Token');
        $this->expectExceptionCode(190);

        $adapter->getLongLivedToken('short-token');
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
