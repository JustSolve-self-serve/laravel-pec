<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class OpenapiHeaders
{
    public function __construct(
        public readonly string $username,
        public readonly string $password
    ) {
    }

    /**
     * @param array{x-username: string, x-password: string} $data
     */
    public static function fromArray(array $data): self
    {
        if (! isset($data['x-username']) || ! is_string($data['x-username']) || $data['x-username'] === '') {
            throw new InvalidArgumentException('OpenapiHeaders.x-username must be a non-empty string.');
        }

        if (! isset($data['x-password']) || ! is_string($data['x-password']) || $data['x-password'] === '') {
            throw new InvalidArgumentException('OpenapiHeaders.x-password must be a non-empty string.');
        }

        return new self(
            username: $data['x-username'],
            password: $data['x-password']
        );
    }

    /**
     * @return array{x-username: string, x-password: string}
     */
    public function toArray(): array
    {
        return [
            'x-username' => $this->username,
            'x-password' => $this->password,
        ];
    }
}
