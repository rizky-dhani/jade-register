<?php

use App\Http\Controllers\PaymentProofController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to(filament()->getLoginUrl());
});

Route::livewire('/visitor/register', \App\Livewire\VisitorRegistration::class)->name('register.visitor');

Route::livewire('/seminar/register', \App\Livewire\SeminarRegistration::class)->name('register.seminar');
Route::livewire('/seminar/success/{id}', \App\Livewire\SeminarRegistrationSuccess::class)->name('register.seminar.success');

Route::livewire('/visitor/qr-code/{token}', \App\Livewire\VisitorQrCode::class)->name('visitor.qr-code');

Route::livewire('/attendance/qr-code/{token}', \App\Livewire\AttendanceQrCode::class)->name('attendance.qr-code');

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('register.seminar');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/poster/submit', \App\Livewire\PosterSubmission::class)->name('poster.submit');
    Route::livewire('/attendance/verify/{token}', \App\Livewire\AttendanceVerify::class)->name('attendance.verify');
    Route::livewire('/visitor/verify/{token}', \App\Livewire\VisitorVerify::class)->name('visitor.verify');

    Route::get('/payment-proofs/{registration}/download', [PaymentProofController::class, 'show'])
        ->name('payment-proofs.download');

    Route::get('/payment-proofs/{registration}/preview', [PaymentProofController::class, 'preview'])
        ->name('payment-proofs.preview');

    Route::get('/database-backups/{filename}/download', [\App\Http\Controllers\DatabaseBackupController::class, 'download'])
        ->name('database-backups.download');
});

Route::get('/mail-test', function () {
    Mail::raw('SMTP test successful', function ($message) {
        $message->to('rizkydhani15@gmail.com')
            ->subject('Laravel Mail Test');
    });
});
