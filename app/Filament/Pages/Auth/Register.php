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
use Filament\Schemas\Schema;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class Register extends BaseRegister
{
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
                $this->getPhoneFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function getCountryFormComponent(): Component
    {
        return Select::make('country_id')
            ->label('Country')
            ->options(Country::orderBy('name')->pluck('name', 'id'))
            ->searchable()
            ->preload()
            ->nullable();
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('WhatsApp Number')
            ->tel()
            ->maxLength(255)
            ->nullable();
    }

    protected function getNameLicenseFormComponent(): Component
    {
        return TextInput::make('name_license')
            ->label('Name Plataran')
            ->maxLength(255)
            ->nullable();
    }

    protected function getNikFormComponent(): Component
    {
        return TextInput::make('nik')
            ->label('NIK')
            ->maxLength(255)
            ->nullable();
    }

    protected function getPdgiBranchFormComponent(): Component
    {
        return TextInput::make('pdgi_branch')
            ->label('PDGI Branch')
            ->maxLength(255)
            ->nullable();
    }

    protected function getKompetensiFormComponent(): Component
    {
        return Select::make('kompetensi')
            ->label('Competency')
            ->options([
                'Dokter Gigi Umum' => 'Dokter Gigi Umum',
                'Sp.KG' => 'Sp.KG',
                'Sp.KGA' => 'Sp.KGA',
                'Sp.Pros' => 'Sp.Pros',
                'Sp.B.M.M' => 'Sp.B.M.M',
                'Sp.Perio' => 'Sp.Perio',
                'Sp.Ort' => 'Sp.Ort',
                'Sp.RKG' => 'Sp.RKG',
                'Sp.PM' => 'Sp.PM',
                'Sp.OF' => 'Sp.OF',
                'Sp.PMM' => 'Sp.PMM',
                'Mahasiswa Kedokteran Gigi' => 'Mahasiswa Kedokteran Gigi',
                'drg Internship' => 'drg Internship',
            ])
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
            ->title('Registration successful')
            ->body('Please check your email to verify your account before logging in.')
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
