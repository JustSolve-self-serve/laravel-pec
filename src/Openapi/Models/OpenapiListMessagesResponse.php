<?php

namespace JustSolve\LaravelPec\Openapi\Models;

use InvalidArgumentException;

class OpenapiListMessagesResponse
{
    /**
     * @param array<int, InboxSearch> $data
     */
    public function __construct(
        public readonly array $data,
        public readonly bool $success,
        public readonly string $message,
        public readonly int $page,
        public readonly int $total,
        public readonly int $numberOfPages
    ) {
    }

    /**
     * @param array{
     *   data: array<int, array{sender: string, recipient: string, date: string, object: string, id: int}|InboxSearch>,
     *   success: bool,
     *   message: string,
     *   page: int,
     *   total: int,
     *   n_of_pages: int
     * } $data
     */
    public static function fromArray(array $data): static
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            throw new InvalidArgumentException('OpenapiListMessagesResponse.data must be an array.');
        }

        $items = [];
        foreach ($data['data'] as $item) {
            if ($item instanceof InboxSearch) {
                $items[] = $item;
                continue;
            }

            if (! is_array($item)) {
                throw new InvalidArgumentException('OpenapiListMessagesResponse.data items must be InboxSearch or array.');
            }

            $items[] = InboxSearch::fromArray($item);
        }

        if (! isset($data['success']) || ! is_bool($data['success'])) {
            throw new InvalidArgumentException('OpenapiListMessagesResponse.success must be a boolean.');
        }

        if (! isset($data['message']) || ! is_string($data['message'])) {
            throw new InvalidArgumentException('OpenapiListMessagesResponse.message must be a string.');
        }

        foreach (['page', 'total', 'n_of_pages'] as $requiredIntegerField) {
            if (! isset($data[$requiredIntegerField]) || ! is_int($data[$requiredIntegerField])) {
                throw new InvalidArgumentException("OpenapiListMessagesResponse.{$requiredIntegerField} must be an integer.");
            }
        }

        return new static(
            data: $items,
            success: $data['success'],
            message: $data['message'],
            page: $data['page'],
            total: $data['total'],
            numberOfPages: $data['n_of_pages']
        );
    }

    /**
     * @return array{
     *   data: array<int, array{sender: string, recipient: string, date: string, object: string, id: int}>,
     *   success: bool,
     *   message: string,
     *   page: int,
     *   total: int,
     *   n_of_pages: int
     * }
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(
                static fn (InboxSearch $item): array => $item->toArray(),
                $this->data
            ),
            'success' => $this->success,
            'message' => $this->message,
            'page' => $this->page,
            'total' => $this->total,
            'n_of_pages' => $this->numberOfPages,
        ];
    }
}
