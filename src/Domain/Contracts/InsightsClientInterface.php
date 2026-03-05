<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\Contracts;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccountMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightPeriod;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaMetric;

interface InsightsClientInterface
{
    /**
     * Get insights for an Instagram professional account.
     *
     * @param  AccountMetric[]  $metrics
     * @param  array<string, mixed>  $options  Optional: since, until (Unix timestamps or strtotime strings)
     *
     * @return InsightMetric[]
     */
    public function getAccountInsights(
        string $igId,
        string $accessToken,
        array $metrics,
        InsightPeriod $period,
        array $options = [],
    ): array;

    /**
     * Get insights for a specific media object.
     *
     * @param  MediaMetric[]  $metrics
     *
     * @return InsightMetric[]
     */
    public function getMediaInsights(
        string $mediaId,
        string $accessToken,
        array $metrics,
    ): array;
}
