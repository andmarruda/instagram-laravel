<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\OAuthClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;

final class ExchangeCodeForTokenUseCase
{
    public function __construct(
        private readonly OAuthClientInterface $oauthClient,
    ) {}

    /**
     * Exchange a one-time authorization code for a short-lived access token.
     *
     * The code received from Instagram's redirect may contain a trailing "#_"
     * which is stripped automatically by the adapter.
     */
    public function execute(string $code, string $redirectUri): AccessToken
    {
        return $this->oauthClient->exchangeCodeForToken($code, $redirectUri);
    }
}
