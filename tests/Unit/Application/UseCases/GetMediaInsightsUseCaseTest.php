<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Application\UseCases;

use Andmarruda\InstagramLaravel\Application\UseCases\GetMediaInsightsUseCase;
use Andmarruda\InstagramLaravel\Domain\Contracts\InsightsClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaMetric;
use PHPUnit\Framework\TestCase;

class GetMediaInsightsUseCaseTest extends TestCase
{
    public function test_delegates_to_insights_client(): void
    {
        $metrics = [MediaMetric::Impressions, MediaMetric::Reach];

        $expected = [
            InsightMetric::fromApiResponse([
                'name' => 'impressions', 'period' => 'lifetime',
                'values' => [['value' => 100]], 'title' => 'Impressions',
                'description' => '', 'id' => 'media_id/insights/impressions/lifetime',
            ]),
        ];

        $client = $this->createMock(InsightsClientInterface::class);
        $client->expects($this->once())
            ->method('getMediaInsights')
            ->with('media_id', 'token_xyz', $metrics)
            ->willReturn($expected);

        $useCase = new GetMediaInsightsUseCase($client);
        $result  = $useCase->execute('media_id', 'token_xyz', $metrics);

        $this->assertSame($expected, $result);
    }

    public function test_returns_empty_array_when_no_insights(): void
    {
        $client = $this->createMock(InsightsClientInterface::class);
        $client->expects($this->once())
            ->method('getMediaInsights')
            ->with('media_id', 'token', [MediaMetric::Likes])
            ->willReturn([]);

        $useCase = new GetMediaInsightsUseCase($client);
        $result  = $useCase->execute('media_id', 'token', [MediaMetric::Likes]);

        $this->assertSame([], $result);
    }
}
