<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Domain\ValueObjects;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightValue;
use PHPUnit\Framework\TestCase;

class InsightMetricTest extends TestCase
{
    public function test_from_api_response_maps_all_fields(): void
    {
        $data = [
            'name'        => 'impressions',
            'period'      => 'day',
            'values'      => [
                ['value' => 32, 'end_time' => '2018-01-11T08:00:00+0000'],
                ['value' => 45, 'end_time' => '2018-01-12T08:00:00+0000'],
            ],
            'title'       => 'Impressions',
            'description' => 'Total number of times the media object has been seen',
            'id'          => 'media_id/insights/impressions/day',
        ];

        $metric = InsightMetric::fromApiResponse($data);

        $this->assertSame('impressions', $metric->name);
        $this->assertSame('day', $metric->period);
        $this->assertSame('Impressions', $metric->title);
        $this->assertSame('media_id/insights/impressions/day', $metric->id);
        $this->assertCount(2, $metric->values);
        $this->assertInstanceOf(InsightValue::class, $metric->values[0]);
        $this->assertSame(32, $metric->values[0]->value);
    }

    public function test_total_sums_all_values(): void
    {
        $data = [
            'name'   => 'reach',
            'period' => 'day',
            'values' => [
                ['value' => 10],
                ['value' => 20],
                ['value' => 5],
            ],
            'title'       => 'Reach',
            'description' => '',
            'id'          => 'x/insights/reach/day',
        ];

        $metric = InsightMetric::fromApiResponse($data);

        $this->assertSame(35, $metric->total());
    }

    public function test_collection_from_api_response(): void
    {
        $response = [
            'data' => [
                [
                    'name' => 'impressions', 'period' => 'lifetime',
                    'values' => [['value' => 13]], 'title' => 'Impressions',
                    'description' => '', 'id' => 'x/insights/impressions/lifetime',
                ],
                [
                    'name' => 'reach', 'period' => 'lifetime',
                    'values' => [['value' => 8]], 'title' => 'Reach',
                    'description' => '', 'id' => 'x/insights/reach/lifetime',
                ],
            ],
        ];

        $metrics = InsightMetric::collectionFromApiResponse($response);

        $this->assertCount(2, $metrics);
        $this->assertSame('impressions', $metrics[0]->name);
        $this->assertSame('reach', $metrics[1]->name);
    }

    public function test_insight_value_end_time_is_parsed(): void
    {
        $value = InsightValue::fromApiResponse([
            'value'    => 99,
            'end_time' => '2024-06-01T00:00:00+0000',
        ]);

        $this->assertSame(99, $value->value);
        $this->assertNotNull($value->endTime);
        $this->assertSame('2024-06-01', $value->endTime->format('Y-m-d'));
    }

    public function test_insight_value_without_end_time(): void
    {
        $value = InsightValue::fromApiResponse(['value' => 7]);

        $this->assertSame(7, $value->value);
        $this->assertNull($value->endTime);
    }
}
