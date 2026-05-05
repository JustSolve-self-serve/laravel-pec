<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class Pec
{
    /**
     * @param array<int, PecHistoryItem> $history
     */
    public function __construct(
        public readonly ?string $pec,
        public readonly array $history
    ) {
    }

    /**
     * @param array{pec?: string|null, history: array<int, array{pec: string, timestamp: int}|PecHistoryItem>} $data
     */
    public static function fromArray(array $data): static
    {
        if (array_key_exists('pec', $data) && ! is_null($data['pec']) && ! is_string($data['pec'])) {
            throw new InvalidArgumentException('Pec.pec must be a string or null.');
        }

        if (! isset($data['history']) || ! is_array($data['history'])) {
            throw new InvalidArgumentException('Pec.history must be an array.');
        }

        $history = [];
        foreach ($data['history'] as $item) {
            if ($item instanceof PecHistoryItem) {
                $history[] = $item;
                continue;
            }

            if (! is_array($item)) {
                throw new InvalidArgumentException('Pec.history items must be PecHistoryItem or array.');
            }

            $history[] = PecHistoryItem::fromArray($item);
        }

        return new static(
            pec: $data['pec'] ?? null,
            history: $history
        );
    }

    /**
     * @return array{pec: string|null, history: array<int, array{pec: string, timestamp: int}>}
     */
    public function toArray(): array
    {
        return [
            'pec' => $this->pec,
            'history' => array_map(
                static fn (PecHistoryItem $item): array => $item->toArray(),
                $this->history
            ),
        ];
    }
}
