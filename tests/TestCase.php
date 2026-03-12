<?php

namespace JustSolve\LaravelPec\Tests;

use JustSolve\LaravelPec\PecServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function legalmailBaseUrl(): string
    {
        return rtrim((string) config('pec.drivers.legalmail.base_url'), '/');
    }

    protected function openapiPecMassivaBaseUrl(): string
    {
        return rtrim((string) config('pec.drivers.openapi_pec_massiva.base_url'), '/');
    }

    protected function getPackageProviders($app): array
    {
        return [
            PecServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('pec.drivers', [
            'legalmail' => [
                'base_url' => env('LEGALMAIL_PEC_BASE_URL', 'https://sandbox.example.test'),
                'token' => env('LEGALMAIL_PEC_TOKEN', 'test-token'),
                'mailbox_id' => env('LEGALMAIL_PEC_MAILBOX_ID', 'mailbox-1'),
                'folder_id' => env('LEGALMAIL_PEC_FOLDER_ID', 'folder-1'),
                'message_uid_validity' => env('LEGALMAIL_PEC_MESSAGE_UID_VALIDITY', '999'),
            ],
            'openapi_pec_massiva' => [
                'base_url' => env('OPENAPI_PEC_MASSIVA_BASE_URL', 'https://test.ws.pecmassiva.com'),
                'token' => env('OPENAPI_PEC_MASSIVA_TOKEN', 'openapi-token'),
            ],
        ]);
    }
}
