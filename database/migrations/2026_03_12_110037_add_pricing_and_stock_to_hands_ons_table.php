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
        Schema::table('hands_ons', function (Blueprint $table) {
            $table->integer('original_price')->nullable()->after('price');
            $table->integer('discounted_price')->nullable()->after('original_price');
            $table->integer('stock_limit')->nullable()->after('discounted_price');
            $table->timestamp('early_bird_deadline')->nullable()->after('stock_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hands_ons', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'discounted_price', 'stock_limit', 'early_bird_deadline']);
        });
    }
};
