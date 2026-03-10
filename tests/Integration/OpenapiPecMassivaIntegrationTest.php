<?php

namespace JustSolve\LaravelPec\Tests\Integration;

use JustSolve\LaravelPec\Openapi\Models\OpenapiGetMessageResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiListMessagesResponse;
use JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient;
use JustSolve\LaravelPec\Tests\TestCase;

class OpenapiPecMassivaIntegrationTest extends TestCase
{
    public function test_it_can_list_messages_against_openapi_sandbox(): void
    {
        $this->skipIfIntegrationTestsAreDisabled();

        $client = $this->app->make(OpenapiPecMassivaClient::class);
        $response = $client->listMessages();

        $this->assertInstanceOf(OpenapiListMessagesResponse::class, $response);
    }

    private function skipIfIntegrationTestsAreDisabled(): void
    {
        if (! filter_var((string) env('OPENAPI_PEC_RUN_INTEGRATION_TESTS', false), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('Integration test disabled. Set OPENAPI_PEC_RUN_INTEGRATION_TESTS=true to enable.');
        }
    }
}
