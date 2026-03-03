<?php

namespace JustSolve\LegalmailPec\Tests\Feature;

use Illuminate\Support\Facades\Http;
use JustSolve\LegalmailPec\Contracts\LegalmailPecClient;
use JustSolve\LegalmailPec\Services\HttpLegalmailPecClient;
use JustSolve\LegalmailPec\Tests\TestCase;
use RuntimeException;

class HttpLegalmailPecClientTest extends TestCase
{
    public function test_it_lists_messages(): void
    {
        Http::fake([
            '*' => Http::response(['data' => []], 200),
        ]);

        $client = $this->app->make(LegalmailPecClient::class);
        $response = $client->listMessages(['limit' => 10]);

        $this->assertSame(['data' => []], $response);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'GET'
                && str_contains($request->url(), '/mailbox-1/folders/folder-1/messages/999')
                && str_contains($request->url(), 'limit=10');
        });
    }

    public function test_it_gets_message(): void
    {
        Http::fake([
            '*' => Http::response(['id' => '42'], 200),
        ]);

        $client = $this->app->make(LegalmailPecClient::class);
        $response = $client->getMessage('42');

        $this->assertSame(['id' => '42'], $response);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'GET'
                && str_contains($request->url(), '/mailbox-1/folders/folder-1/messages/999/42');
        });
    }

    public function test_it_creates_submission(): void
    {
        Http::fake([
            '*' => Http::response(['submissionId' => 'sub-1'], 201),
        ]);

        $client = $this->app->make(LegalmailPecClient::class);
        $payload = ['subject' => 'Test'];

        $response = $client->createSubmission($payload);

        $this->assertSame(['submissionId' => 'sub-1'], $response);

        Http::assertSent(function ($request) use ($payload): bool {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/mailbox-1/submissions')
                && $request['subject'] === $payload['subject'];
        });
    }

    public function test_it_updates_message(): void
    {
        Http::fake([
            '*' => Http::response(['updated' => true], 200),
        ]);

        $client = $this->app->make(LegalmailPecClient::class);
        $response = $client->updateMessage('42', ['status' => 'read']);

        $this->assertSame(['updated' => true], $response);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/mailbox-1/folders/folder-1/messages/999/42')
                && $request['status'] === 'read';
        });
    }

    public function test_it_deletes_message(): void
    {
        Http::fake([
            '*' => Http::response([], 204),
        ]);

        $client = $this->app->make(LegalmailPecClient::class);
        $result = $client->deleteMessage('42');

        $this->assertTrue($result);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'DELETE'
                && str_contains($request->url(), '/mailbox-1/folders/folder-1/messages/999/42');
        });
    }

    public function test_it_throws_if_required_path_segments_are_missing(): void
    {
        $client = new HttpLegalmailPecClient(
            baseUrl: 'https://sandbox.example.test',
            token: 'token',
            timeout: 20,
            mailboxId: null,
            folderId: null,
            messageUidValidity: null,
            headers: []
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing Legalmail PEC list path parameters');

        $client->listMessages();
    }
}
