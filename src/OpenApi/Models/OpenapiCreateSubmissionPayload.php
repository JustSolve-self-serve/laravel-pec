<?php

namespace JustSolve\LaravelPec\OpenApi\Models;

use InvalidArgumentException;

class OpenapiCreateSubmissionPayload
{
    /**
     * @param string|array<int, string> $recipient
     * @param array<int, OpenapiAttachment> $attachments
     */
    public function __construct(
        public readonly string $sender,
        public readonly string|array $recipient,
        public readonly string $subject,
        public readonly string $body,
        public readonly array $attachments,
        public readonly string $username,
        public readonly string $password
    ) {
    }

    /**
     * @param array{
     *   sender: string,
     *   recipient: string|array<int, string>,
     *   subject: string,
     *   body: string,
     *   attachments: array<int, array{name: string, file: string}|OpenapiAttachment>,
     *   username: string,
     *   password: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        foreach (['sender', 'subject', 'body', 'username', 'password'] as $requiredStringField) {
            if (! isset($data[$requiredStringField]) || ! is_string($data[$requiredStringField]) || $data[$requiredStringField] === '') {
                throw new InvalidArgumentException("OpenapiCreateSubmissionPayload.{$requiredStringField} must be a non-empty string.");
            }
        }

        if (! isset($data['recipient']) || (! is_string($data['recipient']) && ! is_array($data['recipient']))) {
            throw new InvalidArgumentException('OpenapiCreateSubmissionPayload.recipient must be a string or an array of strings.');
        }

        if (is_array($data['recipient'])) {
            if ($data['recipient'] === []) {
                throw new InvalidArgumentException('OpenapiCreateSubmissionPayload.recipient array cannot be empty.');
            }

            foreach ($data['recipient'] as $recipient) {
                if (! is_string($recipient) || $recipient === '') {
                    throw new InvalidArgumentException('OpenapiCreateSubmissionPayload.recipient array must contain only non-empty strings.');
                }
            }
        } elseif ($data['recipient'] === '') {
            throw new InvalidArgumentException('OpenapiCreateSubmissionPayload.recipient must be a non-empty string.');
        }

        if (! isset($data['attachments']) || ! is_array($data['attachments'])) {
            throw new InvalidArgumentException('OpenapiCreateSubmissionPayload.attachments must be an array.');
        }

        $attachments = [];
        foreach ($data['attachments'] as $attachment) {
            if ($attachment instanceof OpenapiAttachment) {
                $attachments[] = $attachment;
                continue;
            }

            if (! is_array($attachment)) {
                throw new InvalidArgumentException('OpenapiCreateSubmissionPayload.attachments items must be OpenapiAttachment or array.');
            }

            $attachments[] = OpenapiAttachment::fromArray($attachment);
        }

        return new self(
            sender: $data['sender'],
            recipient: $data['recipient'],
            subject: $data['subject'],
            body: $data['body'],
            attachments: $attachments,
            username: $data['username'],
            password: $data['password']
        );
    }

    /**
     * @return array{
     *   sender: string,
     *   recipient: string|array<int, string>,
     *   subject: string,
     *   body: string,
     *   attachments: array<int, array{name: string, file: string}>,
     *   username: string,
     *   password: string
     * }
     */
    public function toArray(): array
    {
        return [
            'sender' => $this->sender,
            'recipient' => $this->recipient,
            'subject' => $this->subject,
            'body' => $this->body,
            'attachments' => array_map(
                static fn (OpenapiAttachment $attachment): array => $attachment->toArray(),
                $this->attachments
            ),
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
}
