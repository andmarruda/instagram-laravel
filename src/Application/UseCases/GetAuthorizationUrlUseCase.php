<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\OAuthClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;

final class GetAuthorizationUrlUseCase
{
    public function __construct(
        private readonly OAuthClientInterface $oauthClient,
    ) {}

    /**
     * @param  Scope[]  $scopes
     * @param  array<string, mixed>  $options
     */
    public function execute(string $redirectUri, array $scopes, array $options = []): string
    {
        return $this->oauthClient->buildAuthorizationUrl($redirectUri, $scopes, $options);
    }
}
