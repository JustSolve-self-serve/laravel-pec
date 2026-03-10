<?php

namespace JustSolve\LaravelPec;

use Illuminate\Support\ServiceProvider;
use JustSolve\LaravelPec\Legalmail\LegalmailClient;
use JustSolve\LaravelPec\Openapi\OpenapiPecMassivaClient;

class PecServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/pec.php', 'pec');

        $this->app->singleton(LegalmailClient::class, fn (): LegalmailClient => $this->makeLegalmailClient());
        $this->app->singleton(
            OpenapiPecMassivaClient::class,
            fn (): OpenapiPecMassivaClient => $this->makeOpenapiPecMassivaClient()
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/pec.php' => config_path('pec.php'),
        ], 'pec-config');
    }

    private function makeLegalmailClient(): LegalmailClient
    {
        $config = $this->driverConfig('legalmail');

        return new LegalmailClient(
            baseUrl: (string) ($config['base_url'] ?? ''),
            token: isset($config['token']) ? (string) $config['token'] : null,
            mailboxId: isset($config['mailbox_id']) ? (string) $config['mailbox_id'] : null,
            folderId: isset($config['folder_id']) ? (string) $config['folder_id'] : null,
            messageUidValidity: isset($config['message_uid_validity']) ? (string) $config['message_uid_validity'] : null,
        );
    }

    private function makeOpenapiPecMassivaClient(): OpenapiPecMassivaClient
    {
        $config = $this->driverConfig('openapi_pec_massiva');

        return new OpenapiPecMassivaClient(
            baseUrl: (string) ($config['base_url'] ?? ''),
            token: (string) ($config['token'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function driverConfig(string $driver): array
    {
        $drivers = config('pec.drivers');

        if (is_array($drivers)) {
            return (array) ($drivers[$driver] ?? []);
        }

        return (array) config("pec.{$driver}", []);
    }
}
