<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Domain\ValueObjects;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccountMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;
use PHPUnit\Framework\TestCase;

class MetricEnumTest extends TestCase
{
    public function test_scope_to_string_serializes_comma_separated(): void
    {
        $result = Scope::toString([Scope::Basic, Scope::ContentPublish]);

        $this->assertSame('instagram_business_basic,instagram_business_content_publish', $result);
    }

    public function test_account_metric_to_string_serializes_comma_separated(): void
    {
        $result = AccountMetric::toString([AccountMetric::Impressions, AccountMetric::Reach, AccountMetric::ProfileViews]);

        $this->assertSame('impressions,reach,profile_views', $result);
    }

    public function test_media_metric_to_string_serializes_comma_separated(): void
    {
        $result = MediaMetric::toString([MediaMetric::Likes, MediaMetric::Comments, MediaMetric::Shares]);

        $this->assertSame('likes,comments,shares', $result);
    }

    public function test_single_item_produces_no_comma(): void
    {
        $this->assertSame('reach', AccountMetric::toString([AccountMetric::Reach]));
    }
}
