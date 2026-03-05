<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

enum InsightPeriod: string
{
    case Day      = 'day';
    case Week     = 'week';
    case Month    = 'month';
    case Lifetime = 'lifetime';
}
