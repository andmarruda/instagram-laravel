<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\ContentPublishingClientInterface;

final class PublishContainerUseCase
{
    public function __construct(
        private readonly ContentPublishingClientInterface $publishingClient,
    ) {}

    /**
     * Publish a media container.
     *
     * The container must have status FINISHED before calling this.
     * Use CheckContainerStatusUseCase to poll the status first.
     *
     * @return string Published media ID
     */
    public function execute(string $igId, string $accessToken, string $containerId): string
    {
        return $this->publishingClient->publishContainer($igId, $accessToken, $containerId);
    }
}
