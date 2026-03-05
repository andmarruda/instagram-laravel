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

    /**
     * Build the API payload for creating a carousel item container.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        $urlKey  = $this->mediaType === MediaType::Image ? 'image_url' : 'video_url';
        $payload = array_merge($this->options, [
            $urlKey            => $this->url,
            'is_carousel_item' => true,
        ]);

        if ($this->mediaType !== MediaType::Image) {
            $payload['media_type'] = $this->mediaType->value;
        }

        return $payload;
    }
}
