<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Application\UseCases;

use Andmarruda\InstagramLaravel\Domain\Contracts\CommentModerationClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Comment;

final class GetMediaCommentsUseCase
{
    public function __construct(
        private readonly CommentModerationClientInterface $commentClient,
    ) {}

    /**
     * @return Comment[]
     */
    public function execute(string $mediaId, string $accessToken): array
    {
        return $this->commentClient->getMediaComments($mediaId, $accessToken);
    }
}
