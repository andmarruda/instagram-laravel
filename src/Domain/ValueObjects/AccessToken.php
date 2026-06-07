<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

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
     *   "data": [{
     *     "access_token": "...",
     *     "user_id": "...",
     *     "permissions": "scope1,scope2"|string[]
     *   }]
     * }
     */
    public static function fromShortLivedResponse(array $response): self
    {
        $data = $response['data'][0] ?? $response;
        self::assertRequiredString($data, 'access_token');
        self::assertRequiredUserId($data);

        return new self(
            token: $data['access_token'],
            userId: (string) $data['user_id'],
            tokenType: 'bearer',
            expiresIn: null,
            permissions: self::normalizePermissions($data['permissions'] ?? []),
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
        self::assertRequiredString($response, 'access_token');

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

    private static function normalizePermissions(mixed $permissions): array
    {
        if (is_array($permissions)) {
            return array_values(array_filter(
                $permissions,
                static fn (mixed $permission): bool => is_string($permission) && $permission !== '',
            ));
        }

        if (is_string($permissions)) {
            return array_values(array_filter(array_map('trim', explode(',', $permissions))));
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function assertRequiredString(array $data, string $key): void
    {
        if (! isset($data[$key]) || ! is_string($data[$key]) || $data[$key] === '') {
            throw new InvalidArgumentException("Instagram API response is missing a valid {$key}.");
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function assertRequiredUserId(array $data): void
    {
        if (
            ! isset($data['user_id'])
            || (! is_string($data['user_id']) && ! is_int($data['user_id']))
            || (string) $data['user_id'] === ''
        ) {
            throw new InvalidArgumentException('Instagram API response is missing a valid user_id.');
        }
    }
}
