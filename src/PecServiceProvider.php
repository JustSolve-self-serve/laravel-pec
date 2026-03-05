<?php

namespace JustSolve\LaravelPec;

use Illuminate\Support\ServiceProvider;
use JustSolve\LaravelPec\Legalmail\LegalmailClient;
use JustSolve\LaravelPec\OpenApi\OpenApiPecMassivaClient;

class PecServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/pec.php', 'pec');

        $this->app->singleton(LegalmailClient::class, fn (): LegalmailClient => $this->makeLegalmailClient());
        $this->app->singleton(
            OpenApiPecMassivaClient::class,
            fn (): OpenApiPecMassivaClient => $this->makeOpenApiPecMassivaClient()
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
            mailboxId: config('pec.mailbox_id') !== null ? (string) config('pec.mailbox_id') : null,
            folderId: config('pec.folder_id') !== null ? (string) config('pec.folder_id') : null,
            messageUidValidity: config('pec.message_uid_validity') !== null ? (string) config('pec.message_uid_validity') : null,
            headers: (array) ($config['headers'] ?? []),
        );
    }

    private function makeOpenApiPecMassivaClient(): OpenApiPecMassivaClient
    {
        $config = $this->driverConfig('openapi_pec_massiva');

        return new OpenApiPecMassivaClient(
            baseUrl: (string) ($config['base_url'] ?? ''),
            token: isset($config['token']) ? (string) $config['token'] : null,
            headers: (array) ($config['headers'] ?? []),
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
