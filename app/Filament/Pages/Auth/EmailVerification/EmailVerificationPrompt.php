<?php

namespace App\Filament\Pages\Auth\EmailVerification;

use App\Notifications\VerifyEmail;
use Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt as BasePage;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use LogicException;

class EmailVerificationPrompt extends BasePage
{
    protected function sendEmailVerificationNotification(MustVerifyEmail $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        if (! method_exists($user, 'notify')) {
            $userClass = $user::class;

            throw new LogicException("Model [{$userClass}] does not have a [notify()] method.");
        }

        $user->notify(new VerifyEmail(
            Filament::getVerifyEmailUrl($user)
        ));
    }
}
