<?php

namespace JustSolve\LaravelPec\Tests\Feature;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use JustSolve\LaravelPec\Openapi\Models\OpenapiGetPecAddressResponse;
use JustSolve\LaravelPec\Openapi\Models\Pec;
use JustSolve\LaravelPec\Openapi\Models\PecHistoryItem;
use JustSolve\LaravelPec\Openapi\OpenapiCompanyClient;
use JustSolve\LaravelPec\Tests\TestCase;
use RuntimeException;

class OpenapiCompanyClientTest extends TestCase
{
    public function test_it_hydrates_typed_get_pec_address_response(): void
    {
        $response = OpenapiGetPecAddressResponse::fromArray([
            'data' => [
                [
                    'pec' => 'company@example.test',
                    'history' => [
                        [
                            'pec' => 'old-company@example.test',
                            'timestamp' => 1710000000,
                        ],
                    ],
                ],
            ],
            'success' => true,
            'message' => 'Ok',
        ]);

        $this->assertTrue($response->success);
        $this->assertSame('Ok', $response->message);
        $this->assertNull($response->error);
        $this->assertCount(1, $response->data);
        $this->assertInstanceOf(Pec::class, $response->data[0]);
        $this->assertSame('company@example.test', $response->data[0]->pec);
        $this->assertInstanceOf(PecHistoryItem::class, $response->data[0]->history[0]);
        $this->assertSame(1710000000, $response->data[0]->history[0]->timestamp);
    }

    public function test_it_hydrates_error_value_when_present(): void
    {
        $response = OpenapiGetPecAddressResponse::fromArray([
            'data' => [],
            'success' => false,
            'message' => 'Not found',
            'error' => 404,
        ]);

        $this->assertFalse($response->success);
        $this->assertSame(404, $response->error);
    }

    public function test_it_hydrates_null_data_when_no_pec_data_is_returned(): void
    {
        $response = OpenapiGetPecAddressResponse::fromArray([
            'data' => null,
            'success' => false,
            'message' => 'No data',
        ]);

        $this->assertNull($response->data);
        $this->assertSame([
            'data' => null,
            'success' => false,
            'message' => 'No data',
        ], $response->toArray());
    }

    public function test_it_rejects_invalid_get_pec_address_response_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('OpenapiGetPecAddressResponse.success must be a boolean.');

        OpenapiGetPecAddressResponse::fromArray([
            'data' => [],
            'success' => 'yes',
            'message' => 'Ok',
        ]);
    }

    public function test_openapi_company_client_returns_typed_get_pec_address_response(): void
    {
        $baseUrl = $this->openapiCompanyBaseUrl();

        Http::fake([
            '*' => Http::response([
                'data' => [
                    [
                        'pec' => 'company@example.test',
                        'history' => [
                            [
                                'pec' => 'old-company@example.test',
                                'timestamp' => 1710000000,
                            ],
                        ],
                    ],
                ],
                'success' => true,
                'message' => 'Ok',
            ], 200),
        ]);

        $client = $this->app->make(OpenapiCompanyClient::class);

        $response = $client->getPecAddress('12345678901');

        $this->assertInstanceOf(OpenapiGetPecAddressResponse::class, $response);
        $this->assertTrue($response->success);
        $this->assertSame('company@example.test', $response->data[0]->pec);

        Http::assertSent(function ($request) use ($baseUrl): bool {
            return $request->method() === 'GET'
                && $request->url() === "{$baseUrl}/IT-pec/12345678901"
                && $request->hasHeader('Authorization', 'Bearer company-token')
                && $request->hasHeader('Accept', 'application/json');
        });
    }

    public function test_openapi_company_client_throws_api_message_on_error(): void
    {
        Http::fake([
            '*' => Http::response([
                'success' => false,
                'message' => 'Invalid VAT number',
                'error' => 400,
            ], 400),
        ]);

        $client = $this->app->make(OpenapiCompanyClient::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid VAT number');

        $client->getPecAddress('invalid');
    }
}
