<?php

namespace App\Providers;

use App\Models\SeminarRegistration;
use App\Observers\SeminarRegistrationObserver;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        SeminarRegistration::observe(SeminarRegistrationObserver::class);

        Gate::before(fn ($user) => $user->hasRole('Super Admin') ? true : null);
        Gate::define('view-payment-proof', fn ($user) => $user->hasPermissionTo('view_payment_proofs'));
        FilamentTimezone::set('Asia/Jakarta');
    }
}
