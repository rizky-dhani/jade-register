<?php

namespace App\Providers;

use App\Models\SeminarRegistration;
use App\Observers\SeminarRegistrationObserver;
use Filament\Actions\Action;
use Filament\Support\Facades\FilamentTimezone;
use Filament\Tables\Table;
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

        // Close the filter dropdown once filters are applied, so it does not stay
        // open over the table. `close()` is the dropdown's Alpine method.
        Table::configureUsing(function (Table $table): void {
            $table->filtersApplyAction(
                fn (Action $action): Action => $action->alpineClickHandler('typeof close === "function" && close()'),
            );
        });
    }
}
