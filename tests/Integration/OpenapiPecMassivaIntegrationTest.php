<?php

namespace JustSolve\LaravelPec\Tests\Integration;

use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionPayload;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiDeleteMessageResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiGetMessageResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiAttachment;
use JustSolve\LaravelPec\Openapi\Models\OpenapiHeaders;
use JustSolve\LaravelPec\Openapi\Models\OpenapiListMessagesResponse;
use JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient;
use JustSolve\LaravelPec\Tests\TestCase;

class OpenapiPecMassivaIntegrationTest extends TestCase
{
    public function test_it_can_create_list_get_and_delete_a_message_against_openapi_sandbox(): void
    {
        $this->skipIfIntegrationTestsAreDisabled();

        $payload = $this->submissionPayloadFromEnv();

        $headers = $this->openapiCredentialHeaders(); 
        $client = $this->app->make(OpenapiPecMassivaClient::class);

        $creation = $client->createSubmission($payload);
        $this->assertInstanceOf(OpenapiCreateSubmissionResponse::class, $creation);
        $this->assertTrue($creation->success);
        $this->assertNotSame('', $creation->messageId);

        $response = $client->listMessages(headers: $headers);

        $this->assertInstanceOf(OpenapiListMessagesResponse::class, $response);
        $this->assertTrue($response->success);
        $this->assertNotEmpty($response->data);

        $messageId = (string) $response->data[0]->id;
        $this->assertNotSame('', $messageId);

        $message = $client->getMessage($messageId, headers: $headers);
        $this->assertInstanceOf(OpenapiGetMessageResponse::class, $message);
        $this->assertTrue($message->success);

        $deleted = $client->deleteMessage($messageId, headers: $headers);
        $this->assertInstanceOf(OpenapiDeleteMessageResponse::class, $deleted);
        $this->assertTrue($deleted->success);
    }

    private function openapiCredentialHeaders(): OpenapiHeaders
    {
        $username = env('OPENAPI_PEC_TEST_USERNAME');
        $password = env('OPENAPI_PEC_TEST_PASSWORD');
        return new OpenapiHeaders($username, $password);
    }

    private function submissionPayloadFromEnv(): OpenapiCreateSubmissionPayload
    {
        $requiredEnv = [
            'OPENAPI_PEC_TEST_SENDER' => env('OPENAPI_PEC_TEST_SENDER'),
            'OPENAPI_PEC_TEST_RECIPIENT' => env('OPENAPI_PEC_TEST_RECIPIENT'),
            'OPENAPI_PEC_TEST_USERNAME' => env('OPENAPI_PEC_TEST_USERNAME'),
            'OPENAPI_PEC_TEST_PASSWORD' => env('OPENAPI_PEC_TEST_PASSWORD'),
        ];

        foreach ($requiredEnv as $name => $value) {
            if (! is_string($value) || $value === '') {
                $this->markTestSkipped("{$name} not set.");
            }
        }

        return new OpenapiCreateSubmissionPayload(
            sender: $requiredEnv['OPENAPI_PEC_TEST_SENDER'],
            recipient: $requiredEnv['OPENAPI_PEC_TEST_RECIPIENT'],
            subject: (string) env('OPENAPI_PEC_TEST_SUBJECT', 'OpenAPI PEC integration test'),
            body: (string) env('OPENAPI_PEC_TEST_BODY', 'Automated integration test message.'),
            attachments: [
                new OpenapiAttachment(
                    'integration-test.txt',
                    base64_encode('OpenAPI PEC integration attachment')
                ),
            ],
            username: $requiredEnv['OPENAPI_PEC_TEST_USERNAME'],
            password: $requiredEnv['OPENAPI_PEC_TEST_PASSWORD']
        );
    }

    private function skipIfIntegrationTestsAreDisabled(): void
    {
        if (! filter_var((string) env('OPENAPI_PEC_RUN_INTEGRATION_TESTS', false), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('Integration test disabled. Set OPENAPI_PEC_RUN_INTEGRATION_TESTS=true to enable.');
        }
    }
}
