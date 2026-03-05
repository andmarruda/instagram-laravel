<p align="center">
  <h1 align="center">andmarruda / instagram-laravel</h1>
</p>

<p align="center">
  <a href="https://packagist.org/packages/andmarruda/instagram-laravel"><img src="https://img.shields.io/packagist/v/andmarruda/instagram-laravel?style=flat-square&color=ff69b4" alt="Latest Version" /></a>
  <a href="https://packagist.org/packages/andmarruda/instagram-laravel"><img src="https://img.shields.io/packagist/php-v/andmarruda/instagram-laravel?style=flat-square" alt="PHP Version" /></a>
  <a href="https://packagist.org/packages/andmarruda/instagram-laravel"><img src="https://img.shields.io/packagist/l/andmarruda/instagram-laravel?style=flat-square" alt="License" /></a>
  <a href="https://github.com/andmarruda/instagram-laravel/actions"><img src="https://img.shields.io/github/actions/workflow/status/andmarruda/instagram-laravel/tests.yml?style=flat-square&label=tests" alt="Tests" /></a>
  <a href="https://www.buymeacoffee.com/andmarruda"><img src="https://img.shields.io/badge/buy%20me%20a%20coffee-donate-yellow?style=flat-square&logo=buy-me-a-coffee" alt="Buy Me a Coffee" /></a>
</p>

<p align="center">
  A <strong>Ports &amp; Adapters</strong> (Hexagonal Architecture) Laravel package for the Instagram Business Login API.<br />
  OAuth · Content Publishing · Insights &mdash; clean, testable, zero coupling to Instagram's SDK.
</p>

<p align="center">
  <a href="#english">🇺🇸 English</a> &nbsp;·&nbsp;
  <a href="#portuguese">🇧🇷 Português</a>
</p>

---

<a name="english"></a>

# 🇺🇸 English

## Table of Contents

- [Features](#features)
- [Architecture](#architecture)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [OAuth — Authentication](#oauth--authentication)
- [Content Publishing](#content-publishing)
- [Insights](#insights)
- [Exception Handling](#exception-handling)
- [Architecture Deep Dive](#architecture-deep-dive)
- [Testing](#testing)

---

## Features

| Area | What's included |
|------|----------------|
| **OAuth** | Authorization URL · Code exchange · Long-lived token · Token refresh |
| **Content Publishing** | Image · Video · Reels · Stories · Carousel · Status polling · Rate limit |
| **Insights** | Account metrics · Media metrics · Period-based queries · Time-series values |

- **Zero persistence coupling** — the package never stores tokens. Your application decides where they live.
- **First-class type safety** — every API concept is a typed PHP 8.2 Value Object or Enum.
- **Fully mockable** — every infrastructure concern sits behind a Domain Contract (interface). Swap the real HTTP adapter for a mock in one line.
- **Laravel auto-wired** — one `ServiceProvider` wires all 12 use cases, 3 adapters, and the `Instagram` Facade automatically.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         Your Application                            │
│                                                                     │
│   Instagram::authorizationUrl()   Instagram::accountInsights()      │
└──────────────────────────┬──────────────────────┬───────────────────┘
                           │  Laravel Facade       │
                    ┌──────▼──────────────────────▼──────┐
                    │         InstagramManager            │
                    │  (thin orchestration — no logic)    │
                    └──────┬──────────────────────┬──────┘
                           │  Use Cases            │
          ┌────────────────▼──────────────────────▼────────────────┐
          │                   Application Layer                      │
          │  GetAuthorizationUrlUseCase  ExchangeCodeForTokenUseCase │
          │  CreateImageContainerUseCase  GetAccountInsightsUseCase  │
          │  ... 12 use cases total                                  │
          └────────────────┬──────────────────────┬────────────────┘
                           │  Domain Contracts     │
          ┌────────────────▼──────────────────────▼────────────────┐
          │                    Domain Layer                          │
          │  OAuthClientInterface  ContentPublishingClientInterface  │
          │  InsightsClientInterface                                 │
          │                                                          │
          │  Value Objects: AccessToken · CarouselItem · InsightMetric│
          │  Enums: Scope · MediaType · ContainerStatus · InsightPeriod│
          │         AccountMetric · MediaMetric                      │
          └────────────────┬──────────────────────┬────────────────┘
                           │  Adapters (pluggable) │
          ┌────────────────▼──────────────────────▼────────────────┐
          │                 Infrastructure Layer                     │
          │  InstagramOAuthHttpAdapter                               │
          │  InstagramContentPublishingHttpAdapter                   │
          │  InstagramInsightsHttpAdapter                            │
          │                                                          │
          │  All sharing GraphApiTrait: url() · decode() · headers() │
          └─────────────────────────────────────────────────────────┘
```

---

## Requirements

| Dependency | Version |
|-----------|---------|
| PHP | `^8.2` |
| Laravel | `^10.0 \| ^11.0` |
| guzzlehttp/guzzle | `^7.0` |

---

## Installation

```bash
composer require andmarruda/instagram-laravel
```

The `InstagramServiceProvider` is auto-discovered. Publish the config file:

```bash
php artisan vendor:publish --tag=instagram-config
```

---

## Configuration

Add the following keys to your `.env` file:

```dotenv
INSTAGRAM_APP_ID=your_app_id
INSTAGRAM_APP_SECRET=your_app_secret
INSTAGRAM_REDIRECT_URI=https://yourdomain.com/instagram/callback

# Optional — comma-separated scopes (default: instagram_business_basic)
INSTAGRAM_SCOPES=instagram_business_basic,instagram_business_content_publish
```

Available scopes:

| Scope Enum | Instagram API value |
|-----------|-------------------|
| `Scope::Basic` | `instagram_business_basic` |
| `Scope::ContentPublish` | `instagram_business_content_publish` |
| `Scope::ManageMessages` | `instagram_business_manage_messages` |
| `Scope::ManageComments` | `instagram_business_manage_comments` |

---

## OAuth — Authentication

The package implements the full **Instagram Business Login** OAuth 2.0 flow in four steps.

### Step 1 — Redirect the user to Instagram

```php
use Andmarruda\InstagramLaravel\Laravel\Facades\Instagram;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;

// Uses redirect_uri and scopes from config automatically
return redirect(Instagram::authorizationUrl());

// Or override at call time
return redirect(Instagram::authorizationUrl(
    redirectUri: 'https://yourdomain.com/callback',
    scopes:      [Scope::Basic, Scope::ContentPublish],
    options:     ['state' => csrf_token()],
));
```

### Step 2 — Exchange the authorization code for a short-lived token

```php
// In your callback controller
$token = Instagram::exchangeCode($request->input('code'));
// $token is an AccessToken value object
// The package automatically strips any trailing "#_" from the code.

echo $token->token;      // eyJhbGci...
echo $token->userId;     // 17841400...
echo $token->expiresIn;  // 3600 (seconds)
```

### Step 3 — Upgrade to a long-lived token (valid 60 days)

```php
$longLived = Instagram::longLivedToken($token->token);

echo $longLived->expiresAt()?->format('Y-m-d'); // 2025-08-03
echo $longLived->isExpired();                   // false
```

### Step 4 — Refresh a long-lived token before it expires

```php
// Refresh before the 60-day expiry to keep the token alive.
// Instagram requires: token is >= 24 h old, not expired, and
// the user has granted instagram_business_basic.
$refreshed = Instagram::refreshToken($longLived->token);
```

### AccessToken Value Object

```php
$token->token       // string — the raw access token
$token->userId      // string — Instagram Business account ID
$token->tokenType   // string — always "bearer"
$token->expiresIn   // int|null — TTL in seconds (null = never expires)
$token->permissions // array   — granted scopes
$token->expiresAt() // DateTimeImmutable|null
$token->isExpired() // bool
```

---

## Content Publishing

The full **three-step container → status → publish** flow is supported.

> **Rate limit:** Instagram allows 50 posts per 24 h per user. Check `publishingLimit()` before publishing.

### Publish a single image

```php
use Andmarruda\InstagramLaravel\Laravel\Facades\Instagram;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\ContainerStatus;

// Step 1 — create the container
$containerId = Instagram::createImageContainer(
    igId:        $igId,
    accessToken: $token,
    imageUrl:    'https://cdn.example.com/photo.jpg',
    options:     ['caption' => 'Hello world! 🌍'],
);

// Step 2 — wait until the container is ready
do {
    sleep(2);
    $status = Instagram::containerStatus($containerId, $token);
} while (! $status->isFinal());

// Step 3 — publish
if ($status->isReadyToPublish()) {
    $mediaId = Instagram::publish($igId, $token, $containerId);
}
```

### Publish a video / reel / story

```php
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaType;

$containerId = Instagram::createVideoContainer(
    igId:        $igId,
    accessToken: $token,
    videoUrl:    'https://cdn.example.com/reel.mp4',
    mediaType:   MediaType::Reels,
    options:     ['caption' => 'My reel 🎬', 'share_to_feed' => true],
);
```

Available `MediaType` values: `Video` · `Reels` · `Stories` · `Carousel`

### Publish a carousel (2–10 items)

```php
use Andmarruda\InstagramLaravel\Domain\ValueObjects\CarouselItem;

$mediaId = Instagram::createCarouselContainer(
    igId:        $igId,
    accessToken: $token,
    items: [
        CarouselItem::image('https://cdn.example.com/slide1.jpg'),
        CarouselItem::image('https://cdn.example.com/slide2.jpg'),
        CarouselItem::video('https://cdn.example.com/slide3.mp4'),
    ],
    caption: 'Check out these three slides 👇',
);
```

### ContainerStatus Enum

```php
$status->isReadyToPublish() // true when status = FINISHED
$status->isFinal()          // true when status is in {FINISHED, ERROR, EXPIRED, PUBLISHED}
```

| Status | Meaning |
|--------|---------|
| `InProgress` | Still being processed by Instagram |
| `Finished` | Ready to publish |
| `Published` | Already published |
| `Error` | Processing failed |
| `Expired` | Container expired before publishing |

### Check publishing rate limit

```php
$limit = Instagram::publishingLimit($igId, $token);

$limit->quotaUsage    // int — posts made in the last 24 h
$limit->quotaTotal    // int — max posts allowed (usually 50)
$limit->percentUsed() // int — e.g. 60 (%)
$limit->hasReachedLimit() // bool
```

---

## Insights

### Account Insights

```php
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccountMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightPeriod;

$metrics = Instagram::accountInsights(
    igId:        $igId,
    accessToken: $token,
    metrics:     [AccountMetric::Impressions, AccountMetric::Reach, AccountMetric::ProfileViews],
    period:      InsightPeriod::Day,
);

foreach ($metrics as $metric) {
    echo "{$metric->title}: {$metric->total()}\n";
    // "Impressions: 4820"

    foreach ($metric->values as $value) {
        echo "  {$value->endTime?->format('Y-m-d')}: {$value->value}\n";
        // "  2024-06-01: 1240"
    }
}
```

Query a specific date range using `since` / `until`:

```php
$metrics = Instagram::accountInsights(
    igId:        $igId,
    accessToken: $token,
    metrics:     [AccountMetric::Reach],
    period:      InsightPeriod::Day,
    options:     [
        'since' => strtotime('2024-06-01'),
        'until' => strtotime('2024-06-30'),
    ],
);
```

#### Available AccountMetric values

| Enum case | API value | Description |
|-----------|-----------|-------------|
| `Impressions` | `impressions` | Total times content was seen |
| `Reach` | `reach` | Unique accounts that saw content |
| `ProfileViews` | `profile_views` | Profile page visits |
| `FollowerCount` | `follower_count` | Follower count snapshot |
| `WebsiteClicks` | `website_clicks` | Clicks on website link in bio |
| `EmailContacts` | `email_contacts` | Taps on email contact button |
| `PhoneCallClicks` | `phone_call_clicks` | Taps on call button |
| `TextMessageClicks` | `text_message_clicks` | Taps on text button |
| `GetDirectionsClicks` | `get_directions_clicks` | Taps on directions button |
| `TotalInteractions` | `total_interactions` | Sum of all interactions |
| `AccountsEngaged` | `accounts_engaged` | Unique accounts that interacted |
| `Likes` | `likes` | Total likes |
| `Comments` | `comments` | Total comments |
| `Shares` | `shares` | Total shares |
| `Saves` | `saves` | Total saves |

#### Available InsightPeriod values

| Enum case | API value |
|-----------|-----------|
| `Day` | `day` |
| `Week` | `week` |
| `Month` | `month` |
| `Lifetime` | `lifetime` |

### Media Insights

```php
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaMetric;

$metrics = Instagram::mediaInsights(
    mediaId:     $mediaId,
    accessToken: $token,
    metrics:     [MediaMetric::Impressions, MediaMetric::Likes, MediaMetric::Shares],
);

foreach ($metrics as $metric) {
    echo "{$metric->name}: {$metric->total()}\n";
}
```

#### Available MediaMetric values

| Enum case | API value |
|-----------|-----------|
| `Engagement` | `engagement` |
| `Impressions` | `impressions` |
| `Reach` | `reach` |
| `Saved` | `saved` |
| `VideoViews` | `video_views` |
| `Likes` | `likes` |
| `Comments` | `comments` |
| `Shares` | `shares` |
| `Plays` | `plays` |
| `TotalInteractions` | `total_interactions` |
| `Follows` | `follows` |
| `ProfileVisits` | `profile_visits` |

### InsightMetric Value Object

```php
$metric->name        // string — e.g. "impressions"
$metric->period      // string — e.g. "day"
$metric->title       // string — e.g. "Impressions"
$metric->description // string
$metric->id          // string — e.g. "123/insights/impressions/day"
$metric->values      // InsightValue[]
$metric->total()     // int|float — sum of all values

// Each InsightValue:
$value->value   // int|float
$value->endTime // DateTimeImmutable|null
```

---

## Exception Handling

Each infrastructure layer throws a dedicated typed exception. All extend `RuntimeException`.

```php
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramOAuthException;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramPublishingException;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramInsightsException;

try {
    $mediaId = Instagram::publish($igId, $token, $containerId);
} catch (InstagramPublishingException $e) {
    // Rich error context for publishing failures
    $e->errorSubcode;       // ?int   — Instagram API error subcode
    $e->isTransient;        // bool   — temporary Instagram-side failure
    $e->userTitle;          // ?string
    $e->userMessage;        // ?string

    if ($e->isRetryable()) {
        // Safe to retry with the same container
    }

    if ($e->requiresNewContainer()) {
        // Must create a brand-new container before retrying
    }
}
```

`isRetryable()` returns `true` for transient errors and subcodes `2207001` / `2207008`.
`requiresNewContainer()` returns `true` for subcodes `2207003`, `2207006`, `2207020`, `2207032`, `2207053`.

---

## Architecture Deep Dive

### Why Ports & Adapters?

The Domain and Application layers have **zero knowledge of HTTP, Guzzle, or Instagram**. They depend only on the three interfaces (ports):

```php
// Domain Contract — the "port"
interface InsightsClientInterface
{
    public function getAccountInsights(
        string $igId,
        string $accessToken,
        array $metrics,
        InsightPeriod $period,
        array $options = [],
    ): array;
}

// In tests — swap the real adapter for any mock in one line
$client = $this->createMock(InsightsClientInterface::class);
$client->method('getAccountInsights')->willReturn([$fakeMetric]);
```

This means:
- You can test all 12 use cases with **zero HTTP calls**.
- You can replace the Instagram HTTP adapter with a different transport (e.g., a caching decorator, a circuit breaker) without touching a single use case.

### Value Objects as first-class citizens

Rather than passing raw arrays around, every API concept is encoded as an immutable value object:

```php
// Instead of:    ['token' => '...', 'user_id' => '...', 'expires_in' => 3600]
// You get:       AccessToken with isExpired(), expiresAt(), permissions

// Instead of:    'FINISHED'
// You get:       ContainerStatus::Finished with isReadyToPublish(), isFinal()
```

### Shared infrastructure via traits

Two cross-cutting traits prevent duplication across adapters:

| Trait | Provides | Used by |
|-------|----------|---------|
| `GraphApiTrait` | `url()` · `decode()` · `buildHeaders()` | `ContentPublishingHttpAdapter`, `InsightsHttpAdapter` |
| `HasCommaString` | `toString(array): string` | `Scope`, `AccountMetric`, `MediaMetric` |

---

## Testing

```bash
composer test
# or
vendor/bin/phpunit
```

The test suite ships with **27 tests, 95 assertions**, all using PHPUnit mocks — no real API calls.

```
tests/
└── Unit/
    ├── Application/UseCases/
    │   ├── CreateCarouselContainerUseCaseTest.php
    │   ├── ExchangeCodeForTokenUseCaseTest.php
    │   ├── GetAccountInsightsUseCaseTest.php
    │   └── GetMediaInsightsUseCaseTest.php
    ├── Domain/ValueObjects/
    │   ├── AccessTokenTest.php
    │   ├── ContainerStatusTest.php
    │   ├── InsightMetricTest.php
    │   └── MetricEnumTest.php
    └── Infrastructure/Http/Exceptions/
        └── InstagramPublishingExceptionTest.php
```

### Writing your own mocks

Because every use case depends on a Domain Contract, testing your own code is straightforward:

```php
use Andmarruda\InstagramLaravel\Domain\Contracts\InsightsClientInterface;
use Andmarruda\InstagramLaravel\Application\UseCases\GetAccountInsightsUseCase;

$mock = $this->createMock(InsightsClientInterface::class);
$mock->method('getAccountInsights')->willReturn([$metric]);

$useCase = new GetAccountInsightsUseCase($mock);
$result  = $useCase->execute($igId, $token, [AccountMetric::Reach], InsightPeriod::Day);
```

---

<a name="portuguese"></a>

---

# 🇧🇷 Português

## Sumário

- [Funcionalidades](#funcionalidades)
- [Arquitetura](#arquitetura)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [OAuth — Autenticação](#oauth--autenticação)
- [Publicação de Conteúdo](#publicação-de-conteúdo)
- [Insights](#insights-1)
- [Tratamento de Erros](#tratamento-de-erros)
- [Arquitetura em Detalhes](#arquitetura-em-detalhes)
- [Testes](#testes)

---

## Funcionalidades

| Área | O que está incluído |
|------|---------------------|
| **OAuth** | URL de autorização · Troca de código · Token de longa duração · Renovação de token |
| **Publicação de Conteúdo** | Imagem · Vídeo · Reels · Stories · Carrossel · Polling de status · Limite de taxa |
| **Insights** | Métricas de conta · Métricas de mídia · Consultas por período · Séries temporais |

- **Sem acoplamento a persistência** — o pacote jamais armazena tokens. Sua aplicação decide onde eles ficam.
- **Segurança de tipos em primeiro lugar** — todo conceito de API é um Value Object ou Enum PHP 8.2 tipado.
- **Totalmente mockável** — toda infraestrutura está atrás de um Contrato de Domínio (interface). Troque o adaptador HTTP real por um mock em uma linha.
- **Auto-configurável no Laravel** — um único `ServiceProvider` configura automaticamente os 12 use cases, 3 adaptadores e a Facade `Instagram`.

---

## Arquitetura

```
┌─────────────────────────────────────────────────────────────────────┐
│                       Sua Aplicação                                  │
│                                                                     │
│   Instagram::authorizationUrl()   Instagram::accountInsights()      │
└──────────────────────────┬──────────────────────┬───────────────────┘
                           │  Laravel Facade       │
                    ┌──────▼──────────────────────▼──────┐
                    │         InstagramManager            │
                    │  (orquestração fina — sem lógica)   │
                    └──────┬──────────────────────┬──────┘
                           │  Use Cases            │
          ┌────────────────▼──────────────────────▼────────────────┐
          │                   Camada de Aplicação                    │
          │  GetAuthorizationUrlUseCase  ExchangeCodeForTokenUseCase │
          │  CreateImageContainerUseCase  GetAccountInsightsUseCase  │
          │  ... 12 use cases no total                               │
          └────────────────┬──────────────────────┬────────────────┘
                           │  Domain Contracts     │
          ┌────────────────▼──────────────────────▼────────────────┐
          │                    Camada de Domínio                     │
          │  OAuthClientInterface  ContentPublishingClientInterface  │
          │  InsightsClientInterface                                 │
          │                                                          │
          │  Value Objects: AccessToken · CarouselItem · InsightMetric│
          │  Enums: Scope · MediaType · ContainerStatus · InsightPeriod│
          │         AccountMetric · MediaMetric                      │
          └────────────────┬──────────────────────┬────────────────┘
                           │  Adapters (plugável)  │
          ┌────────────────▼──────────────────────▼────────────────┐
          │                 Camada de Infraestrutura                 │
          │  InstagramOAuthHttpAdapter                               │
          │  InstagramContentPublishingHttpAdapter                   │
          │  InstagramInsightsHttpAdapter                            │
          │                                                          │
          │  Todos compartilham GraphApiTrait: url() · decode() · …  │
          └─────────────────────────────────────────────────────────┘
```

---

## Requisitos

| Dependência | Versão |
|------------|--------|
| PHP | `^8.2` |
| Laravel | `^10.0 \| ^11.0` |
| guzzlehttp/guzzle | `^7.0` |

---

## Instalação

```bash
composer require andmarruda/instagram-laravel
```

O `InstagramServiceProvider` é descoberto automaticamente. Publique o arquivo de configuração:

```bash
php artisan vendor:publish --tag=instagram-config
```

---

## Configuração

Adicione as seguintes chaves ao seu arquivo `.env`:

```dotenv
INSTAGRAM_APP_ID=seu_app_id
INSTAGRAM_APP_SECRET=seu_app_secret
INSTAGRAM_REDIRECT_URI=https://seudominio.com.br/instagram/callback

# Opcional — escopos separados por vírgula (padrão: instagram_business_basic)
INSTAGRAM_SCOPES=instagram_business_basic,instagram_business_content_publish
```

Escopos disponíveis:

| Enum de Scope | Valor na API do Instagram |
|--------------|--------------------------|
| `Scope::Basic` | `instagram_business_basic` |
| `Scope::ContentPublish` | `instagram_business_content_publish` |
| `Scope::ManageMessages` | `instagram_business_manage_messages` |
| `Scope::ManageComments` | `instagram_business_manage_comments` |

---

## OAuth — Autenticação

O pacote implementa o fluxo completo do **Instagram Business Login** OAuth 2.0 em quatro etapas.

### Etapa 1 — Redirecione o usuário ao Instagram

```php
use Andmarruda\InstagramLaravel\Laravel\Facades\Instagram;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\Scope;

// Usa redirect_uri e escopos do config automaticamente
return redirect(Instagram::authorizationUrl());

// Ou sobrescreva na chamada
return redirect(Instagram::authorizationUrl(
    redirectUri: 'https://seudominio.com.br/callback',
    scopes:      [Scope::Basic, Scope::ContentPublish],
    options:     ['state' => csrf_token()],
));
```

### Etapa 2 — Troque o código por um token de curta duração

```php
// No seu controller de callback
$token = Instagram::exchangeCode($request->input('code'));
// $token é um Value Object AccessToken
// O pacote remove automaticamente o "#_" que o Instagram às vezes anexa ao código.

echo $token->token;      // eyJhbGci...
echo $token->userId;     // 17841400...
echo $token->expiresIn;  // 3600 (segundos)
```

### Etapa 3 — Obtenha um token de longa duração (válido por 60 dias)

```php
$longLived = Instagram::longLivedToken($token->token);

echo $longLived->expiresAt()?->format('d/m/Y'); // 03/08/2025
echo $longLived->isExpired();                   // false
```

### Etapa 4 — Renove o token antes de expirar

```php
// Renove antes dos 60 dias para manter o token vivo.
// O Instagram exige: token com >= 24 h de vida, não expirado e
// o usuário concedeu instagram_business_basic.
$renovado = Instagram::refreshToken($longLived->token);
```

### Value Object AccessToken

```php
$token->token       // string — o token bruto
$token->userId      // string — ID da conta Business do Instagram
$token->tokenType   // string — sempre "bearer"
$token->expiresIn   // int|null — TTL em segundos (null = não expira)
$token->permissions // array   — escopos concedidos
$token->expiresAt() // DateTimeImmutable|null
$token->isExpired() // bool
```

---

## Publicação de Conteúdo

O fluxo completo de **container → status → publicação** é suportado.

> **Limite de taxa:** O Instagram permite 50 publicações por 24 h por usuário. Verifique `publishingLimit()` antes de publicar.

### Publicar uma imagem

```php
use Andmarruda\InstagramLaravel\Laravel\Facades\Instagram;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\ContainerStatus;

// Etapa 1 — cria o container
$containerId = Instagram::createImageContainer(
    igId:        $igId,
    accessToken: $token,
    imageUrl:    'https://cdn.exemplo.com.br/foto.jpg',
    options:     ['caption' => 'Olá mundo! 🌍'],
);

// Etapa 2 — aguarda o container estar pronto
do {
    sleep(2);
    $status = Instagram::containerStatus($containerId, $token);
} while (! $status->isFinal());

// Etapa 3 — publica
if ($status->isReadyToPublish()) {
    $mediaId = Instagram::publish($igId, $token, $containerId);
}
```

### Publicar vídeo / reel / story

```php
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaType;

$containerId = Instagram::createVideoContainer(
    igId:        $igId,
    accessToken: $token,
    videoUrl:    'https://cdn.exemplo.com.br/reel.mp4',
    mediaType:   MediaType::Reels,
    options:     ['caption' => 'Meu reel 🎬', 'share_to_feed' => true],
);
```

Valores disponíveis de `MediaType`: `Video` · `Reels` · `Stories` · `Carousel`

### Publicar carrossel (2–10 itens)

```php
use Andmarruda\InstagramLaravel\Domain\ValueObjects\CarouselItem;

$mediaId = Instagram::createCarouselContainer(
    igId:        $igId,
    accessToken: $token,
    items: [
        CarouselItem::image('https://cdn.exemplo.com.br/slide1.jpg'),
        CarouselItem::image('https://cdn.exemplo.com.br/slide2.jpg'),
        CarouselItem::video('https://cdn.exemplo.com.br/slide3.mp4'),
    ],
    caption: 'Confira esses três slides 👇',
);
```

### Enum ContainerStatus

```php
$status->isReadyToPublish() // true quando status = FINISHED
$status->isFinal()          // true quando status está em {FINISHED, ERROR, EXPIRED, PUBLISHED}
```

| Status | Significado |
|--------|-------------|
| `InProgress` | Ainda sendo processado pelo Instagram |
| `Finished` | Pronto para publicar |
| `Published` | Já publicado |
| `Error` | Falha no processamento |
| `Expired` | Container expirou antes da publicação |

### Verificar limite de publicações

```php
$limit = Instagram::publishingLimit($igId, $token);

$limit->quotaUsage    // int — publicações nas últimas 24 h
$limit->quotaTotal    // int — máximo permitido (geralmente 50)
$limit->percentUsed() // int — ex: 60 (%)
$limit->hasReachedLimit() // bool
```

---

## Insights

### Insights de Conta

```php
use Andmarruda\InstagramLaravel\Domain\ValueObjects\AccountMetric;
use Andmarruda\InstagramLaravel\Domain\ValueObjects\InsightPeriod;

$metrics = Instagram::accountInsights(
    igId:        $igId,
    accessToken: $token,
    metrics:     [AccountMetric::Impressions, AccountMetric::Reach, AccountMetric::ProfileViews],
    period:      InsightPeriod::Day,
);

foreach ($metrics as $metric) {
    echo "{$metric->title}: {$metric->total()}\n";
    // "Impressions: 4820"

    foreach ($metric->values as $value) {
        echo "  {$value->endTime?->format('d/m/Y')}: {$value->value}\n";
        // "  01/06/2024: 1240"
    }
}
```

Consulta por intervalo de datas com `since` / `until`:

```php
$metrics = Instagram::accountInsights(
    igId:        $igId,
    accessToken: $token,
    metrics:     [AccountMetric::Reach],
    period:      InsightPeriod::Day,
    options:     [
        'since' => strtotime('2024-06-01'),
        'until' => strtotime('2024-06-30'),
    ],
);
```

#### Valores disponíveis de AccountMetric

| Case do Enum | Valor na API | Descrição |
|-------------|--------------|-----------|
| `Impressions` | `impressions` | Total de vezes que o conteúdo foi visto |
| `Reach` | `reach` | Contas únicas que viram o conteúdo |
| `ProfileViews` | `profile_views` | Visitas ao perfil |
| `FollowerCount` | `follower_count` | Instantâneo de seguidores |
| `WebsiteClicks` | `website_clicks` | Cliques no link da bio |
| `EmailContacts` | `email_contacts` | Toques no botão de e-mail |
| `PhoneCallClicks` | `phone_call_clicks` | Toques no botão de ligação |
| `TextMessageClicks` | `text_message_clicks` | Toques no botão de mensagem |
| `GetDirectionsClicks` | `get_directions_clicks` | Toques em direções/rotas |
| `TotalInteractions` | `total_interactions` | Soma de todas as interações |
| `AccountsEngaged` | `accounts_engaged` | Contas únicas que interagiram |
| `Likes` | `likes` | Total de curtidas |
| `Comments` | `comments` | Total de comentários |
| `Shares` | `shares` | Total de compartilhamentos |
| `Saves` | `saves` | Total de salvamentos |

#### Valores disponíveis de InsightPeriod

| Case do Enum | Valor na API |
|-------------|--------------|
| `Day` | `day` |
| `Week` | `week` |
| `Month` | `month` |
| `Lifetime` | `lifetime` |

### Insights de Mídia

```php
use Andmarruda\InstagramLaravel\Domain\ValueObjects\MediaMetric;

$metrics = Instagram::mediaInsights(
    mediaId:     $mediaId,
    accessToken: $token,
    metrics:     [MediaMetric::Impressions, MediaMetric::Likes, MediaMetric::Shares],
);

foreach ($metrics as $metric) {
    echo "{$metric->name}: {$metric->total()}\n";
}
```

#### Valores disponíveis de MediaMetric

| Case do Enum | Valor na API |
|-------------|--------------|
| `Engagement` | `engagement` |
| `Impressions` | `impressions` |
| `Reach` | `reach` |
| `Saved` | `saved` |
| `VideoViews` | `video_views` |
| `Likes` | `likes` |
| `Comments` | `comments` |
| `Shares` | `shares` |
| `Plays` | `plays` |
| `TotalInteractions` | `total_interactions` |
| `Follows` | `follows` |
| `ProfileVisits` | `profile_visits` |

### Value Object InsightMetric

```php
$metric->name        // string — ex: "impressions"
$metric->period      // string — ex: "day"
$metric->title       // string — ex: "Impressions"
$metric->description // string
$metric->id          // string — ex: "123/insights/impressions/day"
$metric->values      // InsightValue[]
$metric->total()     // int|float — soma de todos os valores

// Cada InsightValue:
$value->value   // int|float
$value->endTime // DateTimeImmutable|null
```

---

## Tratamento de Erros

Cada camada de infraestrutura lança uma exceção tipada dedicada. Todas estendem `RuntimeException`.

```php
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramOAuthException;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramPublishingException;
use Andmarruda\InstagramLaravel\Infrastructure\Http\Exceptions\InstagramInsightsException;

try {
    $mediaId = Instagram::publish($igId, $token, $containerId);
} catch (InstagramPublishingException $e) {
    // Contexto de erro rico para falhas de publicação
    $e->errorSubcode;       // ?int    — subcódigo de erro da API
    $e->isTransient;        // bool    — falha temporária no lado do Instagram
    $e->userTitle;          // ?string
    $e->userMessage;        // ?string

    if ($e->isRetryable()) {
        // Seguro tentar novamente com o mesmo container
    }

    if ($e->requiresNewContainer()) {
        // É necessário criar um novo container antes de tentar novamente
    }
}
```

`isRetryable()` retorna `true` para erros transientes e subcódigos `2207001` / `2207008`.
`requiresNewContainer()` retorna `true` para subcódigos `2207003`, `2207006`, `2207020`, `2207032`, `2207053`.

---

## Arquitetura em Detalhes

### Por que Ports & Adapters?

As camadas de Domínio e Aplicação têm **zero conhecimento de HTTP, Guzzle ou Instagram**. Elas dependem apenas das três interfaces (portas):

```php
// Contrato de Domínio — a "porta"
interface InsightsClientInterface
{
    public function getAccountInsights(
        string $igId,
        string $accessToken,
        array $metrics,
        InsightPeriod $period,
        array $options = [],
    ): array;
}

// Nos testes — troque o adaptador real por qualquer mock em uma linha
$client = $this->createMock(InsightsClientInterface::class);
$client->method('getAccountInsights')->willReturn([$fakeMetric]);
```

Isso significa:
- Você pode testar todos os 12 use cases com **zero chamadas HTTP**.
- Você pode substituir o adaptador HTTP do Instagram por um transporte diferente (ex: decorador com cache, circuit breaker) sem tocar em nenhum use case.

### Value Objects como cidadãos de primeira classe

Em vez de passar arrays crus, todo conceito de API é codificado como um value object imutável:

```php
// Em vez de:  ['token' => '...', 'user_id' => '...', 'expires_in' => 3600]
// Você tem:   AccessToken com isExpired(), expiresAt(), permissions

// Em vez de:  'FINISHED'
// Você tem:   ContainerStatus::Finished com isReadyToPublish(), isFinal()
```

### Infraestrutura compartilhada via traits

Duas traits transversais evitam duplicação entre os adaptadores:

| Trait | Fornece | Usado por |
|-------|---------|-----------|
| `GraphApiTrait` | `url()` · `decode()` · `buildHeaders()` | `ContentPublishingHttpAdapter`, `InsightsHttpAdapter` |
| `HasCommaString` | `toString(array): string` | `Scope`, `AccountMetric`, `MediaMetric` |

---

## Testes

```bash
composer test
# ou
vendor/bin/phpunit
```

A suíte de testes inclui **27 testes, 95 assertivas**, todos usando mocks do PHPUnit — sem chamadas reais à API.

```
tests/
└── Unit/
    ├── Application/UseCases/
    │   ├── CreateCarouselContainerUseCaseTest.php
    │   ├── ExchangeCodeForTokenUseCaseTest.php
    │   ├── GetAccountInsightsUseCaseTest.php
    │   └── GetMediaInsightsUseCaseTest.php
    ├── Domain/ValueObjects/
    │   ├── AccessTokenTest.php
    │   ├── ContainerStatusTest.php
    │   ├── InsightMetricTest.php
    │   └── MetricEnumTest.php
    └── Infrastructure/Http/Exceptions/
        └── InstagramPublishingExceptionTest.php
```

### Escrevendo seus próprios mocks

Como cada use case depende de um Contrato de Domínio, testar seu próprio código é direto ao ponto:

```php
use Andmarruda\InstagramLaravel\Domain\Contracts\InsightsClientInterface;
use Andmarruda\InstagramLaravel\Application\UseCases\GetAccountInsightsUseCase;

$mock = $this->createMock(InsightsClientInterface::class);
$mock->method('getAccountInsights')->willReturn([$metric]);

$useCase = new GetAccountInsightsUseCase($mock);
$result  = $useCase->execute($igId, $token, [AccountMetric::Reach], InsightPeriod::Day);
```

---

<p align="center">
  Feito com ❤️ no Brasil · MIT License
</p>
