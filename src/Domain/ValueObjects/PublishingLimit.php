<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

final class PublishingLimit
{
    public function __construct(
        public readonly int $quotaUsage,
        public readonly int $quotaTotal,
    ) {}

    public static function fromApiResponse(array $response): self
    {
        $data = $response['data'][0] ?? $response;

        return new self(
            quotaUsage: (int) ($data['quota_usage'] ?? 0),
            quotaTotal: (int) ($data['config']['quota_total'] ?? 100),
        );
    }

    public function percentUsed(): int
    {
        if ($this->quotaTotal === 0) {
            return 0;
        }

        return (int) round(($this->quotaUsage / $this->quotaTotal) * 100);
    }

    public function hasReachedLimit(): bool
    {
        return $this->quotaUsage >= $this->quotaTotal;
    }
}
