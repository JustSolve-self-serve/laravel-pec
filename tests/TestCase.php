<?php

namespace JustSolve\LegalmailPec\Tests;

use JustSolve\LegalmailPec\LegalmailPecServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LegalmailPecServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('legalmail-pec.base_url', env('LEGALMAIL_PEC_BASE_URL', 'https://sandbox.example.test'));
        $app['config']->set('legalmail-pec.token', env('LEGALMAIL_PEC_TOKEN', 'test-token'));
        $app['config']->set('legalmail-pec.timeout', (int) env('LEGALMAIL_PEC_TIMEOUT', 20));
        $app['config']->set('legalmail-pec.mailbox_id', env('LEGALMAIL_PEC_MAILBOX_ID', 'mailbox-1'));
        $app['config']->set('legalmail-pec.folder_id', env('LEGALMAIL_PEC_FOLDER_ID', 'folder-1'));
        $app['config']->set('legalmail-pec.message_uid_validity', env('LEGALMAIL_PEC_MESSAGE_UID_VALIDITY', '999'));
        $app['config']->set('legalmail-pec.headers', []);
    }
}
