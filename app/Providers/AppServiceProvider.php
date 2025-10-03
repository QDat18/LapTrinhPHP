<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Organization;
use Illuminate\Pagination\Paginator;

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
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');
        View::composer('admin.*', function ($view) {
            if (auth()->check() && auth()->user()->user_type === 'Admin') {
                $pendingVerifications = Organization::where('verification_status', 'Pending')->count();
                $view->with('pendingVerifications', $pendingVerifications);
            }
        });
    }
}
