<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Laravel;

use Andmarruda\InstagramLaravel\Application\UseCases\ExchangeCodeForTokenUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetAuthorizationUrlUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetLongLivedTokenUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\RefreshLongLivedTokenUseCase;
use Andmarruda\InstagramLaravel\Domain\Contracts\OAuthClientInterface;
use Andmarruda\InstagramLaravel\Infrastructure\Http\InstagramOAuthHttpAdapter;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class InstagramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/instagram.php',
            'instagram'
        );

        $this->app->singleton(OAuthClientInterface::class, function ($app) {
            $config = $app->make('config')->get('instagram');

            return new InstagramOAuthHttpAdapter(
                httpClient: new Client(),
                clientId: $config['client_id'] ?? '',
                clientSecret: $config['client_secret'] ?? '',
            );
        });

        $this->app->bind(GetAuthorizationUrlUseCase::class, function ($app) {
            return new GetAuthorizationUrlUseCase($app->make(OAuthClientInterface::class));
        });

        $this->app->bind(ExchangeCodeForTokenUseCase::class, function ($app) {
            return new ExchangeCodeForTokenUseCase($app->make(OAuthClientInterface::class));
        });

        $this->app->bind(GetLongLivedTokenUseCase::class, function ($app) {
            return new GetLongLivedTokenUseCase($app->make(OAuthClientInterface::class));
        });

        $this->app->bind(RefreshLongLivedTokenUseCase::class, function ($app) {
            return new RefreshLongLivedTokenUseCase($app->make(OAuthClientInterface::class));
        });

        $this->app->singleton(InstagramManager::class, function ($app) {
            return new InstagramManager(
                $app->make(GetAuthorizationUrlUseCase::class),
                $app->make(ExchangeCodeForTokenUseCase::class),
                $app->make(GetLongLivedTokenUseCase::class),
                $app->make(RefreshLongLivedTokenUseCase::class),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/instagram.php' => config_path('instagram.php'),
            ], 'instagram-config');
        }
    }
}
