<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Laravel;

use Andmarruda\InstagramLaravel\Application\UseCases\ExchangeCodeForTokenUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetAuthorizationUrlUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetLongLivedTokenUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\RefreshLongLivedTokenUseCase;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;

final class InstagramManager
{
    public function __construct(
        private readonly GetAuthorizationUrlUseCase $getAuthorizationUrl,
        private readonly ExchangeCodeForTokenUseCase $exchangeCodeForToken,
        private readonly GetLongLivedTokenUseCase $getLongLivedToken,
        private readonly RefreshLongLivedTokenUseCase $refreshLongLivedToken,
    ) {}

    /**
     * Build the authorization URL to redirect users to Instagram's OAuth window.
     *
     * @param  Scope[]|null  $scopes  Defaults to config('instagram.scopes') if null.
     * @param  array<string, mixed>  $options
     */
    public function authorizationUrl(?string $redirectUri = null, ?array $scopes = null, array $options = []): string
    {
        $redirectUri = $redirectUri ?? config('instagram.redirect_uri');
        $scopes      = $scopes ?? Scope::fromArray(config('instagram.scopes', [Scope::Basic->value]));

        return $this->getAuthorizationUrl->execute($redirectUri, $scopes, $options);
    }

    /**
     * Exchange an authorization code for a short-lived access token.
     */
    public function exchangeCode(string $code, ?string $redirectUri = null): AccessToken
    {
        $redirectUri = $redirectUri ?? config('instagram.redirect_uri');

        return $this->exchangeCodeForToken->execute($code, $redirectUri);
    }

    /**
     * Exchange a short-lived token for a long-lived access token (valid 60 days).
     */
    public function longLivedToken(string $shortLivedToken): AccessToken
    {
        return $this->getLongLivedToken->execute($shortLivedToken);
    }

    /**
     * Refresh a long-lived token for another 60 days.
     */
    public function refreshToken(string $longLivedToken): AccessToken
    {
        return $this->refreshLongLivedToken->execute($longLivedToken);
    }
}
