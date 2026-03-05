<?php


use JustSolve\LaravelPec\Contracts\PecClient;
use JustSolve\LaravelPec\Contracts\PecClientManager;

if (! function_exists('pec_client')) {
    function pec_client(?string $driver = null): PecClient
    {
        if ($driver !== null) {
            /** @var PecClientManager $manager */
            $manager = app(PecClientManager::class);

            return $manager->driver($driver);
        }

        /** @var PecClient $client */
        $client = app(PecClient::class);

        return $client;
    }
}
