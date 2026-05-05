<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class OpenapiGetPecAddressResponse
{
    /**
     * @param array<int, Pec>|null $data
     */
    public function __construct(
        public readonly ?array $data,
        public readonly bool $success,
        public readonly string $message,
        public readonly ?int $error = null
    ) {
    }

    /**
     * @param array{
     *   data: array<int, array{pec?: string|null, history: array<int, array{pec: string, timestamp: int}|PecHistoryItem>}|Pec>|null,
     *   success: bool,
     *   message: string,
     *   error?: int|null
     * } $data
     */
    public static function fromArray(array $data): static
    {
        if (! array_key_exists('data', $data) || (! is_null($data['data']) && ! is_array($data['data']))) {
            throw new InvalidArgumentException('OpenapiGetPecAddressResponse.data must be an array or null.');
        }

        $items = null;
        if (is_array($data['data'])) {
            $items = [];
            foreach ($data['data'] as $item) {
                if ($item instanceof Pec) {
                    $items[] = $item;
                    continue;
                }

                if (! is_array($item)) {
                    throw new InvalidArgumentException('OpenapiGetPecAddressResponse.data items must be Pec or array.');
                }

                $items[] = Pec::fromArray($item);
            }
        }

        if (! isset($data['success']) || ! is_bool($data['success'])) {
            throw new InvalidArgumentException('OpenapiGetPecAddressResponse.success must be a boolean.');
        }

        if (! isset($data['message']) || ! is_string($data['message'])) {
            throw new InvalidArgumentException('OpenapiGetPecAddressResponse.message must be a string.');
        }

        if (array_key_exists('error', $data) && ! is_null($data['error']) && ! is_int($data['error'])) {
            throw new InvalidArgumentException('OpenapiGetPecAddressResponse.error must be an integer or null.');
        }

        return new static(
            data: $items,
            success: $data['success'],
            message: $data['message'],
            error: $data['error'] ?? null
        );
    }

    /**
     * @return array{
     *   data: array<int, array{pec: string|null, history: array<int, array{pec: string, timestamp: int}>}>|null,
     *   success: bool,
     *   message: string,
     *   error?: int|null
     * }
     */
    public function toArray(): array
    {
        $data = [
            'data' => $this->data === null
                ? null
                : array_map(
                    static fn (Pec $item): array => $item->toArray(),
                    $this->data
                ),
            'success' => $this->success,
            'message' => $this->message,
        ];

        if ($this->error !== null) {
            $data['error'] = $this->error;
        }

        return $data;
    }
}
