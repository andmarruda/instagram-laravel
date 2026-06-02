<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\CommentModerationClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Comment;

final class GetCommentRepliesUseCase
{
    public function __construct(
        private readonly CommentModerationClientInterface $commentClient,
    ) {}

    /**
     * @return Comment[]
     */
    public function execute(string $commentId, string $accessToken): array
    {
        return $this->commentClient->getCommentReplies($commentId, $accessToken);
    }
}
