<?php

namespace Uzhlaravel\Maishapay;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Uzhlaravel\Maishapay\Services\MaishapayService;

class MaishapayServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('maishapay')
            ->hasConfigFile()
            ->hasMigrations([
                'create_maishapay_transactions_table',
            ])
            ->hasRoute('web');
    }

    public function packageBooted()
    {
        $this->app->bind('maishapay', function () {
            return new MaishapayService(
                config('maishapay.public_key'),
                config('maishapay.secret_key'),
                config('maishapay.gateway_mode', 0),
                config('maishapay.base_url', 'https://marchand.maishapay.online/api/collect')
            );
        });
    }
}
