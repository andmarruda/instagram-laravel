<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\CommentModerationClientInterface;

final class HideCommentUseCase
{
    public function __construct(
        private readonly CommentModerationClientInterface $commentClient,
    ) {}

    public function execute(string $commentId, string $accessToken, bool $hide): bool
    {
        return $this->commentClient->hideComment($commentId, $accessToken, $hide);
    }
}
