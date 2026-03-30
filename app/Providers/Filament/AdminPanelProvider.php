<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\AuthLogin;
use App\Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt;
use App\Filament\Pages\Auth\Register;
use App\Filament\Widgets\RegistrationActions;
use App\Filament\Widgets\SeminarParticipantCount;
use App\Filament\Widgets\VisitorCount;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('dashboard')
            ->login(AuthLogin::class)
            ->registration(Register::class)
            ->emailVerification(EmailVerificationPrompt::class)
            ->maxContentWidth(Width::Full)
            ->brandLogo(fn () => asset('assets/images/JADE_PDGI_Light.webp'))
            ->darkModeBrandLogo(fn () => asset('assets/images/JADE_PDGI_Dark.webp'))
            ->brandLogoHeight('8rem')
            ->colors([
                'primary' => '#4E397C',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                RegistrationActions::class,
                VisitorCount::class,
                SeminarParticipantCount::class,
            ])
            ->navigationItems([
                NavigationItem::make('Seminar Registration')
                    ->url(fn () => route('register.seminar'))
                    ->icon('heroicon-o-academic-cap')
                    ->group('Registrations'),
                NavigationItem::make('Visitor Registration')
                    ->url(fn () => route('register.visitor'))
                    ->icon('heroicon-o-users')
                    ->group('Registrations'),
                NavigationItem::make('Poster Registrations')
                    ->url(fn () => route('poster.submit'))
                    ->icon('heroicon-o-photo')
                    ->group('Registrations')
                    ->visible(fn () => auth()->user()?->hasRole('Participant') && auth()->user()?->seminarRegistrations()->where('payment_status', 'verified')->exists()),
            ])
            ->navigationGroups([
                'Registrations',
                'Competitions',
                'Events',
                'Data',
                'Settings',
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
