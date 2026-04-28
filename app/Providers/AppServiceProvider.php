<?php

namespace App\Providers;

use App\Services\ConfiguracionSistemaService;
use App\Services\Layout\UserUbicacionService;
use App\Support\Larecipe\DocumentationRepository as CustomDocumentationRepository;
use BinaryTorch\LaRecipe\DocumentationRepository as BaseDocumentationRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BaseDocumentationRepository::class, CustomDocumentationRepository::class);
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

        View::composer([
            'plantilla.header',
        ], function ($view) {
            $view->with(
                'authUserUbicacion',
                app(UserUbicacionService::class)->getAuthUserUbicacion()
            );
        });
    }
}
