<?php

use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\PaymentProofController;
use App\Livewire\AttendanceQrCode;
use App\Livewire\AttendanceVerify;
use App\Livewire\HandsOnRegistration;
use App\Livewire\PosterSubmission;
use App\Livewire\SeminarRegistration;
use App\Livewire\SeminarRegistrationSuccess;
use App\Livewire\VisitorQrCode;
use App\Livewire\VisitorRegistration;
use App\Livewire\VisitorVerify;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to(filament()->getLoginUrl());
});

Route::livewire('/visitor/register', VisitorRegistration::class)->name('register.visitor');

Route::livewire('/seminar/register', SeminarRegistration::class)->name('register.seminar');
Route::livewire('/seminar/success/{id}', SeminarRegistrationSuccess::class)->name('register.seminar.success');

Route::livewire('/hands-on/register', HandsOnRegistration::class)->name('register.hands-on');

Route::livewire('/visitor/qr-code/{token}', VisitorQrCode::class)->name('visitor.qr-code');

Route::livewire('/attendance/qr-code/{token}', AttendanceQrCode::class)->name('attendance.qr-code');

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
    Route::livewire('/poster/submit', PosterSubmission::class)->name('poster.submit');
    Route::livewire('/attendance/verify/{token}', AttendanceVerify::class)->name('attendance.verify');
    Route::livewire('/visitor/verify/{token}', VisitorVerify::class)->name('visitor.verify');

    Route::get('/payment-proofs/{registration}/download', [PaymentProofController::class, 'show'])
        ->name('payment-proofs.download');

    Route::get('/payment-proofs/{registration}/preview', [PaymentProofController::class, 'preview'])
        ->name('payment-proofs.preview');

    Route::get('/database-backups/{filename}/download', [DatabaseBackupController::class, 'download'])
        ->name('database-backups.download');
});

Route::get('/mail-test', function () {
    Mail::raw('SMTP test successful', function ($message) {
        $message->to('rizkydhani15@gmail.com')
            ->subject('Laravel Mail Test');
    });
});
