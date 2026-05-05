<?php

namespace JustSolve\LaravelPec\Tests\Integration;

use JustSolve\LaravelPec\Openapi\Models\OpenapiGetPecAddressResponse;
use JustSolve\LaravelPec\Openapi\Models\Pec;
use JustSolve\LaravelPec\Openapi\OpenapiCompanyClient;
use JustSolve\LaravelPec\Tests\TestCase;
use RuntimeException;

class OpenapiCompanyIntegrationTest extends TestCase
{
    public function test_it_can_get_pec_address_for_a_valid_vat_number_against_openapi_company_sandbox(): void
    {
        $this->skipIfIntegrationTestsAreDisabled();

        $client = $this->app->make(OpenapiCompanyClient::class);
        $response = $client->getPecAddress($this->requiredEnv('OPENAPI_COMPANY_TEST_VALID_VAT_NUMBER'));

        $this->assertInstanceOf(OpenapiGetPecAddressResponse::class, $response);
        $this->assertTrue($response->success);
        $this->assertIsString($response->message);
        $this->assertIsArray($response->data);

        foreach ($response->data as $item) {
            $this->assertInstanceOf(Pec::class, $item);
        }
    }

    public function test_it_gets_an_exception_for_an_invalid_vat_number_against_openapi_company_sandbox(): void
    {
        $this->skipIfIntegrationTestsAreDisabled();

        $client = $this->app->make(OpenapiCompanyClient::class);

        $this->expectException(RuntimeException::class);

        $client->getPecAddress($this->requiredEnv('OPENAPI_COMPANY_TEST_INVALID_VAT_NUMBER'));
    }

    private function requiredEnv(string $name): string
    {
        $value = env($name);

        if (! is_string($value) || $value === '') {
            $this->markTestSkipped("{$name} not set.");
        }

        return $value;
    }

    private function skipIfIntegrationTestsAreDisabled(): void
    {
        if (! filter_var((string) env('OPENAPI_COMPANY_RUN_INTEGRATION_TESTS', false), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('Integration test disabled. Set OPENAPI_COMPANY_RUN_INTEGRATION_TESTS=true to enable.');
        }
    }
}
