<?php

use JustSolve\LaravelPec\Legalmail\LegalmailClient;
use JustSolve\LaravelPec\Openapi\OpenapiCompanyClient;
use JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient;

if (! function_exists('legalmail_client')) {
    function legalmail_client(): LegalmailClient
    {
        /** @var LegalmailClient $client */
        $client = app(LegalmailClient::class);

        return $client;
    }
}

if (! function_exists('openapi_pec_massiva_client')) {
    function openapi_pec_massiva_client(): OpenapiPecMassivaClient
    {
        /** @var OpenapiPecMassivaClient $client */
        $client = app(OpenapiPecMassivaClient::class);

        return $client;
    }
}

if (! function_exists('openapi_company_client')) {
    function openapi_company_client(): OpenapiCompanyClient
    {
        /** @var OpenapiCompanyClient $client */
        $client = app(OpenapiCompanyClient::class);

        return $client;
    }
}
