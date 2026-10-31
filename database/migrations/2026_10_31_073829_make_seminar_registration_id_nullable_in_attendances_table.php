<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A standalone Hands On registration has no seminar registration, so its
     * check-in row cannot carry one either. The column was NOT NULL, which made
     * check-in throw for every hands-on QR token.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('seminar_registration_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('seminar_registration_id')->nullable(false)->change();
        });
    }
};
