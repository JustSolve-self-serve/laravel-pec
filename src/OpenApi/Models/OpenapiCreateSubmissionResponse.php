<?php

namespace JustSolve\LaravelPec\OpenApi\Models;

use InvalidArgumentException;

class OpenapiCreateSubmissionResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly string $messageId,
        public readonly int $sent
    ) {
    }

    /**
     * @param array{success: bool, message: string, message_id: string, sent: int} $data
     */
    public static function fromArray(array $data): static
    {
        if (! isset($data['success']) || ! is_bool($data['success'])) {
            throw new InvalidArgumentException('OpenapiCreateSubmissionResponse.success must be a boolean.');
        }

        if (! isset($data['message']) || ! is_string($data['message'])) {
            throw new InvalidArgumentException('OpenapiCreateSubmissionResponse.message must be a string.');
        }

        if (! isset($data['message_id']) || ! is_string($data['message_id']) || $data['message_id'] === '') {
            throw new InvalidArgumentException('OpenapiCreateSubmissionResponse.message_id must be a non-empty string.');
        }

        if (! isset($data['sent']) || ! is_int($data['sent'])) {
            throw new InvalidArgumentException('OpenapiCreateSubmissionResponse.sent must be an integer.');
        }

        return new static(
            success: $data['success'],
            message: $data['message'],
            messageId: $data['message_id'],
            sent: $data['sent']
        );
    }

    /**
     * @return array{success: bool, message: string, message_id: string, sent: int}
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'message_id' => $this->messageId,
            'sent' => $this->sent,
        ];
    }
}
