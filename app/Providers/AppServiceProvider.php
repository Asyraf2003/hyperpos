<?php

// @audit-skip: line-limit

namespace App\Providers;

use App\Application\IdentityAccess\Request\IdentityAccessRequestStore;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(
            IdentityAccessRequestStore::class,
            fn (): IdentityAccessRequestStore => new IdentityAccessRequestStore()
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
