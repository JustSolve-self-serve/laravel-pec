<?php

use JustSolve\LaravelPec\Services\LegalmailClient;
use JustSolve\LaravelPec\Services\OpenApiPecMassivaClient;

if (! function_exists('legalmail_client')) {
    function legalmail_client(): LegalmailClient
    {
        /** @var LegalmailClient $client */
        $client = app(LegalmailClient::class);

        return $client;
    }
}

if (! function_exists('openapi_pec_massiva_client')) {
    function openapi_pec_massiva_client(): OpenApiPecMassivaClient
    {
        /** @var OpenApiPecMassivaClient $client */
        $client = app(OpenApiPecMassivaClient::class);

        return $client;
    }
}
