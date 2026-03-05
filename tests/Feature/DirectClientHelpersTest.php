<?php

namespace JustSolve\LaravelPec\Tests\Feature;

use JustSolve\LaravelPec\Services\LegalmailClient;
use JustSolve\LaravelPec\Services\OpenApiPecMassivaClient;
use JustSolve\LaravelPec\Tests\TestCase;

class DirectClientHelpersTest extends TestCase
{
    public function test_it_resolves_legalmail_client_helper(): void
    {
        $client = legalmail_client();

        $this->assertInstanceOf(LegalmailClient::class, $client);
    }

    public function test_it_resolves_openapi_client_helper(): void
    {
        $client = openapi_pec_massiva_client();

        $this->assertInstanceOf(OpenApiPecMassivaClient::class, $client);
    }
}
