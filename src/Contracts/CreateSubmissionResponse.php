<?php

namespace JustSolve\LaravelPec\Contracts;

interface CreateSubmissionResponse
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
