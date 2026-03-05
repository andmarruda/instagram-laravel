<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Laravel\Facades;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccountMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\CarouselItem;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\ContainerStatus;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightPeriod;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaType;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\PublishingLimit;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;
use Andmarruda\InstagramLaravel\Laravel\InstagramManager;
use Illuminate\Support\Facades\Facade;

/**
 * OAuth
 * @method static string      authorizationUrl(?string $redirectUri = null, ?array $scopes = null, array $options = [])
 * @method static AccessToken exchangeCode(string $code, ?string $redirectUri = null)
 * @method static AccessToken longLivedToken(string $shortLivedToken)
 * @method static AccessToken refreshToken(string $longLivedToken)
 *
 * Content Publishing
 * @method static string          createImageContainer(string $igId, string $accessToken, string $imageUrl, array $options = [])
 * @method static string          createVideoContainer(string $igId, string $accessToken, string $videoUrl, MediaType $mediaType = MediaType::Video, array $options = [])
 * @method static string          createCarouselContainer(string $igId, string $accessToken, CarouselItem[] $items, string $caption = '', array $options = [])
 * @method static ContainerStatus containerStatus(string $containerId, string $accessToken)
 * @method static string          publish(string $igId, string $accessToken, string $containerId)
 * @method static PublishingLimit publishingLimit(string $igId, string $accessToken)
 *
 * Insights
 * @method static InsightMetric[] accountInsights(string $igId, string $accessToken, AccountMetric[] $metrics, InsightPeriod $period = InsightPeriod::Day, array $options = [])
 * @method static InsightMetric[] mediaInsights(string $mediaId, string $accessToken, MediaMetric[] $metrics)
 *
 * @see InstagramManager
 */
class Instagram extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return InstagramManager::class;
    }
}
