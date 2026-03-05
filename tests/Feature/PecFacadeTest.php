<?php

namespace JustSolve\LaravelPec\Tests\Feature;

use Illuminate\Support\Facades\Http;
use JustSolve\LaravelPec\Facades\Pec;
use JustSolve\LaravelPec\Services\LegalmailProviderClient;
use JustSolve\LaravelPec\Services\OpenApiPecMassivaProviderClient;
use JustSolve\LaravelPec\Tests\TestCase;

class PecFacadeTest extends TestCase
{
    public function test_it_uses_default_driver_when_calling_methods_directly(): void
    {
        Http::fake([
            '*' => Http::response(['submissionId' => 'sub-1'], 201),
        ]);

        $response = Pec::createSubmission(['subject' => 'Facade default']);

        $this->assertSame(['submissionId' => 'sub-1'], $response);

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_starts_with($request->url(), 'https://sandbox.example.test/mailbox-1/submissions'));
    }

    public function test_it_can_resolve_a_specific_driver(): void
    {
        Http::fake([
            '*' => Http::response(['ok' => true], 200),
        ]);

        $legalmailClient = Pec::driver('legalmail');
        $openApiClient = Pec::driver('openapi_pec_massiva');

        $this->assertInstanceOf(LegalmailProviderClient::class, $legalmailClient);
        $this->assertInstanceOf(OpenApiPecMassivaProviderClient::class, $openApiClient);

        Pec::driver('openapi_pec_massiva')->createSubmission(['subject' => 'Facade explicit']);

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_starts_with($request->url(), 'https://openapi.example.test/send'));
    }

}
