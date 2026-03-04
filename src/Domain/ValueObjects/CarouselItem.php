<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

final class CarouselItem
{
    private function __construct(
        public readonly string $url,
        public readonly MediaType $mediaType,
        public readonly array $options,
    ) {}

    public static function image(string $imageUrl, array $options = []): self
    {
        return new self(url: $imageUrl, mediaType: MediaType::Image, options: $options);
    }

    public static function video(string $videoUrl, array $options = []): self
    {
        return new self(url: $videoUrl, mediaType: MediaType::Video, options: $options);
    }
}
