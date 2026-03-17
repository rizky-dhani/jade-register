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
            // Rename pricing_tier to selected_seminar
            $table->renameColumn('pricing_tier', 'selected_seminar');

            // Add payment_method column after selected_seminar
            $table->string('payment_method')->default('bank_transfer')->after('selected_seminar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_registrations', function (Blueprint $table) {
            // Drop payment_method column
            $table->dropColumn('payment_method');

            // Rename selected_seminar back to pricing_tier
            $table->renameColumn('selected_seminar', 'pricing_tier');
        });
    }
};
