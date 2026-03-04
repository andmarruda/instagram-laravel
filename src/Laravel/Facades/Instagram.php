<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Laravel\Facades;

use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccessToken;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;
use Andmarruda\InstagramLaravel\Laravel\InstagramManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string      authorizationUrl(?string $redirectUri = null, ?array $scopes = null, array $options = [])
 * @method static AccessToken exchangeCode(string $code, ?string $redirectUri = null)
 * @method static AccessToken longLivedToken(string $shortLivedToken)
 * @method static AccessToken refreshToken(string $longLivedToken)
 *
 * @see InstagramManager
 */
class Instagram extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return InstagramManager::class;
    }
}
