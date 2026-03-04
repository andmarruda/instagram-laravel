<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

enum MediaType: string
{
    case Image    = 'IMAGE';
    case Video    = 'VIDEO';
    case Reels    = 'REELS';
    case Stories  = 'STORIES';
    case Carousel = 'CAROUSEL';
}
