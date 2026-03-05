<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Application\UseCases;

use Andmarruda\InstagramLaravel\Application\UseCases\GetAccountInsightsUseCase;
use Andmarruda\InstagramLaravel\Domain\Contracts\InsightsClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccountMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightPeriod;
use PHPUnit\Framework\TestCase;

class GetAccountInsightsUseCaseTest extends TestCase
{
    public function test_delegates_to_insights_client(): void
    {
        $metrics = [AccountMetric::Impressions, AccountMetric::Reach];
        $period  = InsightPeriod::Day;

        $expected = [
            InsightMetric::fromApiResponse([
                'name' => 'impressions', 'period' => 'day',
                'values' => [['value' => 32]], 'title' => 'Impressions',
                'description' => '', 'id' => 'ig_id/insights/impressions/day',
            ]),
        ];

        $client = $this->createMock(InsightsClientInterface::class);
        $client->expects($this->once())
            ->method('getAccountInsights')
            ->with('ig_id', 'token_abc', $metrics, $period, [])
            ->willReturn($expected);

        $useCase = new GetAccountInsightsUseCase($client);
        $result  = $useCase->execute('ig_id', 'token_abc', $metrics, $period);

        $this->assertSame($expected, $result);
    }

    public function test_passes_options_to_client(): void
    {
        $options = ['since' => 1700000000, 'until' => 1700086400];

        $client = $this->createMock(InsightsClientInterface::class);
        $client->expects($this->once())
            ->method('getAccountInsights')
            ->with('ig_id', 'token', [AccountMetric::ProfileViews], InsightPeriod::Week, $options)
            ->willReturn([]);

        $useCase = new GetAccountInsightsUseCase($client);
        $useCase->execute('ig_id', 'token', [AccountMetric::ProfileViews], InsightPeriod::Week, $options);
    }
}
