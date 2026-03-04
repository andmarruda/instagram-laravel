<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

enum ContainerStatus: string
{
    case Expired    = 'EXPIRED';
    case Error      = 'ERROR';
    case Finished   = 'FINISHED';
    case InProgress = 'IN_PROGRESS';
    case Published  = 'PUBLISHED';

    public function isReadyToPublish(): bool
    {
        return $this === self::Finished;
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Expired, self::Error, self::Published => true,
            default => false,
        };
    }
}
