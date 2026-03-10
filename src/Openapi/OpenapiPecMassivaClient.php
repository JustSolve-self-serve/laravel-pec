<?php

namespace JustSolve\LaravelPec\Openapi;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionPayload;
use JustSolve\LaravelPec\Openapi\Models\OpenapiCreateSubmissionResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiDeleteMessageResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiGetMessageResponse;
use JustSolve\LaravelPec\Openapi\Models\OpenapiHeaders;
use JustSolve\LaravelPec\Openapi\Models\OpenapiListMessagesResponse;
use RuntimeException;

class OpenapiPecMassivaClient
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
    ) {}

    private function headers(): array
    {
        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function listMessages(
        array $query = [],
        ?OpenapiHeaders $headers = null
    ): OpenapiListMessagesResponse {
        return OpenapiListMessagesResponse::fromArray(
            $this->request('GET', $this->messagesBasePath(), [
                'query' => $query,
                'headers' => $this->normalizeHeaders($headers),
            ])
        );
    }

    public function getMessage(
        string $messageUid,
        ?OpenapiHeaders $headers = null
    ): OpenapiGetMessageResponse {
        return OpenapiGetMessageResponse::fromArray(
            $this->request('GET', $this->messagePath($messageUid), [
                'headers' => $this->normalizeHeaders($headers),
            ])
        );
    }

    public function createSubmission(OpenapiCreateSubmissionPayload $payload): OpenapiCreateSubmissionResponse
    {
        return OpenapiCreateSubmissionResponse::fromArray(
            $this->request('POST', $this->submissionPath(), ['json' => $payload->toArray()])
        );
    }

    public function deleteMessage(
        string $messageUid,
        ?OpenapiHeaders $headers = null
    ): OpenapiDeleteMessageResponse {
        return OpenapiDeleteMessageResponse::fromArray(
            $this->request('DELETE', $this->messagePath($messageUid), [
                'headers' => $this->normalizeHeaders($headers),
            ])
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
        $headers = array_merge($this->headers(), $requestHeaders);

        $client = Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->withHeaders($headers)
            ->withToken($this->token);

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
     * @return array<string, string>
     */
    private function normalizeHeaders(?OpenapiHeaders $headers): array
    {
        return $headers?->toArray() ?? [];
    }

    protected function messagePath(string $messageUid): string
    {
        return $this->messagesBasePath() . '/' . rawurlencode($messageUid);
    }

    protected function messagesBasePath(): string
    {
        return '/inbox';
    }

    protected function submissionPath(): string
    {
        return '/send';
    }
}
