<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions;

use RuntimeException;

final class InstagramPublishingException extends RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?int $errorSubcode = null,
        public readonly bool $isTransient = false,
        public readonly ?string $userTitle = null,
        public readonly ?string $userMessage = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromApiResponse(array $error, ?\Throwable $previous = null): self
    {
        return new self(
            message: $error['message'] ?? 'Unknown Instagram publishing error',
            code: (int) ($error['code'] ?? 0),
            errorSubcode: isset($error['error_subcode']) ? (int) $error['error_subcode'] : null,
            isTransient: (bool) ($error['is_transient'] ?? false),
            userTitle: $error['error_user_title'] ?? null,
            userMessage: $error['error_user_msg'] ?? null,
            previous: $previous,
        );
    }

    /**
     * Whether this error can be retried with the same container ID.
     * Based on the Instagram API error code reference.
     */
    public function isRetryable(): bool
    {
        return $this->isTransient || match ($this->errorSubcode) {
            2207001, // Instagram server error
            2207008  // Temporary error publishing a container
                => true,
            default => false,
        };
    }

    /**
     * Whether the caller should generate a new container and retry.
     */
    public function requiresNewContainer(): bool
    {
        return match ($this->errorSubcode) {
            2207003, // Download timeout
            2207020, // Media expired
            2207032, // Create media failed
            2207053, // Unknown upload error
            2207006  // Media not found / permission error
                => true,
            default => false,
        };
    }
}
