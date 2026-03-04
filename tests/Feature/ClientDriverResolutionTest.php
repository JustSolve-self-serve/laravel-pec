<?php

namespace JustSolve\LegalmailPec\Tests\Feature;

use Illuminate\Support\Facades\Http;
use JustSolve\LegalmailPec\Contracts\PecClient;
use JustSolve\LegalmailPec\Contracts\PecClientManager;
use JustSolve\LegalmailPec\Services\LegalmailProviderClient;
use JustSolve\LegalmailPec\Services\OpenApiPecMassivaProviderClient;
use JustSolve\LegalmailPec\Tests\TestCase;
use RuntimeException;

class ClientDriverResolutionTest extends TestCase
{
    public function test_it_uses_legalmail_driver_by_default(): void
    {
        $client = $this->app->make(PecClient::class);

        $this->assertInstanceOf(LegalmailProviderClient::class, $client);
    }

    public function test_it_uses_openapi_pec_massiva_driver_when_configured(): void
    {
        $this->app['config']->set('pec.default', 'openapi_pec_massiva');
        $this->app->forgetInstance(PecClient::class);
        $this->app->forgetInstance(PecClientManager::class);

        Http::fake([
            '*' => Http::response(['data' => []], 200),
        ]);

        $client = $this->app->make(PecClient::class);

        $this->assertInstanceOf(OpenApiPecMassivaProviderClient::class, $client);

        $client->listMessages();

        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://openapi.example.test/inbox'));
    }

    public function test_it_throws_for_unsupported_driver(): void
    {
        $this->app['config']->set('pec.default', 'unknown_driver');
        $this->app->forgetInstance(PecClientManager::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported pec driver [unknown_driver].');

        $this->app->make(PecClientManager::class)->default();
    }

    public function test_it_can_resolve_both_drivers_with_manager(): void
    {
        $manager = $this->app->make(PecClientManager::class);

        $legalmailClient = $manager->driver('legalmail');
        $openApiClient = $manager->driver('openapi_pec_massiva');

        $this->assertInstanceOf(LegalmailProviderClient::class, $legalmailClient);
        $this->assertInstanceOf(OpenApiPecMassivaProviderClient::class, $openApiClient);
    }

    public function test_openapi_pec_massiva_uses_its_provider_specific_uris(): void
    {
        Http::fake([
            '*' => Http::response(['ok' => true], 200),
        ]);

        /** @var PecClientManager $manager */
        $manager = $this->app->make(PecClientManager::class);
        $client = $manager->driver('openapi_pec_massiva');

        $client->listMessages();
        $client->getMessage('message-1');
        $client->createSubmission(['subject' => 'Hello']);
        $client->deleteMessage('message-1');

        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://openapi.example.test/inbox'));
        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://openapi.example.test/inbox/message-1'));
        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_starts_with($request->url(), 'https://openapi.example.test/send'));
        Http::assertSent(fn ($request): bool => $request->method() === 'DELETE'
            && str_starts_with($request->url(), 'https://openapi.example.test/inbox/message-1'));
    }
}
