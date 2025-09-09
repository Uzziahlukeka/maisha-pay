<?php

namespace Uzhlaravel\Maishapay;

use Illuminate\Support\ServiceProvider;
use Uzhlaravel\Maishapay\Commands\MaishapayCommand;

class MaishapayServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge package config with app config
        $this->mergeConfigFrom(
            __DIR__.'/../config/maishapay.php',
            'maishapay'
        );

        // Register the Maishapay service
        $this->app->singleton('maishapay', function ($app) {
            return new Maishapay(
                config('maishapay.public_key'),
                config('maishapay.secret_key'),
                config('maishapay.gateway_mode', 0),
                config('maishapay.base_url', 'https://marchand.maishapay.online/api/collect')
            );
        });

        // Register the Maishapay class
        $this->app->singleton(Maishapay::class, function ($app) {
            return new Maishapay(
                config('maishapay.public_key'),
                config('maishapay.secret_key'),
                config('maishapay.gateway_mode', 0),
                config('maishapay.base_url', 'https://marchand.maishapay.online/api/collect')
            );
        });
    }

    public function boot()
    {
        // Publish config file
        $this->publishes([
            __DIR__.'/../config/maishapay.php' => config_path('maishapay.php'),
        ], 'maishapay-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'maishapay-migrations');

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Register commands if any
        $this->registerCommands();

        // Register helper methods
        // $this->registerHelpers();
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MaishapayCommand::class,
            ]);
        }
    }
}
