<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seminar_registrations', function (Blueprint $table) {
            $table->timestamp('confirmation_email_sent_at')->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_registrations', function (Blueprint $table) {
            $table->dropColumn('confirmation_email_sent_at');
        });
    }
};
