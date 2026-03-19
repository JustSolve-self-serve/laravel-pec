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
        public readonly ?string $message
    ) {
    }

    /**
     * @param array{sender: string, recipient: string, date: string, subject: string, message: ?string} $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['sender', 'recipient', 'date', 'subject'] as $requiredStringField) {
            if (! isset($data[$requiredStringField]) || ! is_string($data[$requiredStringField]) || $data[$requiredStringField] === '') {
                throw new InvalidArgumentException("ResponseStatus.{$requiredStringField} must be a non-empty string.");
            }
        }

        if (! array_key_exists('message', $data) || (! is_string($data['message']) && ! is_null($data['message']))) {
            throw new InvalidArgumentException('ResponseStatus.message must be a string or null.');
        }

        return new self(
            sender: $data['sender'],
            recipient: $data['recipient'],
            date: $data['date'],
            subject: $data['subject'],
            message: $data['message']
        );
    }

    /**
     * @return array{sender: string, recipient: string, date: string, subject: string, message: ?string}
     */
    public function toArray(): array
    {
        return [
            'sender' => $this->sender,
            'recipient' => $this->recipient,
            'date' => $this->date,
            'subject' => $this->subject,
            'message' => $this->message,
        ];
    }
}
