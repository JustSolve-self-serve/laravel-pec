<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class InboxSearch
{
    public function __construct(
        public readonly string $sender,
        public readonly string $recipient,
        public readonly string $date,
        public readonly string $object,
        public readonly int $id
    ) {
    }

    /**
     * @param array{sender: string, recipient: string, date: string, object: string, id: int} $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['sender', 'recipient', 'date', 'object'] as $requiredStringField) {
            if (! isset($data[$requiredStringField]) || ! is_string($data[$requiredStringField]) || $data[$requiredStringField] === '') {
                throw new InvalidArgumentException("InboxSearch.{$requiredStringField} must be a non-empty string.");
            }
        }

        if (! isset($data['id']) || ! is_int($data['id'])) {
            throw new InvalidArgumentException('InboxSearch.id must be an integer.');
        }

        return new self(
            sender: $data['sender'],
            recipient: $data['recipient'],
            date: $data['date'],
            object: $data['object'],
            id: $data['id']
        );
    }

    /**
     * @return array{sender: string, recipient: string, date: string, object: string, id: int}
     */
    public function toArray(): array
    {
        return [
            'sender' => $this->sender,
            'recipient' => $this->recipient,
            'date' => $this->date,
            'object' => $this->object,
            'id' => $this->id,
        ];
    }
}
