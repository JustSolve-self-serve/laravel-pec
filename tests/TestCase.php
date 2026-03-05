<?php

namespace JustSolve\LaravelPec\Tests;

use JustSolve\LaravelPec\PecServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PecServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('pec.default', env('LEGALMAIL_PEC_DRIVER', 'legalmail'));
        $app['config']->set('pec.mailbox_id', env('LEGALMAIL_PEC_MAILBOX_ID', 'mailbox-1'));
        $app['config']->set('pec.folder_id', env('LEGALMAIL_PEC_FOLDER_ID', 'folder-1'));
        $app['config']->set('pec.message_uid_validity', env('LEGALMAIL_PEC_MESSAGE_UID_VALIDITY', '999'));
        $app['config']->set('pec.drivers', [
            'legalmail' => [
                'base_url' => env('LEGALMAIL_PEC_BASE_URL', 'https://sandbox.example.test'),
                'token' => env('LEGALMAIL_PEC_TOKEN', 'test-token'),
                'timeout' => (int) env('LEGALMAIL_PEC_TIMEOUT', 20),
                'headers' => [],
            ],
            'openapi_pec_massiva' => [
                'base_url' => env('OPENAPI_PEC_MASSIVA_BASE_URL', 'https://openapi.example.test'),
                'token' => env('OPENAPI_PEC_MASSIVA_TOKEN', 'openapi-token'),
                'timeout' => (int) env('OPENAPI_PEC_MASSIVA_TIMEOUT', 20),
                'headers' => [],
            ],
        ]);
    }
}
