<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

use DateTimeImmutable;

final class AccessToken
{
    private function __construct(
        public readonly string $token,
        public readonly string $userId,
        public readonly string $tokenType,
        public readonly ?int $expiresIn,
        public readonly array $permissions,
        private readonly DateTimeImmutable $createdAt,
    ) {}

    /**
     * Build from the short-lived token API response.
     *
     * Expected shape:
     * {
     *   "data": [{ "access_token": "...", "user_id": "...", "permissions": "scope1,scope2" }]
     * }
     */
    public static function fromShortLivedResponse(array $response): self
    {
        $data = $response['data'][0] ?? $response;

        return new self(
            token: $data['access_token'],
            userId: (string) $data['user_id'],
            tokenType: 'bearer',
            expiresIn: null,
            permissions: isset($data['permissions'])
                ? explode(',', $data['permissions'])
                : [],
            createdAt: new DateTimeImmutable(),
        );
    }

    /**
     * Build from the long-lived / refresh token API response.
     *
     * Expected shape:
     * { "access_token": "...", "token_type": "bearer", "expires_in": 5183944 }
     */
    public static function fromLongLivedResponse(array $response, string $userId = '', array $permissions = []): self
    {
        return new self(
            token: $response['access_token'],
            userId: $userId,
            tokenType: $response['token_type'] ?? 'bearer',
            expiresIn: isset($response['expires_in']) ? (int) $response['expires_in'] : null,
            permissions: $permissions,
            createdAt: new DateTimeImmutable(),
        );
    }

    public function expiresAt(): ?DateTimeImmutable
    {
        if ($this->expiresIn === null) {
            return null;
        }

        return $this->createdAt->modify("+{$this->expiresIn} seconds");
    }

    public function isExpired(): bool
    {
        $expiresAt = $this->expiresAt();

        return $expiresAt !== null && $expiresAt <= new DateTimeImmutable();
    }
}
