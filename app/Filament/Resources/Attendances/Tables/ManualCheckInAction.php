<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\Attendance;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Services\AttendanceCheckInService;
use Closure;
use DomainException;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

/**
 * Check-in without the participant's QR code, for the case where their
 * confirmation email is not reachable at the desk.
 *
 * The select searches verified registrations only: an unverified payment is
 * never a valid check-in, so it should not be offered in the first place.
 */
class ManualCheckInAction
{
    public static function seminar(): Action
    {
        return self::base()
            ->schema([
                Select::make('seminar_registration_id')
                    ->label(__('filament.attendance.participant'))
                    ->required()
                    ->searchable()
                    ->searchPrompt(__('filament.attendance.search_participant'))
                    ->getSearchResultsUsing(fn (string $search): array => SeminarRegistration::query()
                        ->where('payment_status', 'verified')
                        ->where(function ($query) use ($search): void {
                            $query->where('name_license', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('registration_code', 'like', "%{$search}%");
                        })
                        ->orderBy('name_license')
                        ->limit(25)
                        ->get()
                        ->mapWithKeys(fn (SeminarRegistration $registration): array => [
                            $registration->id => self::seminarOptionLabel($registration),
                        ])
                        ->all())
                    ->getOptionLabelUsing(fn ($value): ?string => filled($value) && ($registration = SeminarRegistration::find($value))
                        ? self::seminarOptionLabel($registration)
                        : null),
            ])
            ->action(function (array $data): void {
                $staff = auth()->user();
                $registration = SeminarRegistration::find($data['seminar_registration_id'] ?? null);

                if (! $staff || ! $registration) {
                    return;
                }

                self::checkIn(fn () => app(AttendanceCheckInService::class)->checkInSeminar($registration, $staff));
            });
    }

    public static function handsOn(): Action
    {
        return self::base()
            ->schema([
                Select::make('hands_on_registration_id')
                    ->label(__('filament.attendance.hands_on_session'))
                    ->required()
                    ->searchable()
                    ->searchPrompt(__('filament.attendance.search_participant'))
                    ->getSearchResultsUsing(fn (string $search): array => HandsOnRegistration::query()
                        ->where('payment_status', 'verified')
                        ->with(['handsOn', 'seminarRegistration'])
                        ->where(function ($query) use ($search): void {
                            $query->where('name_license', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('registration_code', 'like', "%{$search}%")
                                ->orWhereHas('seminarRegistration', function ($seminar) use ($search): void {
                                    $seminar->where('name_license', 'like', "%{$search}%");
                                });
                        })
                        ->limit(25)
                        ->get()
                        ->mapWithKeys(fn (HandsOnRegistration $registration): array => [
                            $registration->id => self::handsOnOptionLabel($registration),
                        ])
                        ->all())
                    ->getOptionLabelUsing(fn ($value): ?string => filled($value) && ($registration = HandsOnRegistration::with(['handsOn', 'seminarRegistration'])->find($value))
                        ? self::handsOnOptionLabel($registration)
                        : null),
            ])
            ->action(function (array $data): void {
                $staff = auth()->user();
                $registration = HandsOnRegistration::find($data['hands_on_registration_id'] ?? null);

                if (! $staff || ! $registration) {
                    return;
                }

                self::checkIn(fn () => app(AttendanceCheckInService::class)->checkInHandsOn($registration, $staff));
            });
    }

    private static function base(): Action
    {
        return Action::make('manualCheckIn')
            ->label(__('filament.attendance.manual_check_in'))
            ->icon('heroicon-o-user-plus')
            ->color('gray')
            ->modalHeading(__('filament.attendance.manual_check_in'))
            ->modalDescription(__('filament.attendance.manual_check_in_description'))
            ->modalSubmitActionLabel(__('filament.attendance.check_in'))
            // Gated on the desk role for the same reason as check-out: the Admin
            // role is never granted `update attendances`, and the people at the
            // desk are the ones who scan (see AttendanceVerify::canCheckIn).
            ->visible(fn (): bool => auth()->user()?->hasRole(['Admin', 'Super Admin']) ?? false);
    }

    /**
     * The select offers verified registrations only, but the submitted id is
     * still user-controlled, so the service rule is enforced here too.
     */
    private static function checkIn(Closure $callback): void
    {
        try {
            self::report($callback());
        } catch (DomainException) {
            Notification::make()
                ->danger()
                ->title(__('filament.attendance.not_verified'))
                ->send();
        }
    }

    private static function report(Attendance $attendance): void
    {
        $participant = $attendance->seminarRegistration?->name_license
            ?? $attendance->handsOnRegistration?->name_license
            ?? $attendance->handsOnRegistration?->email
            ?? __('filament.attendance.participant_name');

        $notification = Notification::make();

        if ($attendance->wasRecentlyCreated) {
            $notification->success()->title(__('filament.attendance.check_in_success', ['name' => $participant]));
        } else {
            $notification->warning()->title(__('filament.attendance.already_checked_in', ['name' => $participant]));
        }

        $notification->send();
    }

    private static function seminarOptionLabel(SeminarRegistration $registration): string
    {
        return collect([$registration->name_license, $registration->registration_code, $registration->email])
            ->filter()
            ->unique()
            ->implode(' · ');
    }

    private static function handsOnOptionLabel(HandsOnRegistration $registration): string
    {
        return collect([
            $registration->seminarRegistration?->name_license ?? $registration->name_license,
            $registration->handsOn?->name,
            $registration->registration_code,
            $registration->email,
        ])
            ->filter()
            ->unique()
            ->implode(' · ');
    }
}
