<?php

namespace JustSolve\LaravelPec\Tests\Feature;

use Illuminate\Support\Facades\Http;
use JustSolve\LaravelPec\Facades\Legalmail;
use JustSolve\LaravelPec\Facades\OpenapiPecMassiva;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionPayload;
use JustSolve\LaravelPec\Tests\TestCase;

class ClientFacadesTest extends TestCase
{
    public function test_legalmail_facade_resolves_legalmail_client(): void
    {
        $baseUrl = $this->legalmailBaseUrl();

        Http::fake([
            '*' => Http::response(['data' => []], 200),
        ]);

        $response = Legalmail::listMessages();

        $this->assertSame(['data' => []], $response);

        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && str_starts_with($request->url(), "{$baseUrl}/mailbox-1/folders/folder-1/messages/999"));
    }

    public function test_openapi_facade_resolves_openapi_client(): void
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

        $payload = new OpenapiCreateSubmissionPayload(
            sender: 'sender@example.test',
            recipient: 'recipient@example.test',
            subject: 'Hello',
            body: 'Body',
            attachments: [],
            username: 'username',
            password: 'password'
        );

        $response = OpenapiPecMassiva::createSubmission($payload);

        $this->assertTrue($response->success);
        $this->assertSame('message-1', $response->messageId);

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_starts_with($request->url(), "{$baseUrl}/send"));
    }
}
