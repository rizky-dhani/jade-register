<?php

use App\Http\Controllers\PaymentProofController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to(filament()->getLoginUrl());
});

Route::livewire('/register', \App\Livewire\RegisterPortal::class)->name('register.portal');
Route::livewire('/register/visitor', \App\Livewire\VisitorRegistration::class)->name('register.visitor');
Route::livewire('/register/seminar', \App\Livewire\SeminarRegistration::class)->name('register.seminar');
Route::livewire('/poster/submit', \App\Livewire\PosterSubmission::class)->name('poster.submit');

Route::middleware(['auth'])->group(function () {
    Route::get('/payment-proofs/{registration}/download', [PaymentProofController::class, 'show'])
        ->name('payment-proofs.download');

    Route::get('/payment-proofs/{registration}/preview', [PaymentProofController::class, 'preview'])
        ->name('payment-proofs.preview');
});
Route::get('/mail-test', function () {
    Mail::raw('SMTP test successful', function ($message) {
        $message->to('rizkydhani15@gmail.com')
            ->subject('Laravel Mail Test');
    });
});
