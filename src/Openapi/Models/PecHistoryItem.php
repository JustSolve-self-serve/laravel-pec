<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class PecHistoryItem
{
    public function __construct(
        public readonly string $pec,
        public readonly int $timestamp
    ) {
    }

    /**
     * @param array{pec: string, timestamp: int} $data
     */
    public static function fromArray(array $data): static
    {
        if (! isset($data['pec']) || ! is_string($data['pec']) || $data['pec'] === '') {
            throw new InvalidArgumentException('PecHistoryItem.pec must be a non-empty string.');
        }

        if (! isset($data['timestamp']) || ! is_int($data['timestamp'])) {
            throw new InvalidArgumentException('PecHistoryItem.timestamp must be an integer.');
        }

        return new static(
            pec: $data['pec'],
            timestamp: $data['timestamp']
        );
    }

    /**
     * @return array{pec: string, timestamp: int}
     */
    public function toArray(): array
    {
        return [
            'pec' => $this->pec,
            'timestamp' => $this->timestamp,
        ];
    }
}
