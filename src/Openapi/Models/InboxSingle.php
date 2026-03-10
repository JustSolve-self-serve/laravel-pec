<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class InboxSingle
{
    public function __construct(
        public readonly string $sender,
        public readonly string $recipient,
        public readonly string $date,
        public readonly string $object,
        public readonly string $body
    ) {
    }

    /**
     * @param array{sender: string, recipient: string, date: string, object: string, body: string} $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['sender', 'recipient', 'date', 'object', 'body'] as $requiredStringField) {
            if (! isset($data[$requiredStringField]) || ! is_string($data[$requiredStringField]) || $data[$requiredStringField] === '') {
                throw new InvalidArgumentException("InboxSingle.{$requiredStringField} must be a non-empty string.");
            }
        }

        return new self(
            sender: $data['sender'],
            recipient: $data['recipient'],
            date: $data['date'],
            object: $data['object'],
            body: $data['body']
        );
    }

    /**
     * @return array{sender: string, recipient: string, date: string, object: string, body: string}
     */
    public function toArray(): array
    {
        return [
            'sender' => $this->sender,
            'recipient' => $this->recipient,
            'date' => $this->date,
            'object' => $this->object,
            'body' => $this->body,
        ];
    }
}
