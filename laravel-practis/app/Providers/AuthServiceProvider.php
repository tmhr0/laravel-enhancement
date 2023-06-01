<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\Company;
use App\Models\Section;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\SectionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Company::class => CompanyPolicy::class,
        Section::class => SectionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
       $this->registerPolicies();

        Gate::define('view-page', [UserPolicy::class, 'view']);
    }
}
