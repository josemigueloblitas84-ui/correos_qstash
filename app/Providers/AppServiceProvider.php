<?php

namespace App\Providers;

use App\Services\ConfiguracionSistemaService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer([
            'plantilla.menu',
            'layouts.guest',
            'components.application-logo',
        ], function ($view) {
            $view->with(
                'systemConfig',
                app(ConfiguracionSistemaService::class)->getPresentationData()
            );
        });
    }
}
