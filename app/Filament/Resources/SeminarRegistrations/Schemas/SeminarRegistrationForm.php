<?php

namespace App\Filament\Resources\SeminarRegistrations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SeminarRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('filament.seminar_registration.form.name_str'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('name_license')
                    ->label(__('filament.seminar_registration.form.name_plataran'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('filament.seminar_registration.form.email_plataran'))
                    ->required()
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label(__('filament.seminar_registration.form.whatsapp_number'))
                    ->required()
                    ->maxLength(20),
                TextInput::make('nik')
                    ->label(__('filament.seminar_registration.form.nik'))
                    ->required()
                    ->maxLength(20),
                TextInput::make('pdgi_branch')
                    ->label(__('filament.seminar_registration.form.pdgi_branch'))
                    ->required()
                    ->maxLength(255),
                Select::make('kompetensi')
                    ->label(__('filament.seminar_registration.form.competency'))
                    ->required()
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
                    ->placeholder(__('seminar.select_competency')),
            ]);
    }
}
