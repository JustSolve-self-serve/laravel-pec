<?php

namespace JustSolve\LaravelPec\Openapi;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JustSolve\LaravelPec\Openapi\Models\OpenapiGetPecAddressResponse;
use RuntimeException;

class OpenapiCompanyClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
    ) {}

    public function getPecAddress(string $code): OpenapiGetPecAddressResponse
    {
        return OpenapiGetPecAddressResponse::fromArray(
            $this->request('GET', $this->pecAddressPath($code))
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function request(string $method, string $uri): array
    {
        $response = match ($method) {
            'GET' => $this->client()->get($uri),
            default => throw new RuntimeException("Unsupported method [{$method}]."),
        };

        return $this->decodeResponse($response);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->withHeaders($this->headers())
            ->withToken($this->token);
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
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

    protected function pecAddressPath(string $code): string
    {
        return '/IT-pec/' . rawurlencode($code);
    }
}
