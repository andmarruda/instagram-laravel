<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\ContentPublishingClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\ContainerStatus;

final class CheckContainerStatusUseCase
{
    public function __construct(
        private readonly ContentPublishingClientInterface $publishingClient,
    ) {}

    public function execute(string $containerId, string $accessToken): ContainerStatus
    {
        return $this->publishingClient->getContainerStatus($containerId, $accessToken);
    }
}
