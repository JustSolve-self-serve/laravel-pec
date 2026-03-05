<?php

namespace JustSolve\LaravelPec\Contracts;

interface CreateSubmissionPayload
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
