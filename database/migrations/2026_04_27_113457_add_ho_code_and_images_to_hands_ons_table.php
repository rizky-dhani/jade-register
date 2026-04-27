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
            $table->string('ho_code')->nullable()->after('name');
            $table->string('flyer_path')->nullable()->after('ho_code');
            $table->string('skp_path')->nullable()->after('flyer_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hands_ons', function (Blueprint $table) {
            $table->dropColumn(['ho_code', 'flyer_path', 'skp_path']);
        });
    }
};
