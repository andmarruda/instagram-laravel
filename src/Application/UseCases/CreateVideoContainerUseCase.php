<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\ContentPublishingClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaType;

final class CreateVideoContainerUseCase
{
    public function __construct(
        private readonly ContentPublishingClientInterface $publishingClient,
    ) {}

    /**
     * @param  array<string, mixed>  $options  Optional: caption, thumb_offset, share_to_feed, trial_params
     *
     * @return string Container ID
     */
    public function execute(
        string $igId,
        string $accessToken,
        string $videoUrl,
        MediaType $mediaType,
        array $options = [],
    ): string {
        return $this->publishingClient->createVideoContainer($igId, $accessToken, $videoUrl, $mediaType, $options);
    }
}
