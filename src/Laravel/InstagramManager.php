<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Laravel;

use Andmarruda\InstagramLaravel\Application\UseCases\CheckContainerStatusUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\CreateCarouselContainerUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\CreateImageContainerUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\CreateVideoContainerUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\ExchangeCodeForTokenUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetAuthorizationUrlUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetLongLivedTokenUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetPublishingLimitUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\PublishContainerUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\RefreshLongLivedTokenUseCase;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\CarouselItem;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\ContainerStatus;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaType;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\PublishingLimit;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;

final class InstagramManager
{
    public function __construct(
        // OAuth
        private readonly GetAuthorizationUrlUseCase $authorizationUrlUseCase,
        private readonly ExchangeCodeForTokenUseCase $exchangeCodeForTokenUseCase,
        private readonly GetLongLivedTokenUseCase $getLongLivedTokenUseCase,
        private readonly RefreshLongLivedTokenUseCase $refreshLongLivedTokenUseCase,
        // Publishing
        private readonly CreateImageContainerUseCase $createImageContainerUseCase,
        private readonly CreateVideoContainerUseCase $createVideoContainerUseCase,
        private readonly CreateCarouselContainerUseCase $createCarouselContainerUseCase,
        private readonly CheckContainerStatusUseCase $checkContainerStatusUseCase,
        private readonly PublishContainerUseCase $publishContainerUseCase,
        private readonly GetPublishingLimitUseCase $getPublishingLimitUseCase,
    ) {}

    // =========================================================================
    // OAuth
    // =========================================================================

    /**
     * @param  Scope[]|null  $scopes  Defaults to config('instagram.scopes') if null.
     * @param  array<string, mixed>  $options
     */
    public function authorizationUrl(?string $redirectUri = null, ?array $scopes = null, array $options = []): string
    {
        $redirectUri = $redirectUri ?? config('instagram.redirect_uri');
        $scopes      = $scopes ?? Scope::fromArray(config('instagram.scopes', [Scope::Basic->value]));

        return $this->authorizationUrlUseCase->execute($redirectUri, $scopes, $options);
    }

    public function exchangeCode(string $code, ?string $redirectUri = null): AccessToken
    {
        return $this->exchangeCodeForTokenUseCase->execute($code, $redirectUri ?? config('instagram.redirect_uri'));
    }

    public function longLivedToken(string $shortLivedToken): AccessToken
    {
        return $this->getLongLivedTokenUseCase->execute($shortLivedToken);
    }

    public function refreshToken(string $longLivedToken): AccessToken
    {
        return $this->refreshLongLivedTokenUseCase->execute($longLivedToken);
    }

    // =========================================================================
    // Content Publishing
    // =========================================================================

    /**
     * Create a container for a single image post.
     *
     * @param  array<string, mixed>  $options  Optional: caption, alt_text, user_tags, location_id
     *
     * @return string Container ID
     */
    public function createImageContainer(
        string $igId,
        string $accessToken,
        string $imageUrl,
        array $options = [],
    ): string {
        return $this->createImageContainerUseCase->execute($igId, $accessToken, $imageUrl, $options);
    }

    /**
     * Create a container for a video, reel, or story post.
     *
     * @param  array<string, mixed>  $options  Optional: caption, thumb_offset, share_to_feed, trial_params
     *
     * @return string Container ID
     */
    public function createVideoContainer(
        string $igId,
        string $accessToken,
        string $videoUrl,
        MediaType $mediaType = MediaType::Video,
        array $options = [],
    ): string {
        return $this->createVideoContainerUseCase->execute($igId, $accessToken, $videoUrl, $mediaType, $options);
    }

    /**
     * Create a carousel container from a list of CarouselItem value objects.
     * Item containers are created automatically.
     *
     * @param  CarouselItem[]  $items  2–10 items
     *
     * @return string Carousel Container ID
     */
    public function createCarouselContainer(
        string $igId,
        string $accessToken,
        array $items,
        string $caption = '',
        array $options = [],
    ): string {
        return $this->createCarouselContainerUseCase->execute($igId, $accessToken, $items, $caption, $options);
    }

    /**
     * Get the current publishing status of a media container.
     * Poll at most once per minute until status is FINISHED or a final state.
     */
    public function containerStatus(string $containerId, string $accessToken): ContainerStatus
    {
        return $this->checkContainerStatusUseCase->execute($containerId, $accessToken);
    }

    /**
     * Publish a media container. The container must have status FINISHED.
     *
     * @return string Published media ID
     */
    public function publish(string $igId, string $accessToken, string $containerId): string
    {
        return $this->publishContainerUseCase->execute($igId, $accessToken, $containerId);
    }

    /**
     * Get the app user's current 24-hour publishing rate limit usage.
     */
    public function publishingLimit(string $igId, string $accessToken): PublishingLimit
    {
        return $this->getPublishingLimitUseCase->execute($igId, $accessToken);
    }
}
