<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class OpenapiAttachment
{
    public function __construct(
        public readonly string $name,
        public readonly string $file
    ) {
    }

    /**
     * @param array{name: string, file: string} $data
     */
    public static function fromArray(array $data): self
    {
        if (! isset($data['name']) || ! is_string($data['name']) || $data['name'] === '') {
            throw new InvalidArgumentException('OpenapiAttachment.name must be a non-empty string.');
        }

        if (! isset($data['file']) || ! is_string($data['file']) || $data['file'] === '') {
            throw new InvalidArgumentException('OpenapiAttachment.file must be a non-empty base64 string.');
        }

        return new self(
            $data['name'],
            $data['file']
        );
    }

    /**
     * @return array{name: string, file: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'file' => $this->file,
        ];
    }
}
