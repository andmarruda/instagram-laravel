<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\Contracts;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\CarouselItem;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\ContainerStatus;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaType;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\PublishingLimit;

interface ContentPublishingClientInterface
{
    /**
     * Create a media container for a single image.
     *
     * @param  array<string, mixed>  $options  Optional: caption, alt_text, user_tags, location_id, etc.
     *
     * @return string Container ID
     */
    public function createImageContainer(
        string $igId,
        string $accessToken,
        string $imageUrl,
        array $options = [],
    ): string;

    /**
     * Create a media container for a single video, reel, or story.
     *
     * @param  array<string, mixed>  $options  Optional: caption, thumb_offset, share_to_feed, trial_params, etc.
     *
     * @return string Container ID
     */
    public function createVideoContainer(
        string $igId,
        string $accessToken,
        string $videoUrl,
        MediaType $mediaType,
        array $options = [],
    ): string;

    /**
     * Create a carousel item container (image or video with is_carousel_item=true).
     *
     * @return string Container ID
     */
    public function createCarouselItemContainer(
        string $igId,
        string $accessToken,
        CarouselItem $item,
    ): string;

    /**
     * Create a carousel container from a list of child container IDs.
     *
     * @param  string[]  $childrenIds  Up to 10 container IDs
     * @param  array<string, mixed>  $options  Optional: location_id, user_tags, etc.
     *
     * @return string Carousel Container ID
     */
    public function createCarouselContainer(
        string $igId,
        string $accessToken,
        array $childrenIds,
        string $caption = '',
        array $options = [],
    ): string;

    /**
     * Get the publishing status of a media container.
     */
    public function getContainerStatus(string $containerId, string $accessToken): ContainerStatus;

    /**
     * Publish a media container.
     *
     * @return string Published media ID
     */
    public function publishContainer(string $igId, string $accessToken, string $containerId): string;

    /**
     * Get the app user's current 24-hour publishing rate limit usage.
     */
    public function getPublishingLimit(string $igId, string $accessToken): PublishingLimit;
}
