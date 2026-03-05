<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\InsightsClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaMetric;

final class GetMediaInsightsUseCase
{
    public function __construct(
        private readonly InsightsClientInterface $insightsClient,
    ) {}

    /**
     * @param  MediaMetric[]  $metrics
     *
     * @return InsightMetric[]
     */
    public function execute(
        string $mediaId,
        string $accessToken,
        array $metrics,
    ): array {
        return $this->insightsClient->getMediaInsights($mediaId, $accessToken, $metrics);
    }
}
