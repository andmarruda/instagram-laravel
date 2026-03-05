<?php

declare(strict_types=1);

namespace Andmarruda\InstagramLaravel\Laravel;

use Andmarruda\InstagramLaravel\Application\UseCases\CheckContainerStatusUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\CreateCarouselContainerUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\CreateImageContainerUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\CreateVideoContainerUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\ExchangeCodeForTokenUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetAuthorizationUrlUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetLongLivedTokenUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetAccountInsightsUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetMediaInsightsUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\GetPublishingLimitUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\PublishContainerUseCase;
use Andmarruda\InstagramLaravel\Application\UseCases\RefreshLongLivedTokenUseCase;
use Andmarruda\InstagramLaravel\Domain\Contracts\ContentPublishingClientInterface;
use Andmarruda\InstagramLaravel\Domain\Contracts\InsightsClientInterface;
use Andmarruda\InstagramLaravel\Domain\Contracts\OAuthClientInterface;
use Andmarruda\InstagramLaravel\Infrastructure\Http\InstagramContentPublishingHttpAdapter;
use Andmarruda\InstagramLaravel\Infrastructure\Http\InstagramInsightsHttpAdapter;
use Andmarruda\InstagramLaravel\Infrastructure\Http\InstagramOAuthHttpAdapter;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;

class InstagramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/instagram.php',
            'instagram'
        );

        // Shared HTTP client with sensible timeouts.
        $this->app->singleton(ClientInterface::class, fn () => new Client([
            'timeout'         => 30,
            'connect_timeout' => 5,
        ]));

        // --- OAuth ---

        $this->app->singleton(OAuthClientInterface::class, function ($app) {
            $config = $app->make('config')->get('instagram');

            return new InstagramOAuthHttpAdapter(
                httpClient: $app->make(ClientInterface::class),
                clientId: $config['client_id'] ?? '',
                clientSecret: $config['client_secret'] ?? '',
            );
        });

        $this->app->bind(GetAuthorizationUrlUseCase::class, fn ($app) =>
            new GetAuthorizationUrlUseCase($app->make(OAuthClientInterface::class)));

        $this->app->bind(ExchangeCodeForTokenUseCase::class, fn ($app) =>
            new ExchangeCodeForTokenUseCase($app->make(OAuthClientInterface::class)));

        $this->app->bind(GetLongLivedTokenUseCase::class, fn ($app) =>
            new GetLongLivedTokenUseCase($app->make(OAuthClientInterface::class)));

        $this->app->bind(RefreshLongLivedTokenUseCase::class, fn ($app) =>
            new RefreshLongLivedTokenUseCase($app->make(OAuthClientInterface::class)));

        // --- Content Publishing ---

        $this->app->singleton(ContentPublishingClientInterface::class, fn ($app) =>
            new InstagramContentPublishingHttpAdapter($app->make(ClientInterface::class)));

        $this->app->bind(CreateImageContainerUseCase::class, fn ($app) =>
            new CreateImageContainerUseCase($app->make(ContentPublishingClientInterface::class)));

        $this->app->bind(CreateVideoContainerUseCase::class, fn ($app) =>
            new CreateVideoContainerUseCase($app->make(ContentPublishingClientInterface::class)));

        $this->app->bind(CreateCarouselContainerUseCase::class, fn ($app) =>
            new CreateCarouselContainerUseCase($app->make(ContentPublishingClientInterface::class)));

        $this->app->bind(CheckContainerStatusUseCase::class, fn ($app) =>
            new CheckContainerStatusUseCase($app->make(ContentPublishingClientInterface::class)));

        $this->app->bind(PublishContainerUseCase::class, fn ($app) =>
            new PublishContainerUseCase($app->make(ContentPublishingClientInterface::class)));

        $this->app->bind(GetPublishingLimitUseCase::class, fn ($app) =>
            new GetPublishingLimitUseCase($app->make(ContentPublishingClientInterface::class)));

        // --- Insights ---

        $this->app->singleton(InsightsClientInterface::class, fn ($app) =>
            new InstagramInsightsHttpAdapter($app->make(ClientInterface::class)));

        $this->app->bind(GetAccountInsightsUseCase::class, fn ($app) =>
            new GetAccountInsightsUseCase($app->make(InsightsClientInterface::class)));

        $this->app->bind(GetMediaInsightsUseCase::class, fn ($app) =>
            new GetMediaInsightsUseCase($app->make(InsightsClientInterface::class)));

        // --- Manager ---

        $this->app->singleton(InstagramManager::class, fn ($app) => new InstagramManager(
            $app->make(GetAuthorizationUrlUseCase::class),
            $app->make(ExchangeCodeForTokenUseCase::class),
            $app->make(GetLongLivedTokenUseCase::class),
            $app->make(RefreshLongLivedTokenUseCase::class),
            $app->make(CreateImageContainerUseCase::class),
            $app->make(CreateVideoContainerUseCase::class),
            $app->make(CreateCarouselContainerUseCase::class),
            $app->make(CheckContainerStatusUseCase::class),
            $app->make(PublishContainerUseCase::class),
            $app->make(GetPublishingLimitUseCase::class),
            $app->make(GetAccountInsightsUseCase::class),
            $app->make(GetMediaInsightsUseCase::class),
        ));
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
