<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Infrastructure\Http\Exceptions;

use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramPublishingException;
use PHPUnit\Framework\TestCase;

class InstagramPublishingExceptionTest extends TestCase
{
    public function test_from_api_response_maps_all_fields(): void
    {
        $error = [
            'message'          => 'The image size is too large.',
            'type'             => 'OAuthException',
            'code'             => 36000,
            'error_subcode'    => 2207004,
            'is_transient'     => false,
            'error_user_title' => 'Image size too large',
            'error_user_msg'   => 'The image is too large to download. It should be less than 8 MiB.',
        ];

        $exception = InstagramPublishingException::fromApiResponse($error);

        $this->assertSame('The image size is too large.', $exception->getMessage());
        $this->assertSame(36000, $exception->getCode());
        $this->assertSame(2207004, $exception->errorSubcode);
        $this->assertFalse($exception->isTransient);
        $this->assertSame('Image size too large', $exception->userTitle);
        $this->assertFalse($exception->isRetryable());
        $this->assertFalse($exception->requiresNewContainer());
    }

    public function test_download_timeout_requires_new_container(): void
    {
        $exception = InstagramPublishingException::fromApiResponse([
            'message'       => 'Download timeout.',
            'code'          => -2,
            'error_subcode' => 2207003,
            'is_transient'  => false,
        ]);

        $this->assertTrue($exception->requiresNewContainer());
        $this->assertFalse($exception->isRetryable());
    }

    public function test_server_error_is_retryable(): void
    {
        $exception = InstagramPublishingException::fromApiResponse([
            'message'       => 'Instagram server error.',
            'code'          => -1,
            'error_subcode' => 2207001,
            'is_transient'  => false,
        ]);

        $this->assertTrue($exception->isRetryable());
        $this->assertFalse($exception->requiresNewContainer());
    }

    public function test_transient_error_is_retryable(): void
    {
        $exception = InstagramPublishingException::fromApiResponse([
            'message'      => 'Transient error.',
            'code'         => 0,
            'is_transient' => true,
        ]);

        $this->assertTrue($exception->isRetryable());
    }
}
