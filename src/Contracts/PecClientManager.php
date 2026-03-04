<?php

namespace JustSolve\LegalmailPec\Contracts;

interface PecClientManager
{
    public function driver(string $driver): PecClient;

    public function default(): PecClient;
}
