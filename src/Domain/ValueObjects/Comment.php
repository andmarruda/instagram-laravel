<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

final class Comment
{
    public function __construct(
        public readonly string $id,
        public readonly string $text,
        public readonly string $timestamp,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            id:        $data['id'] ?? '',
            text:      $data['text'] ?? '',
            timestamp: $data['timestamp'] ?? '',
        );
    }

    /**
     * Build a collection of Comment from a paged API response.
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
}
