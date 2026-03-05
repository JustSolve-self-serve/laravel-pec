<?php

namespace JustSolve\LaravelPec\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JustSolve\LaravelPec\Contracts\CreateSubmissionPayload;
use JustSolve\LaravelPec\Contracts\PecClient;
use JustSolve\LaravelPec\Contracts\RequestHeaders;
use RuntimeException;

class OpenApiPecMassivaClient implements PecClient
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
        ?string $messageUidValidity = null,
        array|RequestHeaders|null $headers = null
    ): array {
        return $this->request('GET', $this->messagesBasePath($mailboxId, $folderId, $messageUidValidity), [
            'query' => $query,
            'headers' => $this->normalizeHeaders($headers),
        ]);
    }

    public function getMessage(
        string $messageUid,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null,
        array|RequestHeaders|null $headers = null
    ): array {
        return $this->request('GET', $this->messagePath($messageUid, $mailboxId, $folderId, $messageUidValidity), [
            'headers' => $this->normalizeHeaders($headers),
        ]);
    }

    public function createSubmission(
        array|CreateSubmissionPayload $payload,
        ?string $mailboxId = null,
        array|RequestHeaders|null $headers = null
    ): array
    {
        if ($payload instanceof CreateSubmissionPayload) {
            $payload = $payload->toArray();
        }

        return $this->request('POST', $this->submissionPath($mailboxId), [
            'json' => $payload,
            'headers' => $this->normalizeHeaders($headers),
        ]);
    }

    public function deleteMessage(
        string $messageUid,
        ?string $mailboxId = null,
        ?string $folderId = null,
        ?string $messageUidValidity = null,
        array|RequestHeaders|null $headers = null
    ): bool {
        $this->request('DELETE', $this->messagePath($messageUid, $mailboxId, $folderId, $messageUidValidity), [
            'headers' => $this->normalizeHeaders($headers),
        ]);

        return true;
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
            ->timeout($this->timeout)
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

    /**
     * @param array<string, string>|RequestHeaders|null $headers
     * @return array<string, string>
     */
    private function normalizeHeaders(array|RequestHeaders|null $headers): array
    {
        if ($headers instanceof RequestHeaders) {
            return $headers->toArray();
        }

        return $headers ?? [];
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
        return '/inbox';
    }

    protected function submissionPath(?string $mailboxId): string
    {
        return '/send';
    }
}
