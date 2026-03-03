<?php

namespace JustSolve\LegalmailPec\Tests\Integration;

use JustSolve\LegalmailPec\Contracts\LegalmailPecClient;
use JustSolve\LegalmailPec\Tests\TestCase;

class LegalmailPecSandboxTest extends TestCase
{
    public function test_it_can_list_messages_against_sandbox(): void
    {
        if (! filter_var((string) env('LEGALMAIL_PEC_RUN_INTEGRATION_TESTS', false), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('Integration test disabled. Set LEGALMAIL_PEC_RUN_INTEGRATION_TESTS=true to enable.');
        }

        $client = $this->app->make(LegalmailPecClient::class);
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

        $client = $this->app->make(LegalmailPecClient::class);
        $response = $client->getMessage($messageUid);

        $this->assertIsArray($response);
    }
}
