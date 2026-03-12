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
        Schema::table('seminars', function (Blueprint $table) {
            $table->integer('original_price')->nullable()->after('applies_to');
            $table->integer('discounted_price')->nullable()->after('original_price');
            $table->integer('max_seats')->nullable()->after('discounted_price');
            $table->timestamp('early_bird_deadline')->nullable()->after('max_seats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminars', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'discounted_price', 'max_seats', 'early_bird_deadline']);
        });
    }
};
