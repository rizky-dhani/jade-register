<?php

use App\Http\Controllers\PaymentProofController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/register/visitor', \App\Livewire\VisitorRegistration::class)->name('register.visitor');
Route::livewire('/register/seminar', \App\Livewire\SeminarRegistration::class)->name('register.seminar');
Route::livewire('/poster/submit', \App\Livewire\PosterSubmission::class)->name('poster.submit');

Route::get('/payment-proofs/{registration}/download', [PaymentProofController::class, 'show'])
    ->name('payment-proofs.download');

Route::get('/payment-proofs/{registration}/preview', [PaymentProofController::class, 'preview'])
    ->name('payment-proofs.preview');
