<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\ContentPublishingClientInterface;

final class CreateImageContainerUseCase
{
    public function __construct(
        private readonly ContentPublishingClientInterface $publishingClient,
    ) {}

    /**
     * @param  array<string, mixed>  $options  Optional: caption, alt_text, user_tags, location_id
     *
     * @return string Container ID
     */
    public function execute(
        string $igId,
        string $accessToken,
        string $imageUrl,
        array $options = [],
    ): string {
        return $this->publishingClient->createImageContainer($igId, $accessToken, $imageUrl, $options);
    }
}
