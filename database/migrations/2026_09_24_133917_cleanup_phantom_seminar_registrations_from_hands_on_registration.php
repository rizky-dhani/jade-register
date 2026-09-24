<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The standalone Hands-On registration form used to create a parent
     * SeminarRegistration (registration_type=hands_on, seminar_id=NULL) purely
     * to hang the HandsOnRegistration rows off it. That polluted the Seminar
     * registrations list, its exports, and the seminar capacity counter.
     *
     * Detach the HandsOnRegistration rows before deleting the phantom parents:
     * the foreign key is cascadeOnDelete, so a plain delete would take the real
     * hands-on registrations with it.
     */
    public function up(): void
    {
        $phantomIds = DB::table('seminar_registrations')
            ->whereNull('seminar_id')
            ->where('registration_type', 'hands_on')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('hands_on_registrations')
                    ->whereColumn('hands_on_registrations.seminar_registration_id', 'seminar_registrations.id');
            })
            ->pluck('id');

        if ($phantomIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($phantomIds) {
            DB::table('hands_on_registrations')
                ->whereIn('seminar_registration_id', $phantomIds)
                ->update(['seminar_registration_id' => null]);

            DB::table('seminar_registrations')
                ->whereIn('id', $phantomIds)
                ->delete();
        });
    }

    /**
     * Not reversible: the deleted rows were artifacts of a bug. The hands-on
     * registrations they pointed at are preserved with a NULL parent.
     */
    public function down(): void {}
};
