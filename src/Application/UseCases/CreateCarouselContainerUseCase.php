<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\ContentPublishingClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\CarouselItem;

final class CreateCarouselContainerUseCase
{
    public function __construct(
        private readonly ContentPublishingClientInterface $publishingClient,
    ) {}

    /**
     * Create item containers for each media, then wrap them in a carousel container.
     *
     * @param  CarouselItem[]  $items  2–10 items
     * @param  array<string, mixed>  $options  Optional: location_id, user_tags
     *
     * @return string Carousel Container ID
     */
    public function execute(
        string $igId,
        string $accessToken,
        array $items,
        string $caption = '',
        array $options = [],
    ): string {
        $childrenIds = array_map(
            fn (CarouselItem $item) => $this->publishingClient->createCarouselItemContainer($igId, $accessToken, $item),
            $items,
        );

        return $this->publishingClient->createCarouselContainer($igId, $accessToken, $childrenIds, $caption, $options);
    }
}
