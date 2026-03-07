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
            $table->boolean('wants_poster_competition')->default(false)->after('payment_proof_path');
            $table->foreignId('user_id')->nullable()->constrained()->after('wants_poster_competition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_registrations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['wants_poster_competition', 'user_id']);
        });
    }
};
