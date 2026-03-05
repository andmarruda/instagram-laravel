<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Infrastructure\Http\Concerns;

use Psr\Http\Message\StreamInterface;

trait GraphApiTrait
{
    private const BASE_URL    = 'https://graph.instagram.com';
    private const API_VERSION = 'v21.0';

    private function url(string $path): string
    {
        return sprintf('%s/%s/%s', self::BASE_URL, self::API_VERSION, ltrim($path, '/'));
    }

    private function decode(StreamInterface $body): array
    {
        return json_decode((string) $body, true, 512, JSON_THROW_ON_ERROR);
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
}
