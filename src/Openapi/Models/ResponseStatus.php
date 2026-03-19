<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class ResponseStatus
{
    public function __construct(
        public readonly string $sender,
        public readonly string $recipient,
        public readonly string $date,
        public readonly string $subject,
        public readonly string $body
    ) {
    }

    /**
     * @param array{sender: string, recipient: string, date: string, subject: string, body: string} $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['sender', 'recipient', 'date', 'subject', 'body'] as $requiredStringField) {
            if (! isset($data[$requiredStringField]) || ! is_string($data[$requiredStringField]) || $data[$requiredStringField] === '') {
                throw new InvalidArgumentException("ResponseStatus.{$requiredStringField} must be a non-empty string.");
            }
        }

        return new self(
            sender: $data['sender'],
            recipient: $data['recipient'],
            date: $data['date'],
            subject: $data['subject'],
            body: $data['body']
        );
    }

    /**
     * @return array{sender: string, recipient: string, date: string, subject: string, body: string}
     */
    public function toArray(): array
    {
        return [
            'sender' => $this->sender,
            'recipient' => $this->recipient,
            'date' => $this->date,
            'subject' => $this->subject,
            'body' => $this->body,
        ];
    }
}
