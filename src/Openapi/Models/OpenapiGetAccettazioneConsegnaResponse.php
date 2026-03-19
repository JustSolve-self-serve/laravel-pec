<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class OpenapiGetAccettazioneConsegnaResponse
{
    /**
     * @param array<int, ResponseStatus> $data
     */
    public function __construct(
        public readonly array $data,
        public readonly bool $success,
        public readonly string $message
    ) {
    }

    /**
     * @param array{
     *   data: array<int, array{sender: string, recipient: string, date: string, subject: string, body: string}|ResponseStatus>,
     *   success: bool,
     *   message: string
     * } $data
     */
    public static function fromArray(array $data): static
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            throw new InvalidArgumentException('OpenapiGetAccettazioneConsegnaResponse.data must be an array.');
        }

        $items = [];
        foreach ($data['data'] as $item) {
            if ($item instanceof ResponseStatus) {
                $items[] = $item;
                continue;
            }

            if (! is_array($item)) {
                throw new InvalidArgumentException('OpenapiGetAccettazioneConsegnaResponse.data items must be ResponseStatus or array.');
            }

            $items[] = ResponseStatus::fromArray($item);
        }

        if (! isset($data['success']) || ! is_bool($data['success'])) {
            throw new InvalidArgumentException('OpenapiGetAccettazioneConsegnaResponse.success must be a boolean.');
        }

        if (! isset($data['message']) || ! is_string($data['message'])) {
            throw new InvalidArgumentException('OpenapiGetAccettazioneConsegnaResponse.message must be a string.');
        }

        return new static(
            data: $items,
            success: $data['success'],
            message: $data['message']
        );
    }

    /**
     * @return array{
     *   data: array<int, array{sender: string, recipient: string, date: string, subject: string, body: string}>,
     *   success: bool,
     *   message: string
     * }
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(
                static fn (ResponseStatus $item): array => $item->toArray(),
                $this->data
            ),
            'success' => $this->success,
            'message' => $this->message,
        ];
    }
}
