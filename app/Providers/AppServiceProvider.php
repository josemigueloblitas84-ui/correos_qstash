<?php

namespace App\Providers;

use App\Services\ConfiguracionSistemaService;
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
            'layouts.adminLTE_guest',
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
