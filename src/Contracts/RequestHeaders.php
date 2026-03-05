<?php

namespace JustSolve\LaravelPec\Contracts;

interface RequestHeaders
{
    /**
     * @return array<string, string>
     */
    public function toArray(): array;
}
