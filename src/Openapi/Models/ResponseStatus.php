<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class ResponseStatus
{
    public function __construct(
        public readonly string $sender,
        public readonly string $recipient,
        public readonly string $date,
        public readonly string $object,
        public readonly string $message
    ) {
    }

    /**
     * @param array{sender: string, recipient: string, date: string, object: string, message: string} $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['sender', 'recipient', 'date', 'object', 'message'] as $requiredStringField) {
            if (! isset($data[$requiredStringField]) || ! is_string($data[$requiredStringField]) || $data[$requiredStringField] === '') {
                throw new InvalidArgumentException("ResponseStatus.{$requiredStringField} must be a non-empty string.");
            }
        }

        return new self(
            sender: $data['sender'],
            recipient: $data['recipient'],
            date: $data['date'],
            object: $data['object'],
            message: $data['message']
        );
    }

    /**
     * @return array{sender: string, recipient: string, date: string, object: string, message: string}
     */
    public function toArray(): array
    {
        return [
            'sender' => $this->sender,
            'recipient' => $this->recipient,
            'date' => $this->date,
            'object' => $this->object,
            'message' => $this->message,
        ];
    }
}
