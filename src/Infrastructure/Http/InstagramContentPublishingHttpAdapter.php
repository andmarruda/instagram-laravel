<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Infrastructure\Http;

use Andmarruda\InstagramLaravel\Domain\Contracts\ContentPublishingClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\CarouselItem;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\ContainerStatus;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaType;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\PublishingLimit;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramPublishingException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;

final class InstagramContentPublishingHttpAdapter implements ContentPublishingClientInterface
{
    private const BASE_URL    = 'https://graph.instagram.com';
    private const API_VERSION = 'v21.0';

    public function __construct(
        private readonly ClientInterface $httpClient,
    ) {}

    public function createImageContainer(
        string $igId,
        string $accessToken,
        string $imageUrl,
        array $options = [],
    ): string {
        return $this->postMedia($igId, $accessToken, array_merge($options, ['image_url' => $imageUrl]));
    }

    public function createVideoContainer(
        string $igId,
        string $accessToken,
        string $videoUrl,
        MediaType $mediaType,
        array $options = [],
    ): string {
        return $this->postMedia($igId, $accessToken, array_merge($options, [
            'video_url'  => $videoUrl,
            'media_type' => $mediaType->value,
        ]));
    }

    public function createCarouselItemContainer(
        string $igId,
        string $accessToken,
        CarouselItem $item,
    ): string {
        return $this->postMedia($igId, $accessToken, $item->toPayload());
    }

    public function createCarouselContainer(
        string $igId,
        string $accessToken,
        array $childrenIds,
        string $caption = '',
        array $options = [],
    ): string {
        $payload = array_merge($options, [
            'media_type' => MediaType::Carousel->value,
            'children'   => implode(',', $childrenIds),
        ]);

        if ($caption !== '') {
            $payload['caption'] = $caption;
        }

        return $this->postMedia($igId, $accessToken, $payload);
    }

    public function getContainerStatus(string $containerId, string $accessToken): ContainerStatus
    {
        try {
            $response = $this->httpClient->request('GET', $this->url($containerId), [
                'headers' => $this->buildHeaders($accessToken),
                'query'   => ['fields' => 'status_code'],
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return ContainerStatus::from($body['status_code']);
        } catch (GuzzleException $e) {
            throw new InstagramPublishingException(
                'Failed to get container status: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    public function publishContainer(string $igId, string $accessToken, string $containerId): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->url("{$igId}/media_publish"), [
                'headers' => $this->buildHeaders($accessToken, json: true),
                'json'    => ['creation_id' => $containerId],
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return (string) $body['id'];
        } catch (GuzzleException $e) {
            throw new InstagramPublishingException(
                'Failed to publish container: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    public function getPublishingLimit(string $igId, string $accessToken): PublishingLimit
    {
        try {
            $response = $this->httpClient->request('GET', $this->url("{$igId}/content_publishing_limit"), [
                'headers' => $this->buildHeaders($accessToken),
                'query'   => ['fields' => 'config,quota_usage'],
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return PublishingLimit::fromApiResponse($body);
        } catch (GuzzleException $e) {
            throw new InstagramPublishingException(
                'Failed to get publishing limit: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private function postMedia(string $igId, string $accessToken, array $payload): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->url("{$igId}/media"), [
                'headers' => $this->buildHeaders($accessToken, json: true),
                'json'    => $payload,
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return (string) $body['id'];
        } catch (GuzzleException $e) {
            throw new InstagramPublishingException(
                'Failed to create media container: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    /** @return array<string, string> */
    private function buildHeaders(string $accessToken, bool $json = false): array
    {
        $headers = ['Authorization' => "Bearer {$accessToken}"];

        if ($json) {
            $headers['Content-Type'] = 'application/json';
        }

        return $headers;
    }

    private function url(string $path): string
    {
        return sprintf('%s/%s/%s', self::BASE_URL, self::API_VERSION, ltrim($path, '/'));
    }

    private function decode(StreamInterface $body): array
    {
        return json_decode((string) $body, true, 512, JSON_THROW_ON_ERROR);
    }

    /** @throws InstagramPublishingException */
    private function assertNoError(array $body): void
    {
        if (isset($body['error'])) {
            throw InstagramPublishingException::fromApiResponse($body['error']);
        }
    }
}
