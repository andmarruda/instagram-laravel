<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Infrastructure\Http;

use Andmarruda\InstagramLaravel\Domain\Contracts\CommentModerationClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Comment;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Concerns\GraphApiTrait;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramCommentModerationException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

final class InstagramCommentModerationHttpAdapter implements CommentModerationClientInterface
{
    use GraphApiTrait;

    public function __construct(
        private readonly ClientInterface $httpClient,
    ) {}

    public function getMediaComments(string $mediaId, string $accessToken): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->url("{$mediaId}/comments"), [
                'headers' => $this->buildHeaders($accessToken),
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return Comment::collectionFromApiResponse($body);
        } catch (GuzzleException $e) {
            throw new InstagramCommentModerationException(
                'Failed to get media comments: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    public function getCommentReplies(string $commentId, string $accessToken): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->url("{$commentId}/replies"), [
                'headers' => $this->buildHeaders($accessToken),
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return Comment::collectionFromApiResponse($body);
        } catch (GuzzleException $e) {
            throw new InstagramCommentModerationException(
                'Failed to get comment replies: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    public function replyToComment(string $commentId, string $accessToken, string $message): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->url("{$commentId}/replies"), [
                'headers' => $this->buildHeaders($accessToken, json: true),
                'json'    => ['message' => $message],
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return $body['id'] ?? '';
        } catch (GuzzleException $e) {
            throw new InstagramCommentModerationException(
                'Failed to reply to comment: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    public function hideComment(string $commentId, string $accessToken, bool $hide): bool
    {
        try {
            $response = $this->httpClient->request('POST', $this->url($commentId), [
                'headers' => $this->buildHeaders($accessToken, json: true),
                'json'    => ['hide' => $hide],
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return (bool) ($body['success'] ?? true);
        } catch (GuzzleException $e) {
            throw new InstagramCommentModerationException(
                'Failed to hide/unhide comment: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    public function deleteComment(string $commentId, string $accessToken): bool
    {
        try {
            $response = $this->httpClient->request('DELETE', $this->url($commentId), [
                'headers' => $this->buildHeaders($accessToken),
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return (bool) ($body['success'] ?? true);
        } catch (GuzzleException $e) {
            throw new InstagramCommentModerationException(
                'Failed to delete comment: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    public function toggleMediaComments(string $mediaId, string $accessToken, bool $enabled): bool
    {
        try {
            $response = $this->httpClient->request('POST', $this->url($mediaId), [
                'headers' => $this->buildHeaders($accessToken, json: true),
                'json'    => ['comment_enabled' => $enabled],
            ]);

            $body = $this->decode($response->getBody());
            $this->assertNoError($body);

            return (bool) ($body['success'] ?? true);
        } catch (GuzzleException $e) {
            throw new InstagramCommentModerationException(
                'Failed to toggle comments on media: ' . $e->getMessage(),
                previous: $e,
            );
        }
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /** @throws InstagramCommentModerationException */
    private function assertNoError(array $body): void
    {
        if (isset($body['error'])) {
            $error   = $body['error'];
            $message = $error['message'] ?? 'Unknown Instagram Comment Moderation error';
            $code    = (int) ($error['code'] ?? 0);

            throw new InstagramCommentModerationException($message, $code);
        }
    }
}
