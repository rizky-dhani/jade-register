<?php

namespace App\Filament\Pages\Auth;

use App\Models\Country;
use App\Notifications\VerifyEmail;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Features\SupportRedirects\Redirector;

class Register extends BaseRegister
{
    public ?string $locale = null;

    public function mount(): void
    {
        parent::mount();

        // Set default locale from session or app locale
        $this->locale = session('locale', config('app.locale', 'en'));
        app()->setLocale($this->locale);
    }

    public function updated($property): void
    {
        if ($property === 'data.country_id') {
            $this->updateLocaleBasedOnCountry();
        }
    }

    protected function updateLocaleBasedOnCountry(): void
    {
        $countryId = $this->data['country_id'] ?? null;

        if ($countryId) {
            $country = Country::find($countryId);
            $this->locale = $country?->is_indonesia ? 'id' : 'en';
        } else {
            $this->locale = config('app.locale', 'en');
        }

        app()->setLocale($this->locale);
        session(['locale' => $this->locale]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getCountryFormComponent(),
                $this->getNameFormComponent(),
                $this->getNameInternationalFormComponent(),
                $this->getNameLicenseFormComponent(),
                $this->getNikFormComponent(),
                $this->getPdgiBranchFormComponent(),
                $this->getKompetensiFormComponent(),
                $this->getStatusFormComponent(),
                $this->getPhoneFormComponent(),
                $this->getPhoneInternationalFormComponent(),
                $this->getEmailFormComponent(),
                $this->getEmailInternationalFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function isIndonesia(Get $get): bool
    {
        $countryId = $get('country_id');
        if (! $countryId) {
            return true;
        }

        $country = Country::find($countryId);

        return $country?->is_indonesia ?? true;
    }

    protected function getCountryFormComponent(): Component
    {
        return Select::make('country_id')
            ->label(__('seminar.country'))
            ->validationAttribute(__('seminar.country'))
            ->options(Country::orderBy('name')->pluck('name', 'id'))
            ->searchable()
            ->preload()
            ->live()
            ->required();
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('seminar.name_str'))
            ->validationAttribute(__('seminar.name_str'))
            ->required()
            ->maxLength(255)
            ->autofocus()
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get));
    }

    protected function getNameInternationalFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('seminar.name'))
            ->validationAttribute(__('seminar.name'))
            ->required()
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('country_id') && ! $this->isIndonesia($get));
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('seminar.email_plataran'))
            ->validationAttribute(__('seminar.email_plataran'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel())
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get));
    }

    protected function getEmailInternationalFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('seminar.email'))
            ->validationAttribute(__('seminar.email'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel())
            ->visible(fn (Get $get): bool => $get('country_id') && ! $this->isIndonesia($get));
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('seminar.password'))
            ->validationAttribute(__('seminar.password'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(Password::default())
            ->autocomplete('new-password');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('seminar.confirm_password'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->same('password')
            ->validationAttribute(__('seminar.confirm_password'));
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label(__('seminar.whatsapp_number'))
            ->validationAttribute(__('seminar.whatsapp_number'))
            ->tel()
            ->required()
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get));
    }

    protected function getPhoneInternationalFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label(__('seminar.whatsapp_number'))
            ->validationAttribute(__('seminar.whatsapp_number'))
            ->tel()
            ->required()
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('country_id') && ! $this->isIndonesia($get));
    }

    protected function getNameLicenseFormComponent(): Component
    {
        return TextInput::make('name_license')
            ->label(__('seminar.name_plataran'))
            ->validationAttribute(__('seminar.name_plataran'))
            ->required()
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get));
    }

    protected function getNikFormComponent(): Component
    {
        return TextInput::make('nik')
            ->label(__('seminar.nik'))
            ->validationAttribute(__('seminar.nik'))
            ->required()
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get));
    }

    protected function getPdgiBranchFormComponent(): Component
    {
        return TextInput::make('pdgi_branch')
            ->label(__('seminar.pdgi_branch'))
            ->validationAttribute(__('seminar.pdgi_branch'))
            ->required()
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get));
    }

    protected function getKompetensiFormComponent(): Component
    {
        return Select::make('kompetensi')
            ->label(__('seminar.competency'))
            ->validationAttribute(__('seminar.competency'))
            ->options([
                'Dokter Gigi Umum' => __('seminar.competency_gp'),
                'Sp.KG' => __('seminar.competency_sp_kg'),
                'Sp.KGA' => __('seminar.competency_sp_kga'),
                'Sp.Pros' => __('seminar.competency_sp_pros'),
                'Sp.B.M.M' => __('seminar.competency_sp_bmm'),
                'Sp.Perio' => __('seminar.competency_sp_perio'),
                'Sp.Ort' => __('seminar.competency_sp_ort'),
                'Sp.RKG' => __('seminar.competency_sp_rkg'),
                'Sp.PM' => __('seminar.competency_sp_pm'),
                'Sp.OF' => __('seminar.competency_sp_of'),
                'Sp.PMM' => __('seminar.competency_sp_pmm'),
                'Mahasiswa Kedokteran Gigi' => __('seminar.competency_dental_student'),
                'drg Internship' => __('seminar.competency_dentist_internship'),
            ])
            ->required()
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get))
            ->preload();
    }

    protected function getStatusFormComponent(): Component
    {
        return Select::make('status')
            ->label(__('seminar.status'))
            ->validationAttribute(__('seminar.status'))
            ->options([
                'Dentist' => __('seminar.dentist'),
                'Student' => __('seminar.student'),
            ])
            ->required()
            ->visible(fn (Get $get): bool => $get('country_id') && ! $this->isIndonesia($get))
            ->preload();
    }

    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create($data);

        $user->assignRole('Participant');

        return $user;
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        if ($this->isRegisterRateLimited($this->data['email'] ?? '')) {
            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            return $user;
        });

        $this->sendEmailVerificationNotification($user);

        Notification::make()
            ->title(__('auth.registration_successful'))
            ->body(__('auth.check_email_verify'))
            ->success()
            ->send();

        return new class implements RegistrationResponse
        {
            public function toResponse($request): RedirectResponse|Redirector
            {
                return redirect()->to(Filament::getLoginUrl());
            }
        };
    }

    protected function sendEmailVerificationNotification(Model $user): void
    {
        if (! $user instanceof MustVerifyEmail) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->notify(new VerifyEmail(
            Filament::getVerifyEmailUrl($user)
        ));
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::auth/pages/register.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::auth/pages/register.notifications.throttled') ?: []) ? __('filament-panels::auth/pages/register.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    protected function isRegisterRateLimited(string $email): bool
    {
        if (blank($email)) {
            return false;
        }

        $rateLimitingKey = 'filament-register:'.sha1($email);

        if (RateLimiter::tooManyAttempts($rateLimitingKey, maxAttempts: 2)) {
            $this->getRateLimitedNotification(new TooManyRequestsException(
                static::class,
                'register',
                request()->ip(),
                RateLimiter::availableIn($rateLimitingKey),
            ))?->send();

            return true;
        }

        RateLimiter::hit($rateLimitingKey);

        return false;
    }
}
