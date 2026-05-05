<?php

namespace JustSolve\LaravelPec\Facades;

use Illuminate\Support\Facades\Facade;
use JustSolve\LaravelPec\Openapi\Models\OpenapiGetPecAddressResponse;
use JustSolve\LaravelPec\Openapi\OpenapiCompanyClient;

/**
 * @method static OpenapiGetPecAddressResponse getPecAddress(string $code)
 */
class OpenapiCompany extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OpenapiCompanyClient::class;
    }
}
