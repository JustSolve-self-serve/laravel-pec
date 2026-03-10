<?php

namespace JustSolve\LaravelPec\Tests\Integration;

use JustSolve\LaravelPec\Legalmail\LegalmailClient;
use JustSolve\LaravelPec\Tests\TestCase;

class LegalmailIntegrationTest extends TestCase
{
    public function test_it_can_list_messages_against_legalmail_sandbox(): void
    {
        $this->skipIfIntegrationTestsAreDisabled();

        $client = $this->app->make(LegalmailClient::class);
        $response = $client->listMessages();

        $this->assertIsArray($response);
    }

    private function skipIfIntegrationTestsAreDisabled(): void
    {
        if (! filter_var((string) env('LEGALMAIL_PEC_RUN_INTEGRATION_TESTS', false), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('Integration test disabled. Set LEGALMAIL_PEC_RUN_INTEGRATION_TESTS=true to enable.');
        }
    }
}
