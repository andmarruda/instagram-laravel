<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\CommentModerationClientInterface;

final class ReplyToCommentUseCase
{
    public function __construct(
        private readonly CommentModerationClientInterface $commentClient,
    ) {}

    /**
     * @return string ID of the newly created reply comment
     */
    public function execute(string $commentId, string $accessToken, string $message): string
    {
        return $this->commentClient->replyToComment($commentId, $accessToken, $message);
    }
}
