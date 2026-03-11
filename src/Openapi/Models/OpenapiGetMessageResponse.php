<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class OpenapiGetMessageResponse
{
    /**
     * @param InboxSingle $data
     */
    public function __construct(
        public readonly InboxSingle $data,
        public readonly bool $success,
        public readonly string $message
    ) {
    }

    /**
     * @param array{
     *   data: InboxSingle,
     *   success: bool,
     *   message: string
     * } $data
     */
    public static function fromArray(array $data): static
    {
        if (! isset($data['data'])) {
            throw new InvalidArgumentException('OpenapiGetMessageResponse.data must be set.');
        }

        if (! ($data['data'] instanceof InboxSingle) && ! is_array($data['data'])) {
            throw new InvalidArgumentException('OpenapiGetMessageResponse.data must be an InboxSingle or an array.');
        }

        $inboxSingle = $data['data'] instanceof InboxSingle
            ? $data['data']
            : InboxSingle::fromArray($data['data']);

        if (! isset($data['success']) || ! is_bool($data['success'])) {
            throw new InvalidArgumentException('OpenapiGetMessageResponse.success must be a boolean.');
        }

        if (! isset($data['message']) || ! is_string($data['message'])) {
            throw new InvalidArgumentException('OpenapiGetMessageResponse.message must be a string.');
        }

        return new static(
            data: $inboxSingle,
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
            'data'    => $this->data->toArray(),
            'success' => $this->success,
            'message' => $this->message,
        ];
    }
}
