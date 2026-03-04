<?php

namespace JustSolve\LegalmailPec\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JustSolve\LegalmailPec\Contracts\PecClient;
use RuntimeException;

abstract class AbstractHttpPecClient implements PecClient
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token = null,
        private readonly int $timeout = 20,
        private readonly ?string $mailboxId = null,
        private readonly ?string $folderId = null,
        private readonly ?string $messageUidValidity = null,
        private readonly array $headers = [],
    ) {
    }

    public function listMessages(
        array $query = [],
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null
    ): array {
        return $this->request('GET', $this->messagesBasePath($mailboxId, $folderId, $messageUidValidity), ['query' => $query]);
    }

    public function getMessage(
        string $messageUid,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null
    ): array {
        return $this->request('GET', $this->messagePath($messageUid, $mailboxId, $folderId, $messageUidValidity));
    }

    public function createSubmission(array $payload, ?string $mailboxId = null): array
    {
        return $this->request('POST', $this->submissionPath($mailboxId), ['json' => $payload]);
    }

    public function updateMessage(
        string $messageUid,
        array $payload,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null
    ): array {
        return $this->request(
            'PUT',
            $this->messagePath($messageUid, $mailboxId, $folderId, $messageUidValidity),
            ['json' => $payload]
        );
    }

    public function deleteMessage(
        string $messageUid,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null
    ): bool {
        $this->request('DELETE', $this->messagePath($messageUid, $mailboxId, $folderId, $messageUidValidity));

        return true;
    }

    /**
     * @param array{query?: array<string, mixed>, json?: array<string, mixed>} $options
     * @return array<string, mixed>
     */
    private function request(string $method, string $uri, array $options = []): array
    {
        $response = match ($method) {
            'GET' => $this->client()->get($uri, $options['query'] ?? []),
            'POST' => $this->client()->post($uri, $options['json'] ?? []),
            'PUT' => $this->client()->put($uri, $options['json'] ?? []),
            'PATCH' => $this->client()->patch($uri, $options['json'] ?? []),
            'DELETE' => $this->client()->delete($uri),
            default => throw new RuntimeException("Unsupported method [{$method}]."),
        };

        return $this->decodeResponse($response);
    }

    private function client(): PendingRequest
    {
        $client = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->timeout($this->timeout)
            ->withHeaders($this->headers);

        if ($this->token !== null && $this->token !== '') {
            $client = $client->withToken($this->token);
        }

        return $client;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json() ?? [];
        }

        $message = sprintf(
            'PEC request failed with status [%d]: %s',
            $response->status(),
            $response->body()
        );

        throw new RuntimeException($message);
    }

    private function messagePath(
        string $messageUid,
        ?string $mailboxId,
        ?string $folderId,
        ?string $messageUidValidity
    ): string {
        return $this->messagesBasePath($mailboxId, $folderId, $messageUidValidity) . '/' . rawurlencode($messageUid);
    }

    private function messagesBasePath(
        ?string $mailboxId,
        ?string $folderId,
        ?string $messageUidValidity
    ): string {
        $resolvedMailboxId = $mailboxId ?? $this->mailboxId;
        $resolvedFolderId = $folderId ?? $this->folderId;
        $resolvedMessageUidValidity = $messageUidValidity ?? $this->messageUidValidity;

        if ($resolvedMailboxId === null || $resolvedFolderId === null || $resolvedMessageUidValidity === null) {
            throw new RuntimeException(
                'Missing PEC message path parameters. Configure mailbox_id, folder_id, and message_uid_validity or pass them explicitly.'
            );
        }

        return sprintf(
            '/%s/folders/%s/messages/%s',
            rawurlencode($resolvedMailboxId),
            rawurlencode($resolvedFolderId),
            rawurlencode($resolvedMessageUidValidity)
        );
    }

    private function submissionPath(?string $mailboxId): string
    {
        $resolvedMailboxId = $mailboxId ?? $this->mailboxId;

        if ($resolvedMailboxId === null) {
            throw new RuntimeException(
                'Missing PEC submission path parameter. Configure mailbox_id or pass it explicitly.'
            );
        }

        return sprintf('/%s/submissions', rawurlencode($resolvedMailboxId));
    }
}
