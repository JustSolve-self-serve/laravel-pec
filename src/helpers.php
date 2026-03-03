<?php


use JustSolve\LegalmailPec\Contracts\LegalmailPecClient;

if (! function_exists('legalmail_pec')) {
    function legalmail_pec(): LegalmailPecClient
    {
        /** @var LegalmailPecClient $client */
        $client = app(LegalmailPecClient::class);

        return $client;
    }
}
