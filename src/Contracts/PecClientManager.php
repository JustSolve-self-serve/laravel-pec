<?php

namespace JustSolve\LaravelPec\Contracts;

interface PecClientManager
{
    public function driver(string $driver): PecClient;

    public function default(): PecClient;
}
