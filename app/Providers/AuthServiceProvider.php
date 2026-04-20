<?php

namespace App\Providers;

use App\Services\DocsAccessService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability){
            return $user->hasRole('SuperAdministrador') ? true : null;
        });

        Gate::define('viewLarecipe', function ($user, $documentation) {
            return app(DocsAccessService::class)->canViewDocumentation($user, $documentation);
        });

        Gate::define('viewDocReporteValidation', function ($user) {
            return (bool) ($user->validador ?? false);
        });
    }
}
