<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Tests\Unit\Application\UseCases;

use Andmarruda\InstagramLaravel\Application\UseCases\ExchangeCodeForTokenUseCase;
use Andmarruda\InstagramLaravel\Domain\Contracts\OAuthClientInterface;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
use PHPUnit\Framework\TestCase;

class ExchangeCodeForTokenUseCaseTest extends TestCase
{
    public function test_delegates_to_oauth_client(): void
    {
        $expectedToken = AccessToken::fromShortLivedResponse([
            'access_token' => 'EAACEdEose0abc',
            'user_id'      => '123',
            'permissions'  => 'instagram_business_basic',
        ]);

        $oauthClient = $this->createMock(OAuthClientInterface::class);
        $oauthClient
            ->expects($this->once())
            ->method('exchangeCodeForToken')
            ->with('auth-code-xyz', 'https://my.app/callback')
            ->willReturn($expectedToken);

        $useCase = new ExchangeCodeForTokenUseCase($oauthClient);
        $result  = $useCase->execute('auth-code-xyz', 'https://my.app/callback');

        $this->assertSame($expectedToken, $result);
    }
}
