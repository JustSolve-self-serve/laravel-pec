<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class OpenapiDeleteMessageResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message
    ) {
    }

    /**
     * @param array{success: bool, message: string} $data
     */
    public static function fromArray(array $data): static
    {
        if (! isset($data['success']) || ! is_bool($data['success'])) {
            throw new InvalidArgumentException('OpenapiDeleteMessageResponse.success must be a boolean.');
        }

        if (! isset($data['message']) || ! is_string($data['message'])) {
            throw new InvalidArgumentException('OpenapiDeleteMessageResponse.message must be a string.');
        }

        return new static(
            success: $data['success'],
            message: $data['message']
        );
    }

    /**
     * @return array{success: bool, message: string}
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
        ];
    }
}
