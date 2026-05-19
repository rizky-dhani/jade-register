<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hands_on_registrations', function (Blueprint $table) {
            $table->timestamp('confirmation_email_sent_at')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('hands_on_registrations', function (Blueprint $table) {
            $table->dropColumn('confirmation_email_sent_at');
        });
    }
};
