<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Infrastructure\Http;

use Andmarruda\InstagramLaravel\Domain\Contracts\OAuthClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramOAuthException;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\StreamInterface;
use Throwable;

final class InstagramOAuthHttpAdapter implements OAuthClientInterface
{
    private const AUTH_BASE_URL  = 'https://www.instagram.com/oauth/authorize';
    private const TOKEN_URL      = 'https://api.instagram.com/oauth/access_token';
    private const GRAPH_BASE_URL = 'https://graph.instagram.com';

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    /**
     * @param  Scope[]  $scopes
     * @param  array<string, mixed>  $options
     */
    public function buildAuthorizationUrl(string $redirectUri, array $scopes, array $options = []): string
    {
        $params = array_merge([
            'enable_fb_login'      => '0',
            'force_authentication' => '1',
        ], $options, [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => Scope::toString($scopes),
        ]);

        return self::AUTH_BASE_URL . '?' . http_build_query($params);
    }

    public function exchangeCodeForToken(string $code, string $redirectUri): AccessToken
    {
        // Strip the trailing "#_" that Instagram appends to the redirect URI.
        if (str_ends_with($code, '#_')) {
            $code = substr($code, 0, -2);
        }

        try {
            $response = $this->httpClient->request('POST', self::TOKEN_URL, [
                'form_params' => [
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type'    => 'authorization_code',
                    'redirect_uri'  => $redirectUri,
                    'code'          => $code,
                ],
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return AccessToken::fromShortLivedResponse($body);
        } catch (InstagramOAuthException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new InstagramOAuthException('Failed to exchange code for token: ' . $e->getMessage(), previous: $e);
        }
    }

    public function getLongLivedToken(string|AccessToken $shortLivedToken): AccessToken
    {
        $userId      = $shortLivedToken instanceof AccessToken ? $shortLivedToken->userId : '';
        $permissions = $shortLivedToken instanceof AccessToken ? $shortLivedToken->permissions : [];
        $token       = $shortLivedToken instanceof AccessToken ? $shortLivedToken->token : $shortLivedToken;

        return $this->fetchLongLivedToken('/access_token', [
            'grant_type'    => 'ig_exchange_token',
            'client_secret' => $this->clientSecret,
            'access_token'  => $token,
        ], 'Failed to get long-lived token', $userId, $permissions);
    }

    public function refreshLongLivedToken(string $longLivedToken): AccessToken
    {
        return $this->fetchLongLivedToken('/refresh_access_token', [
            'grant_type'   => 'ig_refresh_token',
            'access_token' => $longLivedToken,
        ], 'Failed to refresh long-lived token');
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function fetchLongLivedToken(
        string $path,
        array $query,
        string $errorPrefix,
        string $userId = '',
        array $permissions = [],
    ): AccessToken
    {
        try {
            $response = $this->httpClient->request('GET', self::GRAPH_BASE_URL . $path, [
                'query' => $query,
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return AccessToken::fromLongLivedResponse($body, $userId, $permissions);
        } catch (InstagramOAuthException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new InstagramOAuthException($errorPrefix . ': ' . $e->getMessage(), previous: $e);
        }
    }

    private function decode(StreamInterface $body): array
    {
        return json_decode((string) $body, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<string, mixed>  $body
     *
     * @throws InstagramOAuthException
     */
    private function assertNoError(array $body): void
    {
        if (isset($body['error']) && is_array($body['error'])) {
            $message = $body['error']['message']
                ?? $body['error']['error_user_msg']
                ?? 'Unknown Instagram API error';
            $code = $body['error']['code'] ?? 0;

            throw new InstagramOAuthException($message, (int) $code);
        }

        if (isset($body['error_type']) || isset($body['error'])) {
            $message = $body['error_message']
                ?? $body['error_description']
                ?? (is_string($body['error'] ?? null) ? $body['error'] : null)
                ?? 'Unknown Instagram API error';
            $code = $body['code'] ?? 0;

            throw new InstagramOAuthException($message, (int) $code);
        }
    }
}
