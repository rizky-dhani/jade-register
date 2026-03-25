<?php

namespace App\Filament\Pages\Auth;

use App\Models\Country;
use App\Notifications\VerifyEmail;
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
                $this->getNameFormComponent(),
                $this->getCountryFormComponent(),
                $this->getNameLicenseFormComponent(),
                $this->getNikFormComponent(),
                $this->getPdgiBranchFormComponent(),
                $this->getKompetensiFormComponent(),
                $this->getStatusFormComponent(),
                $this->getPhoneFormComponent(),
                $this->getEmailFormComponent(),
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
            ->label(__('auth.country'))
            ->options(Country::orderBy('name')->pluck('name', 'id'))
            ->searchable()
            ->preload()
            ->live()
            ->nullable();
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('auth.name'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('auth.email'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('auth.password'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule(Password::default())
            ->autocomplete('new-password');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('auth.confirm_password'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->same('password')
            ->validationAttribute(__('auth.confirm_password'));
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label(__('auth.whatsapp_number'))
            ->tel()
            ->maxLength(255)
            ->nullable();
    }

    protected function getNameLicenseFormComponent(): Component
    {
        return TextInput::make('name_license')
            ->label(__('auth.name_plataran'))
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get))
            ->nullable();
    }

    protected function getNikFormComponent(): Component
    {
        return TextInput::make('nik')
            ->label(__('auth.nik'))
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get))
            ->nullable();
    }

    protected function getPdgiBranchFormComponent(): Component
    {
        return TextInput::make('pdgi_branch')
            ->label(__('auth.pdgi_branch'))
            ->maxLength(255)
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get))
            ->nullable();
    }

    protected function getKompetensiFormComponent(): Component
    {
        return Select::make('kompetensi')
            ->label(__('auth.competency'))
            ->options([
                'Dokter Gigi Umum' => __('auth.competency_gp'),
                'Sp.KG' => __('auth.competency_sp_kg'),
                'Sp.KGA' => __('auth.competency_sp_kga'),
                'Sp.Pros' => __('auth.competency_sp_pros'),
                'Sp.B.M.M' => __('auth.competency_sp_bmm'),
                'Sp.Perio' => __('auth.competency_sp_perio'),
                'Sp.Ort' => __('auth.competency_sp_ort'),
                'Sp.RKG' => __('auth.competency_sp_rkg'),
                'Sp.PM' => __('auth.competency_sp_pm'),
                'Sp.OF' => __('auth.competency_sp_of'),
                'Sp.PMM' => __('auth.competency_sp_pmm'),
                'Mahasiswa Kedokteran Gigi' => __('auth.competency_dental_student'),
                'drg Internship' => __('auth.competency_dentist_internship'),
            ])
            ->visible(fn (Get $get): bool => $get('country_id') && $this->isIndonesia($get))
            ->nullable()
            ->preload();
    }

    protected function getStatusFormComponent(): Component
    {
        return Select::make('status')
            ->label(__('auth.status'))
            ->options([
                'Dentist' => __('auth.dentist'),
                'Student' => __('auth.student'),
            ])
            ->visible(fn (Get $get): bool => $get('country_id') && ! $this->isIndonesia($get))
            ->nullable()
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
}
