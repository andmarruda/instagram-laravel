<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Application\UseCases;

use Andmarruda\InstagramLaravel\Application\UseCases\CreateCarouselContainerUseCase;
use Andmarruda\InstagramLaravel\Domain\Contracts\ContentPublishingClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\CarouselItem;
use PHPUnit\Framework\TestCase;

class CreateCarouselContainerUseCaseTest extends TestCase
{
    public function test_creates_item_containers_then_carousel_container(): void
    {
        $items = [
            CarouselItem::image('https://example.com/img1.jpg'),
            CarouselItem::image('https://example.com/img2.jpg'),
        ];

        $client = $this->createMock(ContentPublishingClientInterface::class);

        $client->expects($this->exactly(2))
            ->method('createCarouselItemContainer')
            ->willReturnOnConsecutiveCalls('container-1', 'container-2');

        $client->expects($this->once())
            ->method('createCarouselContainer')
            ->with('ig-123', 'token-abc', ['container-1', 'container-2'], 'My caption', [])
            ->willReturn('carousel-container-id');

        $useCase = new CreateCarouselContainerUseCase($client);
        $result  = $useCase->execute('ig-123', 'token-abc', $items, 'My caption');

        $this->assertSame('carousel-container-id', $result);
    }
}
