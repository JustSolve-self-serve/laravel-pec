<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class OpenapiGetMessageResponse
{
    /**
     * @param array<int, InboxSingle> $data
     */
    public function __construct(
        public readonly array $data,
        public readonly bool $success,
        public readonly string $message
    ) {
    }

    /**
     * @param array{
     *   data: array<int, array{sender: string, recipient: string, date: string, object: string, body: string}|InboxSingle>,
     *   success: bool,
     *   message: string
     * } $data
     */
    public static function fromArray(array $data): static
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            throw new InvalidArgumentException('OpenapiGetMessageResponse.data must be an array.');
        }

        $items = [];
        foreach ($data['data'] as $item) {
            if ($item instanceof InboxSingle) {
                $items[] = $item;
                continue;
            }

            if (! is_array($item)) {
                throw new InvalidArgumentException('OpenapiGetMessageResponse.data items must be InboxSingle or array.');
            }

            $items[] = InboxSingle::fromArray($item);
        }

        if (! isset($data['success']) || ! is_bool($data['success'])) {
            throw new InvalidArgumentException('OpenapiGetMessageResponse.success must be a boolean.');
        }

        if (! isset($data['message']) || ! is_string($data['message'])) {
            throw new InvalidArgumentException('OpenapiGetMessageResponse.message must be a string.');
        }

        return new static(
            data: $items,
            success: $data['success'],
            message: $data['message']
        );
    }

    /**
     * @return array{
     *   data: array<int, array{sender: string, recipient: string, date: string, object: string, body: string}>,
     *   success: bool,
     *   message: string
     * }
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(
                static fn (InboxSingle $item): array => $item->toArray(),
                $this->data
            ),
            'success' => $this->success,
            'message' => $this->message,
        ];
    }
}
