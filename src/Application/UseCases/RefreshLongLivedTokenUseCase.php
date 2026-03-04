<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\OAuthClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;

final class RefreshLongLivedTokenUseCase
{
    public function __construct(
        private readonly OAuthClientInterface $oauthClient,
    ) {}

    /**
     * Refresh a long-lived token for another 60 days.
     *
     * Requirements (enforced by Instagram):
     * - Token must be at least 24 hours old.
     * - Token must not be expired.
     * - User must have granted instagram_business_basic permission.
     */
    public function execute(string $longLivedToken): AccessToken
    {
        return $this->oauthClient->refreshLongLivedToken($longLivedToken);
    }
}
