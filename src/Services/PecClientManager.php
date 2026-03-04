<?php

namespace JustSolve\LegalmailPec\Services;

use JustSolve\LegalmailPec\Contracts\PecClient;
use JustSolve\LegalmailPec\Contracts\PecClientManager as PecClientManagerContract;
use RuntimeException;

class PecClientManager implements PecClientManagerContract
{
    /**
     * @var array<string, PecClient>
     */
    private array $clients = [];

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private readonly array $config)
    {
    }

    public function driver(string $driver): PecClient
    {
        if (isset($this->clients[$driver])) {
            return $this->clients[$driver];
        }

        $clientConfig = $this->clientConfigForDriver($driver);
        $clientClass = match ($driver) {
            'legalmail' => LegalmailProviderClient::class,
            'openapi_pec_massiva' => OpenApiPecMassivaProviderClient::class,
            default => throw new RuntimeException("Unsupported pec driver [{$driver}]."),
        };

        $client = new $clientClass(
            baseUrl: (string) ($clientConfig['base_url'] ?? ''),
            token: isset($clientConfig['token']) ? (string) $clientConfig['token'] : null,
            timeout: (int) ($clientConfig['timeout'] ?? 20),
            mailboxId: isset($this->config['mailbox_id']) ? (string) $this->config['mailbox_id'] : null,
            folderId: isset($this->config['folder_id']) ? (string) $this->config['folder_id'] : null,
            messageUidValidity: isset($this->config['message_uid_validity']) ? (string) $this->config['message_uid_validity'] : null,
            headers: (array) ($clientConfig['headers'] ?? []),
        );

        $this->clients[$driver] = $client;

        return $client;
    }

    public function default(): PecClient
    {
        $driver = (string) ($this->config['default'] ?? $this->config['driver'] ?? 'legalmail');

        return $this->driver($driver);
    }

    /**
     * Forward unknown calls to the default PEC client.
     *
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->default()->{$method}(...$arguments);
    }

    /**
     * @return array<string, mixed>
     */
    private function clientConfigForDriver(string $driver): array
    {
        if (isset($this->config['drivers']) && is_array($this->config['drivers'])) {
            return (array) ($this->config['drivers'][$driver] ?? []);
        }

        // Backward compatibility with pre-drivers config structure.
        return (array) ($this->config[$driver] ?? []);
    }
}
