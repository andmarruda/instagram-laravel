<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\ContentPublishingClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\PublishingLimit;

final class GetPublishingLimitUseCase
{
    public function __construct(
        private readonly ContentPublishingClientInterface $publishingClient,
    ) {}

    public function execute(string $igId, string $accessToken): PublishingLimit
    {
        return $this->publishingClient->getPublishingLimit($igId, $accessToken);
    }
}
