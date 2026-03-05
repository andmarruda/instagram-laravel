<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

use DateTimeImmutable;

final class InsightValue
{
    public function __construct(
        public readonly int|float $value,
        public readonly ?DateTimeImmutable $endTime,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            value: $data['value'] ?? 0,
            endTime: isset($data['end_time'])
                ? new DateTimeImmutable($data['end_time'])
                : null,
        );
    }
}
