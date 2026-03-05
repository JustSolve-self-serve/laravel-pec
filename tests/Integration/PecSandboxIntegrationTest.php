<?php

namespace JustSolve\LaravelPec\Tests\Integration;

use JustSolve\LaravelPec\Contracts\PecClient;
use JustSolve\LaravelPec\Tests\TestCase;

class PecSandboxIntegrationTest extends TestCase
{
    public function test_it_can_list_messages_against_sandbox(): void
    {
        if (! filter_var((string) env('LEGALMAIL_PEC_RUN_INTEGRATION_TESTS', false), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('Integration test disabled. Set LEGALMAIL_PEC_RUN_INTEGRATION_TESTS=true to enable.');
        }

        $client = $this->app->make(PecClient::class);
        $response = $client->listMessages();

        $this->assertIsArray($response);
    }

    public function test_it_can_get_message_against_sandbox_when_message_uid_is_provided(): void
    {
        if (! filter_var((string) env('LEGALMAIL_PEC_RUN_INTEGRATION_TESTS', false), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('Integration test disabled. Set LEGALMAIL_PEC_RUN_INTEGRATION_TESTS=true to enable.');
        }

        $messageUid = env('LEGALMAIL_PEC_TEST_MESSAGE_UID');

        if (! is_string($messageUid) || $messageUid === '') {
            $this->markTestSkipped('LEGALMAIL_PEC_TEST_MESSAGE_UID not set.');
        }

        $client = $this->app->make(PecClient::class);
        $response = $client->getMessage($messageUid);

        $this->assertIsArray($response);
    }
}
