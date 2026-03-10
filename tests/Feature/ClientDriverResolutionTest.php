<?php

namespace JustSolve\LaravelPec\Tests\Feature;

use Illuminate\Support\Facades\Http;
use JustSolve\LaravelPec\Legalmail\LegalmailClient;
use JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionPayload;
use JustSolve\LaravelPec\Tests\TestCase;

class ClientDriverResolutionTest extends TestCase
{
    public function test_it_can_resolve_both_clients_directly_from_container(): void
    {
        $legalmailClient = $this->app->make(LegalmailClient::class);
        $openApiClient = $this->app->make(OpenapiPecMassivaClient::class);

        $this->assertInstanceOf(LegalmailClient::class, $legalmailClient);
        $this->assertInstanceOf(OpenapiPecMassivaClient::class, $openApiClient);
    }

    public function test_legalmail_client_uses_its_provider_specific_uris(): void
    {
        Http::fake([
            '*' => Http::response(['ok' => true], 200),
        ]);

        $client = $this->app->make(LegalmailClient::class);
        $client->listMessages();
        $client->getMessage('message-1');
        $client->createSubmission(['subject' => 'Hello']);
        $client->deleteMessage('message-1');

        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://sandbox.example.test/mailbox-1/folders/folder-1/messages/999'));
        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && str_starts_with($request->url(), 'https://sandbox.example.test/mailbox-1/folders/folder-1/messages/999/message-1'));
        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_starts_with($request->url(), 'https://sandbox.example.test/mailbox-1/submissions'));
        Http::assertSent(fn ($request): bool => $request->method() === 'DELETE'
            && str_starts_with($request->url(), 'https://sandbox.example.test/mailbox-1/folders/folder-1/messages/999/message-1'));
    }

    public function test_openapi_pec_massiva_uses_its_provider_specific_uris(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'GET' && $request->url() === 'https://openapi.example.test/inbox') {
                return Http::response([
                    'data' => [],
                    'success' => true,
                    'message' => 'Ok',
                    'page' => 1,
                    'total' => 0,
                    'n_of_pages' => 0,
                ], 200);
            }

            if ($request->method() === 'GET' && $request->url() === 'https://openapi.example.test/inbox/message-1') {
                return Http::response([
                    'data' => [
                        [
                            'sender' => 'sender@example.test',
                            'recipient' => 'recipient@example.test',
                            'date' => '2026-03-10 10:00:00',
                            'object' => 'PEC subject',
                            'body' => 'PEC body',
                        ],
                    ],
                    'success' => true,
                    'message' => 'Ok',
                ], 200);
            }

            if ($request->method() === 'POST' && $request->url() === 'https://openapi.example.test/send') {
                return Http::response([
                    'success' => true,
                    'message' => 'Queued',
                    'message_id' => 'message-1',
                    'sent' => 1,
                ], 200);
            }

            if ($request->method() === 'DELETE' && $request->url() === 'https://openapi.example.test/inbox/message-1') {
                return Http::response([
                    'success' => true,
                    'message' => 'Deleted',
                ], 200);
            }

            return Http::response(['ok' => true], 200);
        });

        $client = $this->app->make(OpenapiPecMassivaClient::class);

        $client->listMessages();
        $client->getMessage('message-1');
        $client->createSubmission(new OpenapiCreateSubmissionPayload(
            sender: 'sender@example.test',
            recipient: 'recipient@example.test',
            subject: 'Hello',
            body: 'Body',
            attachments: [],
            username: 'username',
            password: 'password'
        ));
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
