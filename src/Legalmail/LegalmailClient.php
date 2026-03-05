<?php

namespace JustSolve\LaravelPec\Legalmail;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LegalmailClient
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token = null,
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
        ?string $messageUidValidity = null,
        ?array $headers = null
    ): array {
        return $this->request('GET', $this->messagesBasePath($mailboxId, $folderId, $messageUidValidity), [
            'query' => $query,
            'headers' => $headers ?? [],
        ]);
    }

    public function getMessage(
        string $messageUid,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null,
        ?array $headers = null
    ): array {
        return $this->request('GET', $this->messagePath($messageUid, $mailboxId, $folderId, $messageUidValidity), [
            'headers' => $headers ?? [],
        ]);
    }

    public function createSubmission(
        array $payload,
        ?string $mailboxId = null,
        ?array $headers = null
    ): array
    {
        return $this->request('POST', $this->submissionPath($mailboxId), [
            'json' => $payload,
            'headers' => $headers ?? [],
        ]);
    }

    public function deleteMessage(
        string $messageUid,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null,
        ?array $headers = null
    ): bool {
        $this->request('DELETE', $this->messagePath($messageUid, $mailboxId, $folderId, $messageUidValidity), [
            'headers' => $headers ?? [],
        ]);

        return true;
    }

    public function updateMessage(
        string $messageUid,
        bool $seen,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null
    ): array {
        return $this->request(
            'PUT',
            $this->messagePath($messageUid, $mailboxId, $folderId, $messageUidValidity),
            ['query' => ['seen' => $seen]]
        );
    }

    /**
     * @param array{query?: array<string, mixed>, json?: array<string, mixed>, headers?: array<string, string>} $options
     * @return array<string, mixed>
     */
    protected function request(string $method, string $uri, array $options = []): array
    {
        if ($method !== 'GET' && isset($options['query']) && $options['query'] !== []) {
            $uri .= '?' . http_build_query($options['query']);
        }

        $response = match ($method) {
            'GET' => $this->client($options['headers'] ?? [])->get($uri, $options['query'] ?? []),
            'POST' => $this->client($options['headers'] ?? [])->post($uri, $options['json'] ?? []),
            'PUT' => $this->client($options['headers'] ?? [])->put($uri, $options['json'] ?? []),
            'PATCH' => $this->client($options['headers'] ?? [])->patch($uri, $options['json'] ?? []),
            'DELETE' => $this->client($options['headers'] ?? [])->delete($uri),
            default => throw new RuntimeException("Unsupported method [{$method}]."),
        };

        return $this->decodeResponse($response);
    }

    /**
     * @param array<string, string> $requestHeaders
     */
    private function client(array $requestHeaders = []): PendingRequest
    {
        $headers = array_merge($this->headers, $requestHeaders);

        $client = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->withHeaders($headers);

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

    protected function messagePath(
        string $messageUid,
        ?string $mailboxId,
        ?string $folderId,
        ?string $messageUidValidity
    ): string {
        return $this->messagesBasePath($mailboxId, $folderId, $messageUidValidity) . '/' . rawurlencode($messageUid);
    }

    protected function messagesBasePath(
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

    protected function submissionPath(?string $mailboxId): string
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
