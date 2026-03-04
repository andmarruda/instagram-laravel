<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\OAuthClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;

final class GetLongLivedTokenUseCase
{
    public function __construct(
        private readonly OAuthClientInterface $oauthClient,
    ) {}

    /**
     * Exchange a valid short-lived token for a long-lived token (valid 60 days).
     */
    public function execute(string $shortLivedToken): AccessToken
    {
        return $this->oauthClient->getLongLivedToken($shortLivedToken);
    }
}
