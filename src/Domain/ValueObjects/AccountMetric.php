<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\Concerns\HasCommaString;

/**
 * Metrics available for Instagram professional account insights.
 *
 * @see https://developers.facebook.com/docs/instagram-platform/reference/ig-user/insights
 */
enum AccountMetric: string
{
    use HasCommaString;
    case Impressions          = 'impressions';
    case Reach                = 'reach';
    case ProfileViews         = 'profile_views';
    case FollowerCount        = 'follower_count';
    case WebsiteClicks        = 'website_clicks';
    case EmailContacts        = 'email_contacts';
    case PhoneCallClicks      = 'phone_call_clicks';
    case TextMessageClicks    = 'text_message_clicks';
    case GetDirectionsClicks  = 'get_directions_clicks';
    case TotalInteractions    = 'total_interactions';
    case AccountsEngaged      = 'accounts_engaged';
    case Likes                = 'likes';
    case Comments             = 'comments';
    case Shares               = 'shares';
    case Saves                = 'saves';


}
