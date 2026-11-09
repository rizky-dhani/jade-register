<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The seminar runs for three days, so a seminar participant can be checked in
 * once per day. Hands-on rows keep day 1 — they are identified by their session
 * registration, not by the day.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedTinyInteger('day')->default(1)->after('activity_type');
        });

        // The old index allowed exactly one seminar row per registration. That is
        // no longer true: the day is what makes a seminar check-in unique, and
        // hands-on rows (day stays 1) give way to `attendance_unique_check`.
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendance_unique_check');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(
                ['seminar_registration_id', 'activity_type', 'hands_on_registration_id', 'day'],
                'attendance_unique_check'
            );
        });
    }

    public function down(): void
    {
        // Collapsing three days back to one would silently drop check-ins, so
        // rows past day 1 are removed first. `down()` is a schema rollback, not
        // a data rollback; the check-in evidence for days 2 and 3 cannot be
        // represented in the old shape.
        DB::table('attendances')->where('day', '>', 1)->delete();

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendance_unique_check');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(
                ['seminar_registration_id', 'activity_type', 'hands_on_registration_id'],
                'attendance_unique_check'
            );
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('day');
        });
    }
};
