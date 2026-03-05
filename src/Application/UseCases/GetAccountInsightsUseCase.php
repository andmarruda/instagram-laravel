<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\InsightsClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccountMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightPeriod;

final class GetAccountInsightsUseCase
{
    public function __construct(
        private readonly InsightsClientInterface $insightsClient,
    ) {}

    /**
     * @param  AccountMetric[]  $metrics
     * @param  array<string, mixed>  $options  Optional: since, until
     *
     * @return InsightMetric[]
     */
    public function execute(
        string $igId,
        string $accessToken,
        array $metrics,
        InsightPeriod $period,
        array $options = [],
    ): array {
        return $this->insightsClient->getAccountInsights($igId, $accessToken, $metrics, $period, $options);
    }
}
