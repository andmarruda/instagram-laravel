<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Infrastructure\Http;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaType;
use Andmarruda\InstagramLaravel\Infrastructure\Http\InstagramContentPublishingHttpAdapter;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class InstagramContentPublishingHttpAdapterTest extends TestCase
{
    public function test_create_image_container_sends_documented_request_parameters(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://graph.instagram.com/v21.0/ig-123/media',
                $this->callback(function (array $options): bool {
                    return ! isset($options['json'])
                        && ! isset($options['headers'])
                        && $options['query'] === [
                            'access_token' => 'token-abc',
                            'caption'      => 'Hello',
                            'user_tags'    => '[{"username":"anderson","x":0.5,"y":0.5}]',
                            'image_url'    => 'https://cdn.example.com/photo.jpg',
                        ];
                }),
            )
            ->willReturn(new Response(body: '{"id":"container-123"}'));

        $adapter = new InstagramContentPublishingHttpAdapter($client);

        $containerId = $adapter->createImageContainer('ig-123', 'token-abc', 'https://cdn.example.com/photo.jpg', [
            'caption'   => 'Hello',
            'user_tags' => [
                ['username' => 'anderson', 'x' => 0.5, 'y' => 0.5],
            ],
        ]);

        $this->assertSame('container-123', $containerId);
    }

    public function test_create_video_container_serializes_boolean_parameters(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://graph.instagram.com/v21.0/ig-123/media',
                $this->callback(function (array $options): bool {
                    return $options['query'] === [
                        'access_token'  => 'token-abc',
                        'share_to_feed' => 'true',
                        'video_url'     => 'https://cdn.example.com/reel.mp4',
                        'media_type'    => 'REELS',
                    ];
                }),
            )
            ->willReturn(new Response(body: '{"id":"container-456"}'));

        $adapter = new InstagramContentPublishingHttpAdapter($client);

        $containerId = $adapter->createVideoContainer(
            'ig-123',
            'token-abc',
            'https://cdn.example.com/reel.mp4',
            MediaType::Reels,
            ['share_to_feed' => true],
        );

        $this->assertSame('container-456', $containerId);
    }

    public function test_publish_container_sends_creation_id_as_request_parameter(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://graph.instagram.com/v21.0/ig-123/media_publish',
                [
                    'query' => [
                        'access_token' => 'token-abc',
                        'creation_id'  => 'container-123',
                    ],
                ],
            )
            ->willReturn(new Response(body: '{"id":"media-123"}'));

        $adapter = new InstagramContentPublishingHttpAdapter($client);

        $mediaId = $adapter->publishContainer('ig-123', 'token-abc', 'container-123');

        $this->assertSame('media-123', $mediaId);
    }
}
