<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\Contracts;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;

interface OAuthClientInterface
{
    /**
     * Build the authorization URL to redirect the user to Instagram's OAuth window.
     *
     * @param  Scope[]  $scopes
     * @param  array<string, mixed>  $options  Optional query params (state, force_reauth, enable_fb_login)
     */
    public function buildAuthorizationUrl(string $redirectUri, array $scopes, array $options = []): string;

    /**
     * Exchange a one-time authorization code for a short-lived access token.
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): AccessToken;

    /**
     * Exchange a valid short-lived token for a long-lived token (valid 60 days).
     */
    public function getLongLivedToken(string $shortLivedToken): AccessToken;

    /**
     * Refresh a long-lived token for another 60 days.
     */
    public function refreshLongLivedToken(string $longLivedToken): AccessToken;
}
