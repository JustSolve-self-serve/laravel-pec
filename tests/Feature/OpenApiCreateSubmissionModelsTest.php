<?php

namespace JustSolve\LaravelPec\Tests\Feature;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use JustSolve\LaravelPec\OpenApi\Models\OpenapiAttachment;
use JustSolve\LaravelPec\OpenApi\Models\OpenapiCreateSubmissionPayload;
use JustSolve\LaravelPec\OpenApi\Models\OpenapiCreateSubmissionResponse;
use JustSolve\LaravelPec\OpenApi\Models\OpenapiHeaders;
use JustSolve\LaravelPec\OpenApi\OpenApiPecMassivaClient;
use JustSolve\LaravelPec\Tests\TestCase;

class OpenApiCreateSubmissionModelsTest extends TestCase
{
    public function test_it_serializes_payload_with_attachment_models(): void
    {
        $payload = new OpenapiCreateSubmissionPayload(
            sender: 'sender@example.test',
            recipient: ['recipient-1@example.test', 'recipient-2@example.test'],
            subject: 'Test subject',
            body: 'Test body',
            attachments: [
                new OpenapiAttachment('invoice.pdf', 'YmFzZTY0'),
            ],
            username: 'api-username',
            password: 'api-password'
        );

        $this->assertSame(
            [
                'sender' => 'sender@example.test',
                'recipient' => ['recipient-1@example.test', 'recipient-2@example.test'],
                'subject' => 'Test subject',
                'body' => 'Test body',
                'attachments' => [
                    ['name' => 'invoice.pdf', 'file' => 'YmFzZTY0'],
                ],
                'username' => 'api-username',
                'password' => 'api-password',
            ],
            $payload->toArray()
        );
    }

    public function test_it_builds_payload_model_from_array(): void
    {
        $payload = OpenapiCreateSubmissionPayload::fromArray([
            'sender' => 'sender@example.test',
            'recipient' => 'recipient@example.test',
            'subject' => 'Test subject',
            'body' => 'Test body',
            'attachments' => [
                ['name' => 'invoice.pdf', 'file' => 'YmFzZTY0'],
            ],
            'username' => 'api-username',
            'password' => 'api-password',
        ]);

        $this->assertSame('recipient@example.test', $payload->recipient);
        $this->assertCount(1, $payload->attachments);
        $this->assertSame('invoice.pdf', $payload->attachments[0]->name);
    }

    public function test_it_rejects_invalid_response_model_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OpenapiCreateSubmissionResponse.success must be a boolean.');

        OpenapiCreateSubmissionResponse::fromArray([
            'success' => 'yes',
            'message' => 'Queued',
            'message_id' => 'message-1',
            'sent' => 1,
        ]);
    }

    public function test_it_serializes_openapi_headers_model(): void
    {
        $headers = OpenapiHeaders::fromArray([
            'x-username' => 'openapi-user',
            'x-password' => 'openapi-pass',
        ]);

        $this->assertSame(
            [
                'x-username' => 'openapi-user',
                'x-password' => 'openapi-pass',
            ],
            $headers->toArray()
        );
    }

    public function test_openapi_client_works_with_payload_model_via_array_conversion(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => true,
                'message' => 'Queued',
                'message_id' => 'message-1',
                'sent' => 1,
            ], 201),
        ]);

        $client = $this->app->make(OpenApiPecMassivaClient::class);

        $payload = OpenapiCreateSubmissionPayload::fromArray([
            'sender' => 'sender@example.test',
            'recipient' => ['recipient@example.test'],
            'subject' => 'Test subject',
            'body' => 'Test body',
            'attachments' => [
                ['name' => 'invoice.pdf', 'file' => 'YmFzZTY0'],
            ],
            'username' => 'api-username',
            'password' => 'api-password',
        ]);

        $response = OpenapiCreateSubmissionResponse::fromArray(
            $client->createSubmission($payload)
        );

        $this->assertTrue($response->success);
        $this->assertSame('Queued', $response->message);
        $this->assertSame('message-1', $response->messageId);
        $this->assertSame(1, $response->sent);

        Http::assertSent(function ($request): bool {
            if ($request->method() !== 'POST' || ! str_starts_with($request->url(), 'https://openapi.example.test/send')) {
                return false;
            }

            return $request['sender'] === 'sender@example.test'
                && $request['recipient'] === ['recipient@example.test']
                && $request['attachments'][0]['name'] === 'invoice.pdf';
        });
    }

    public function test_openapi_client_sends_openapi_headers_for_list_get_and_delete(): void
    {
        Http::fake([
            '*' => Http::response(['ok' => true], 200),
        ]);

        $client = $this->app->make(OpenApiPecMassivaClient::class);

        $headers = new OpenapiHeaders('openapi-user', 'openapi-pass');

        $client->listMessages(headers: $headers);
        $client->getMessage('message-1', headers: $headers);
        $client->deleteMessage('message-1', headers: $headers);

        Http::assertSentCount(3);
        Http::assertSent(function ($request): bool {
            return $request->hasHeader('x-username', 'openapi-user')
                && $request->hasHeader('x-password', 'openapi-pass');
        });
    }
}
