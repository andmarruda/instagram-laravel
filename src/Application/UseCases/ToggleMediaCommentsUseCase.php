<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\CommentModerationClientInterface;

final class ToggleMediaCommentsUseCase
{
    public function __construct(
        private readonly CommentModerationClientInterface $commentClient,
    ) {}

    public function execute(string $mediaId, string $accessToken, bool $enabled): bool
    {
        return $this->commentClient->toggleMediaComments($mediaId, $accessToken, $enabled);
    }
}
