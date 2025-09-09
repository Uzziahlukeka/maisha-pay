<?php

namespace Uzhlaravel\Maishapay\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Uzhlaravel\Maishapay\MaishapayServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');


        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        // Run migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Uzhlaravel\\Maishapay\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            MaishapayServiceProvider::class,
        ];
    }
}
