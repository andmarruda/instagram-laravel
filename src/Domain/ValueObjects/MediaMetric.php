<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\Concerns\HasCommaString;

/**
 * Metrics available for Instagram media insights.
 *
 * @see https://developers.facebook.com/docs/instagram-platform/reference/ig-media/insights
 */
enum MediaMetric: string
{
    use HasCommaString;
    case Engagement        = 'engagement';
    case Impressions       = 'impressions';
    case Reach             = 'reach';
    case Saved             = 'saved';
    case VideoViews        = 'video_views';
    case Likes             = 'likes';
    case Comments          = 'comments';
    case Shares            = 'shares';
    case Plays             = 'plays';
    case TotalInteractions = 'total_interactions';
    case Follows           = 'follows';
    case ProfileVisits     = 'profile_visits';


}
