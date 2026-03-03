<?php

namespace JustSolve\LegalmailPec;

use Illuminate\Support\ServiceProvider;
use JustSolve\LegalmailPec\Contracts\LegalmailPecClient;
use JustSolve\LegalmailPec\Services\HttpLegalmailPecClient;

class LegalmailPecServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/legalmail-pec.php', 'legalmail-pec');

        $this->app->singleton(LegalmailPecClient::class, function () {
            $config = (array) config('legalmail-pec', []);

            return new HttpLegalmailPecClient(
                baseUrl: (string) ($config['base_url'] ?? ''),
                token: isset($config['token']) ? (string) $config['token'] : null,
                timeout: (int) ($config['timeout'] ?? 20),
                mailboxId: isset($config['mailbox_id']) ? (string) $config['mailbox_id'] : null,
                folderId: isset($config['folder_id']) ? (string) $config['folder_id'] : null,
                messageUidValidity: isset($config['message_uid_validity']) ? (string) $config['message_uid_validity'] : null,
                headers: (array) ($config['headers'] ?? []),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/legalmail-pec.php' => config_path('legalmail-pec.php'),
        ], 'legalmail-pec-config');
    }
}
