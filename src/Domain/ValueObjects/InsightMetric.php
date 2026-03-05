<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

final class InsightMetric
{
    /**
     * @param  InsightValue[]  $values
     */
    public function __construct(
        public readonly string $name,
        public readonly string $period,
        public readonly array $values,
        public readonly string $title,
        public readonly string $description,
        public readonly string $id,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            period: $data['period'] ?? '',
            values: array_map(
                fn (array $v) => InsightValue::fromApiResponse($v),
                $data['values'] ?? [],
            ),
            title: $data['title'] ?? '',
            description: $data['description'] ?? '',
            id: $data['id'] ?? '',
        );
    }

    /**
     * Build a collection of InsightMetric from an API response.
     *
     * @return self[]
     */
    public static function collectionFromApiResponse(array $response): array
    {
        return array_map(
            fn (array $item) => self::fromApiResponse($item),
            $response['data'] ?? [],
        );
    }

    /**
     * Return the total aggregated value across all time slots.
     */
    public function total(): int|float
    {
        return array_sum(array_map(fn (InsightValue $v) => $v->value, $this->values));
    }
}
