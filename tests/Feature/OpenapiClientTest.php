<?php

namespace JustSolve\LaravelPec\Tests\Feature;

use Illuminate\Support\Facades\Http;
use JustSolve\LaravelPec\Openapi\Models\InboxSingle;
use InvalidArgumentException;
use JustSolve\LaravelPec\Openapi\Models\InboxSearch;
use JustSolve\LaravelPec\Openapi\Models\OpenapiAttachment;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionPayload;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiDeleteMessageResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiGetAccettazioneConsegnaResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiGetMessageResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiHeaders;
use JustSolve\LaravelPec\Openapi\Models\OpenapiListMessagesResponse;
use JustSolve\LaravelPec\Openapi\Models\ResponseStatus;
use JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient;
use JustSolve\LaravelPec\Tests\TestCase;

class OpenapiClientTest extends TestCase
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

    public function test_it_hydrates_typed_list_messages_response(): void
    {
        $response = OpenapiListMessagesResponse::fromArray([
            'data' => [
                [
                    'sender' => 'sender@example.test',
                    'recipient' => 'recipient@example.test',
                    'date' => '2026-03-10 10:00:00',
                    'object' => 'PEC subject',
                    'id' => 42,
                ],
            ],
            'success' => true,
            'message' => 'Ok',
            'page' => 1,
            'total' => 1,
            'n_of_pages' => 1,
        ]);

        $this->assertCount(1, $response->data);
        $this->assertInstanceOf(InboxSearch::class, $response->data[0]);
        $this->assertSame('sender@example.test', $response->data[0]->sender);
        $this->assertSame(1, $response->page);
        $this->assertSame(1, $response->numberOfPages);
    }

    public function test_it_rejects_invalid_list_messages_response_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OpenapiListMessagesResponse.data must be an array.');

        OpenapiListMessagesResponse::fromArray([
            'data' => 'invalid',
            'success' => true,
            'message' => 'Ok',
            'page' => 1,
            'total' => 1,
            'n_of_pages' => 1,
        ]);
    }

    public function test_it_hydrates_typed_get_message_response(): void
    {
        $response = OpenapiGetMessageResponse::fromArray([
            'data' => [
                'sender' => 'sender@example.test',
                'recipient' => 'recipient@example.test',
                'date' => '2026-03-10 10:00:00',
                'object' => 'PEC subject',
                'body' => 'PEC body',
            ],
            'success' => true,
            'message' => 'Ok',
        ]);

        $this->assertInstanceOf(InboxSingle::class, $response->data);
        $this->assertSame('PEC body', $response->data->body);
        $this->assertTrue($response->success);
    }

    public function test_it_hydrates_typed_get_accettazione_consegna_response(): void
    {
        $response = OpenapiGetAccettazioneConsegnaResponse::fromArray([
            'data' => [
                [
                    'sender' => 'sender@example.test',
                    'recipient' => 'recipient@example.test',
                    'date' => '2026-03-10 10:00:00',
                    'subject' => 'PEC subject',
                    'body' => 'Accepted',
                ],
            ],
            'success' => true,
            'message' => 'Ok',
        ]);

        $this->assertCount(1, $response->data);
        $this->assertInstanceOf(ResponseStatus::class, $response->data[0]);
        $this->assertSame('Accepted', $response->data[0]->body);
        $this->assertTrue($response->success);
    }

    public function test_it_rejects_invalid_get_accettazione_consegna_response_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OpenapiGetAccettazioneConsegnaResponse.success must be a boolean.');

        OpenapiGetAccettazioneConsegnaResponse::fromArray([
            'data' => [
                [
                    'sender' => 'sender@example.test',
                    'recipient' => 'recipient@example.test',
                    'date' => '2026-03-10 10:00:00',
                    'subject' => 'PEC subject',
                    'body' => 'Accepted',
                ],
            ],
            'success' => 'yes',
            'message' => 'Ok',
        ]);
    }

    public function test_it_rejects_invalid_get_message_response_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OpenapiGetMessageResponse.success must be a boolean.');

        OpenapiGetMessageResponse::fromArray([
            'data' => [
                'sender' => 'sender@example.test',
                'recipient' => 'recipient@example.test',
                'date' => '2026-03-10 10:00:00',
                'object' => 'PEC subject',
                'body' => 'PEC body',
            ],
            'success' => 'yes',
            'message' => 'Ok',
        ]);
    }

    public function test_it_hydrates_typed_delete_message_response(): void
    {
        $response = OpenapiDeleteMessageResponse::fromArray([
            'success' => true,
            'message' => 'Deleted',
        ]);

        $this->assertTrue($response->success);
        $this->assertSame('Deleted', $response->message);
    }

    public function test_it_rejects_invalid_delete_message_response_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OpenapiDeleteMessageResponse.success must be a boolean.');

        OpenapiDeleteMessageResponse::fromArray([
            'success' => 'yes',
            'message' => 'Deleted',
        ]);
    }

    public function test_openapi_client_returns_typed_create_submission_response(): void
    {
        $baseUrl = $this->openapiPecMassivaBaseUrl();

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'message' => 'Queued',
                'message_id' => 'message-1',
                'sent' => 1,
            ], 201),
        ]);

        $client = $this->app->make(OpenapiPecMassivaClient::class);

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

        $response = $client->createSubmission($payload);

        $this->assertTrue($response->success);
        $this->assertSame('Queued', $response->message);
        $this->assertSame('message-1', $response->messageId);
        $this->assertSame(1, $response->sent);

        Http::assertSent(function ($request) use ($baseUrl): bool {
            if ($request->method() !== 'POST' || ! str_starts_with($request->url(), "{$baseUrl}/send")) {
                return false;
            }

            return $request['sender'] === 'sender@example.test'
                && $request['recipient'] === ['recipient@example.test']
                && $request['attachments'][0]['name'] === 'invoice.pdf';
        });
    }

    public function test_openapi_client_returns_typed_list_messages_response(): void
    {
        $baseUrl = $this->openapiPecMassivaBaseUrl();

        Http::fake([
            '*' => Http::response([
                'data' => [
                    [
                        'sender' => 'sender@example.test',
                        'recipient' => 'recipient@example.test',
                        'date' => '2026-03-10 10:00:00',
                        'object' => 'PEC subject',
                        'id' => 42,
                    ],
                ],
                'success' => true,
                'message' => 'Ok',
                'page' => 1,
                'total' => 1,
                'n_of_pages' => 1,
            ], 200),
        ]);

        $client = $this->app->make(OpenapiPecMassivaClient::class);

        $response = $client->listMessages(['q' => 'test']);

        $this->assertInstanceOf(OpenapiListMessagesResponse::class, $response);
        $this->assertCount(1, $response->data);
        $this->assertInstanceOf(InboxSearch::class, $response->data[0]);
        $this->assertSame('PEC subject', $response->data[0]->object);

        Http::assertSent(function ($request) use ($baseUrl): bool {
            return $request->method() === 'GET'
                && $request->url() === "{$baseUrl}/inbox?q=test";
        });
    }

    public function test_openapi_client_returns_typed_get_message_response(): void
    {
        $baseUrl = $this->openapiPecMassivaBaseUrl();

        Http::fake([
            '*' => Http::response([
                'data' => [
                    'sender' => 'sender@example.test',
                    'recipient' => 'recipient@example.test',
                    'date' => '2026-03-10 10:00:00',
                    'object' => 'PEC subject',
                    'body' => 'PEC body',
                ],
                'success' => true,
                'message' => 'Ok',
            ], 200),
        ]);

        $client = $this->app->make(OpenapiPecMassivaClient::class);

        $response = $client->getMessage('message-1');

        $this->assertInstanceOf(OpenapiGetMessageResponse::class, $response);
        $this->assertInstanceOf(InboxSingle::class, $response->data);
        $this->assertSame('PEC subject', $response->data->object);

        Http::assertSent(function ($request) use ($baseUrl): bool {
            return $request->method() === 'GET'
                && $request->url() === "{$baseUrl}/inbox/message-1";
        });
    }

    public function test_openapi_client_returns_typed_get_accettazione_consegna_response(): void
    {
        $baseUrl = $this->openapiPecMassivaBaseUrl();

        Http::fake([
            '*' => Http::response([
                'data' => [
                    [
                        'sender' => 'sender@example.test',
                        'recipient' => 'recipient@example.test',
                        'date' => '2026-03-10 10:00:00',
                        'subject' => 'PEC subject',
                        'body' => 'Accepted',
                    ],
                ],
                'success' => true,
                'message' => 'Ok',
            ], 200),
        ]);

        $client = $this->app->make(OpenapiPecMassivaClient::class);

        $response = $client->getAccettazioneConsegna('message-1');

        $this->assertInstanceOf(OpenapiGetAccettazioneConsegnaResponse::class, $response);
        $this->assertCount(1, $response->data);
        $this->assertInstanceOf(ResponseStatus::class, $response->data[0]);
        $this->assertSame('PEC subject', $response->data[0]->subject);
        $this->assertSame('Accepted', $response->data[0]->body);

        Http::assertSent(function ($request) use ($baseUrl): bool {
            return $request->method() === 'GET'
                && $request->url() === "{$baseUrl}/send/message-1";
        });
    }

    public function test_openapi_client_returns_typed_delete_message_response(): void
    {
        $baseUrl = $this->openapiPecMassivaBaseUrl();

        Http::fake([
            '*' => Http::response([
                'success' => true,
                'message' => 'Deleted',
            ], 200),
        ]);

        $client = $this->app->make(OpenapiPecMassivaClient::class);

        $response = $client->deleteMessage('message-1');

        $this->assertInstanceOf(OpenapiDeleteMessageResponse::class, $response);
        $this->assertTrue($response->success);
        $this->assertSame('Deleted', $response->message);

        Http::assertSent(function ($request) use ($baseUrl): bool {
            return $request->method() === 'DELETE'
                && $request->url() === "{$baseUrl}/inbox/message-1";
        });
    }

    public function test_openapi_client_sends_openapi_headers_for_list_get_and_delete(): void
    {
        $baseUrl = $this->openapiPecMassivaBaseUrl();

        Http::fake(function ($request) use ($baseUrl) {
            if ($request->method() === 'GET' && $request->url() === "{$baseUrl}/inbox") {
                return Http::response([
                    'data' => [],
                    'success' => true,
                    'message' => 'Ok',
                    'page' => 1,
                    'total' => 0,
                    'n_of_pages' => 0,
                ], 200);
            }

            if ($request->method() === 'GET' && $request->url() === "{$baseUrl}/inbox/message-1") {
                return Http::response([
                    'data' => [
                        'sender' => 'sender@example.test',
                        'recipient' => 'recipient@example.test',
                        'date' => '2026-03-10 10:00:00',
                        'object' => 'PEC subject',
                        'body' => 'PEC body',
                    ],
                    'success' => true,
                    'message' => 'Ok',
                ], 200);
            }

            if ($request->method() === 'GET' && $request->url() === "{$baseUrl}/send/message-1") {
                return Http::response([
                    'data' => [
                        [
                            'sender' => 'sender@example.test',
                            'recipient' => 'recipient@example.test',
                            'date' => '2026-03-10 10:00:00',
                            'subject' => 'PEC subject',
                            'body' => 'Accepted',
                        ],
                    ],
                    'success' => true,
                    'message' => 'Ok',
                ], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === "{$baseUrl}/inbox/message-1") {
                return Http::response([
                    'success' => true,
                    'message' => 'Deleted',
                ], 200);
            }

            return Http::response([], 404);
        });

        $client = $this->app->make(OpenapiPecMassivaClient::class);

        $headers = new OpenapiHeaders('openapi-user', 'openapi-pass');

        $client->listMessages(headers: $headers);
        $client->getMessage('message-1', headers: $headers);
        $client->getAccettazioneConsegna('message-1', headers: $headers);
        $client->deleteMessage('message-1', headers: $headers);

        Http::assertSentCount(4);
        Http::assertSent(function ($request): bool {
            return $request->hasHeader('x-username', 'openapi-user')
                && $request->hasHeader('x-password', 'openapi-pass');
        });
    }
}
