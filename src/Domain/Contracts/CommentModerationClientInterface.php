<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Domain\Contracts;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\Comment;

interface CommentModerationClientInterface
{
    /**
     * Get all comments on a published Instagram media object.
     *
     * @see https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/comment-moderation
     *
     * @return Comment[]
     */
    public function getMediaComments(string $mediaId, string $accessToken): array;

    /**
     * Get all replies on an Instagram comment.
     *
     * @return Comment[]
     */
    public function getCommentReplies(string $commentId, string $accessToken): array;

    /**
     * Reply to an Instagram comment.
     *
     * @return string ID of the newly created reply comment
     */
    public function replyToComment(string $commentId, string $accessToken, string $message): string;

    /**
     * Hide or unhide an Instagram comment.
     */
    public function hideComment(string $commentId, string $accessToken, bool $hide): bool;

    /**
     * Delete an Instagram comment.
     */
    public function deleteComment(string $commentId, string $accessToken): bool;

    /**
     * Enable or disable comments on an Instagram media object.
     */
    public function toggleMediaComments(string $mediaId, string $accessToken, bool $enabled): bool;
}
