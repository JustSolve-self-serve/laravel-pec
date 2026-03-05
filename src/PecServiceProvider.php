<?php

namespace JustSolve\LaravelPec;

use Illuminate\Support\ServiceProvider;
use JustSolve\LaravelPec\Contracts\PecClient;
use JustSolve\LaravelPec\Contracts\PecClientManager as PecClientManagerContract;
use JustSolve\LaravelPec\Services\PecClientManager;

class PecServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/pec.php', 'pec');

        $this->app->singleton(PecClientManagerContract::class, function () {
            return new PecClientManager((array) config('pec', []));
        });

        $this->app->singleton(PecClient::class, function () {
            /** @var PecClientManagerContract $manager */
            $manager = $this->app->make(PecClientManagerContract::class);

            return $manager->default();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/pec.php' => config_path('pec.php'),
        ], 'pec-config');
    }
}
