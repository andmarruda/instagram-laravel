<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

final class PublishingLimit
{
    public function __construct(
        public readonly int $quota24hAllotment,
        public readonly int $configQuota24hAllotment,
        public readonly int $percentUsed,
    ) {}

    public static function fromApiResponse(array $response): self
    {
        $data = $response['data'][0] ?? $response;

        return new self(
            quota24hAllotment: (int) ($data['quota_usage'] ?? 0),
            configQuota24hAllotment: (int) ($data['config']['quota_total'] ?? 100),
            percentUsed: (int) ($data['quota_usage'] ?? 0),
        );
    }

    public function hasReachedLimit(): bool
    {
        return $this->quota24hAllotment >= $this->configQuota24hAllotment;
    }
}
