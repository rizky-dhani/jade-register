<?php

namespace Database\Seeders;

use App\Models\Addon;
use App\Models\AddonRegistration;
use App\Models\Attendance;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorAttendance;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use App\Services\VisitorQrTokenService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAttendanceSeeder extends Seeder
{
    /**
     * Every registration below is payment-verified with a live QR token, so each
     * one can be scanned at the attendance desk and its confirmation email is
     * waiting in Mailpit.
     *
     * Lunch states are deliberately mixed:
     *   1-3  lunch included in the seminar package
     *   4    lunch bought as a verified LUNCH_ADDON
     *   5    no lunch at all, so the coupon button must stay hidden
     *
     * Two seminar participants and their hands-on sessions are already checked in,
     * and two visitors are already at the desk, so every attendance list in the
     * admin panel has rows to show. The rest stay unscanned for a live demo.
     */
    public function run(): void
    {
        $this->prepareSettings();

        $deskStaff = $this->createDeskStaff();
        $lunchPackage = $this->createSeminarPackage();
        $noLunchPackage = $this->createLunchlessSeminarPackage();
        $lunchAddon = $this->createLunchAddon();

        $registrations = $this->createSeminarRegistrations(
            $lunchPackage,
            $noLunchPackage,
            $lunchAddon,
            $deskStaff
        );

        $this->createHandsOnRegistrations($registrations);
        $this->createVisitors($deskStaff);
        $this->createAttendances($registrations, $deskStaff);

        $this->command?->newLine();
        $this->command?->info('Demo attendance data ready.');
        $this->command?->table(
            ['Role', 'Email', 'Password'],
            [['Admin (lunch desk)', $deskStaff->email, 'Password123!']]
        );
        $this->command?->info('Admin panel: '.url('/admin'));
        $this->command?->info('Mailpit: http://127.0.0.1:8025');
    }

    private function prepareSettings(): void
    {
        // Lunch redemption only opens when the seminar event date is today.
        Setting::updateOrCreate(
            ['key' => 'seminar_event_date'],
            [
                'label' => 'Seminar Event Date',
                'value' => today()->toDateString(),
                'type' => 'date',
                'description' => 'Calendar date when lunch coupons may be redeemed. Redemption is blocked until a date is configured.',
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'seminar_registration_opens_at'],
            ['value' => now()->subMonth()->toDateTimeString()]
        );

        Setting::updateOrCreate(
            ['key' => 'seminar_registration_close_at'],
            ['value' => now()->addMonth()->toDateTimeString()]
        );
    }

    private function createDeskStaff(): User
    {
        $staff = User::updateOrCreate(
            ['email' => 'attendancedesk@jakartadentalexhibitions.id'],
            [
                'name' => 'Attendance Desk',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
            ]
        );

        // Only Admin / Super Admin may redeem a lunch coupon.
        $staff->syncRoles(['Admin']);

        return $staff;
    }

    private function createSeminarPackage(): Seminar
    {
        return Seminar::updateOrCreate(
            ['code' => 'DEMO-SEM-LUNCH'],
            [
                'name' => 'JADE 2026 Seminar (Demo)',
                'description' => 'Demo seminar package that includes lunch.',
                'applies_to' => 'all',
                'original_price' => 750000,
                'discounted_price' => 750000,
                'max_seats' => 500,
                'currency' => 'IDR',
                'includes_lunch' => true,
                'is_early_bird' => false,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    private function createLunchlessSeminarPackage(): Seminar
    {
        return Seminar::updateOrCreate(
            ['code' => 'DEMO-SEM-NOLUNCH'],
            [
                'name' => 'JADE 2026 Seminar (Demo, No Lunch)',
                'description' => 'Demo seminar package without lunch.',
                'applies_to' => 'all',
                'original_price' => 500000,
                'discounted_price' => 500000,
                'max_seats' => 500,
                'currency' => 'IDR',
                'includes_lunch' => false,
                'is_early_bird' => false,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );
    }

    private function createLunchAddon(): Addon
    {
        return Addon::updateOrCreate(
            ['code' => 'LUNCH_ADDON'],
            [
                'name' => 'Lunch Add-On',
                'description' => 'Add full lunch to your seminar package.',
                'price' => 75000,
                'currency' => 'IDR',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    /**
     * @return array<int, SeminarRegistration>
     */
    private function createSeminarRegistrations(
        Seminar $lunchPackage,
        Seminar $noLunchPackage,
        Addon $lunchAddon,
        User $deskStaff,
    ): array {
        $indonesia = Country::where('is_indonesia', true)->firstOrFail();

        $participants = [
            ['Ayu Kartika Dewi', 'ayu.dewi@example.test', 'Sp.KG', 'Cabang Jakarta', 'package'],
            ['Budi Santoso', 'budi.santoso@example.test', 'Dokter Gigi Umum', 'Cabang Bandung', 'package'],
            ['Citra Maharani', 'citra.maharani@example.test', 'Sp.Ort', 'Cabang Surabaya', 'package'],
            ['Dimas Prabowo', 'dimas.prabowo@example.test', 'Sp.Pros', 'Cabang Yogyakarta', 'addon'],
            ['Eka Nur Fadilah', 'eka.fadilah@example.test', 'Dokter Gigi Umum', 'Cabang Medan', 'none'],
        ];

        $registrations = [];

        foreach ($participants as $index => [$name, $email, $kompetensi, $branch, $lunchSource]) {
            $package = $lunchSource === 'package' ? $lunchPackage : $noLunchPackage;
            $addonAmount = $lunchSource === 'addon' ? (int) $lunchAddon->price : 0;

            $registration = SeminarRegistration::updateOrCreate(
                ['email' => $email],
                [
                    'registration_code' => 'JADE-SEM-2026-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'name_license' => $name,
                    'nik' => '3174'.str_pad((string) (100000000000 + $index), 12, '0', STR_PAD_LEFT),
                    'pdgi_branch' => $branch,
                    'kompetensi' => $kompetensi,
                    'phone' => '+62812'.str_pad((string) (10000000 + $index), 8, '0', STR_PAD_LEFT),
                    'country_id' => $indonesia->id,
                    'language' => $index % 2 === 0 ? 'id' : 'en',
                    'registration_type' => 'online',
                    'selected_seminar' => $package->code,
                    'seminar_id' => $package->id,
                    'payment_method' => 'bank_transfer',
                    'amount' => (int) $package->current_price + $addonAmount,
                    'currency' => 'IDR',
                    'payment_status' => 'verified',
                    'payment_proof_path' => 'payment-proofs/demo.jpg',
                    'verified_at' => now(),
                    'wants_poster_competition' => false,
                    'wants_hands_on' => false,
                    'hands_on_total_amount' => 0,
                    'addons_total_amount' => $addonAmount,
                    'status' => 'Dentist',
                ]
            );

            if ($lunchSource === 'addon') {
                AddonRegistration::updateOrCreate(
                    [
                        'seminar_registration_id' => $registration->id,
                        'addon_id' => $lunchAddon->id,
                    ],
                    [
                        'amount' => $lunchAddon->price,
                        'currency' => $lunchAddon->currency,
                        'payment_proof_path' => 'payment-proofs/demo.jpg',
                        'payment_status' => 'verified',
                        'verified_at' => now(),
                        'verified_by' => $deskStaff->id,
                    ]
                );
            }

            app(QrTokenService::class)->generate($registration);
            $this->sendSeminarConfirmation($registration->refresh());

            $registrations[] = $registration;
        }

        return $registrations;
    }

    /**
     * @param  array<int, SeminarRegistration>  $registrations
     */
    private function createHandsOnRegistrations(array $registrations): void
    {
        $events = HandsOn::query()
            ->where('is_active', true)
            ->orderBy('event_date')
            ->orderBy('id')
            ->get();

        if ($events->isEmpty()) {
            $this->command?->warn('No Hands On events found. Run HandsOnSeeder first.');

            return;
        }

        foreach ($registrations as $index => $registration) {
            $event = $events[$index % $events->count()];

            $handsOnRegistration = HandsOnRegistration::updateOrCreate(
                [
                    'seminar_registration_id' => $registration->id,
                    'hands_on_id' => $event->id,
                ],
                [
                    'registration_code' => HandsOnRegistration::generateRegistrationCode(),
                    'registration_type' => 'combined',
                    'payment_status' => 'verified',
                    'payment_proof_path' => 'payment-proofs/demo.jpg',
                    'verified_at' => now(),
                    'name' => $registration->name_license,
                    'name_license' => $registration->name_license,
                    'email' => $registration->email,
                    'phone' => $registration->phone,
                    'nik' => $registration->nik,
                    'pdgi_branch' => $registration->pdgi_branch,
                    'kompetensi' => $registration->kompetensi,
                    'status' => $registration->status,
                    'country_id' => $registration->country_id,
                    'payment_method' => $registration->payment_method,
                    'language' => $registration->language,
                ]
            );

            app(QrTokenService::class)->generateForHandsOn($handsOnRegistration);
            $this->sendHandsOnConfirmation($handsOnRegistration->refresh());

            $registration->update([
                'wants_hands_on' => true,
                'hands_on_total_amount' => (int) $event->price,
            ]);
        }
    }

    /**
     * Visitor walk-ins. Two are already at the desk; the other two still hold an
     * unscanned QR so the visitor verification page can be demoed.
     */
    private function createVisitors(User $deskStaff): void
    {
        $visitors = [
            ['Rina Anggraini', 'rina.anggraini@example.test', '+6281290000001', 'Universitas Indonesia', true],
            ['Yusuf Hidayat', 'yusuf.hidayat@example.test', '+6281290000002', 'RS Cipto Mangunkusumo', true],
            ['Melati Puspita', 'melati.puspita@example.test', '+6281290000003', 'Klinik Sehat Gigi', false],
            ['Rangga Wijaya', 'rangga.wijaya@example.test', '+6281290000004', 'PDGI Cabang Bali', false],
        ];

        foreach ($visitors as $index => [$name, $email, $phone, $affiliation, $isCheckedIn]) {
            $visitor = Visitor::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'phone' => $phone, 'affiliation' => $affiliation]
            );

            if (! $visitor->qr_token) {
                app(VisitorQrTokenService::class)->generate($visitor);
            }

            if (! $isCheckedIn) {
                continue;
            }

            $checkedInAt = now()->subMinutes(90 - ($index * 15));

            $visitor->update(['scanned_at' => $checkedInAt]);

            VisitorAttendance::firstOrCreate(
                ['visitor_id' => $visitor->id],
                ['checked_in_at' => $checkedInAt, 'checked_in_by' => $deskStaff->id]
            );
        }
    }

    /**
     * Check in the first two seminar participants and their hands-on sessions.
     *
     * @param  array<int, SeminarRegistration>  $registrations
     */
    private function createAttendances(array $registrations, User $deskStaff): void
    {
        foreach (array_slice($registrations, 0, 2) as $index => $registration) {
            $attendance = Attendance::firstOrCreate(
                [
                    'seminar_registration_id' => $registration->id,
                    'hands_on_registration_id' => null,
                    'activity_type' => 'seminar',
                ],
                [
                    'checked_in_at' => now()->subMinutes(60 - ($index * 10)),
                    'checked_in_by' => $deskStaff->id,
                ]
            );

            // The first participant has already left the hall, so the check-out
            // column has something to show. Kept outside firstOrCreate so
            // re-seeding still fills it in.
            if ($index === 0 && $attendance->checked_out_at === null) {
                $attendance->update([
                    'checked_out_at' => $attendance->checked_in_at->copy()->addMinutes(45),
                    'checked_out_by' => $deskStaff->id,
                ]);
            }
        }

        foreach (array_slice($registrations, 0, 2) as $index => $registration) {
            $handsOnRegistration = $registration->handsOnRegistrations()->first();

            if (! $handsOnRegistration) {
                continue;
            }

            Attendance::firstOrCreate(
                [
                    'hands_on_registration_id' => $handsOnRegistration->id,
                    'activity_type' => 'hands_on',
                ],
                [
                    'seminar_registration_id' => $registration->id,
                    'checked_in_at' => now()->subMinutes(45 - ($index * 10)),
                    'checked_in_by' => $deskStaff->id,
                ]
            );
        }
    }

    private function sendSeminarConfirmation(SeminarRegistration $registration): void
    {
        app(RegistrationService::class)->sendSeminarSubmissionConfirmation($registration);
    }

    private function sendHandsOnConfirmation(HandsOnRegistration $registration): void
    {
        app(RegistrationService::class)->sendHandsOnSubmissionConfirmation($registration);
    }
}
