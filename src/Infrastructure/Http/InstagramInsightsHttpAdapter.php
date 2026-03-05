<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Infrastructure\Http;

use Andmarruda\InstagramLaravel\Domain\Contracts\InsightsClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccountMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightPeriod;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaMetric;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Concerns\GraphApiTrait;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramInsightsException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

final class InstagramInsightsHttpAdapter implements InsightsClientInterface
{
    use GraphApiTrait;

    public function __construct(
        private readonly ClientInterface $httpClient,
    ) {}

    public function getAccountInsights(
        string $igId,
        string $accessToken,
        array $metrics,
        InsightPeriod $period,
        array $options = [],
    ): array {
        try {
            $response = $this->httpClient->request('GET', $this->url("{$igId}/insights"), [
                'headers' => $this->buildHeaders($accessToken),
                'query'   => array_merge($options, [
                    'metric' => AccountMetric::toString($metrics),
                    'period' => $period->value,
                ]),
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return InsightMetric::collectionFromApiResponse($body);
        } catch (GuzzleException $e) {
            throw new InstagramInsightsException(
                'Failed to get account insights: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    public function getMediaInsights(
        string $mediaId,
        string $accessToken,
        array $metrics,
    ): array {
        try {
            $response = $this->httpClient->request('GET', $this->url("{$mediaId}/insights"), [
                'headers' => $this->buildHeaders($accessToken),
                'query'   => ['metric' => MediaMetric::toString($metrics)],
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return InsightMetric::collectionFromApiResponse($body);
        } catch (GuzzleException $e) {
            throw new InstagramInsightsException(
                'Failed to get media insights: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /** @throws InstagramInsightsException */
    private function assertNoError(array $body): void
    {
        if (isset($body['error'])) {
            $error   = $body['error'];
            $message = $error['message'] ?? 'Unknown Instagram Insights error';
            $code    = (int) ($error['code'] ?? 0);

            throw new InstagramInsightsException($message, $code);
        }
    }
}
