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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('venue_name')->nullable()->after('key');
            $table->string('venue_address')->nullable()->after('venue_name');
            $table->decimal('venue_latitude', 10, 7)->nullable()->after('venue_address');
            $table->decimal('venue_longitude', 10, 7)->nullable()->after('venue_latitude');
            $table->integer('venue_detection_radius')->default(500)->after('venue_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'venue_name',
                'venue_address',
                'venue_latitude',
                'venue_longitude',
                'venue_detection_radius',
            ]);
        });
    }
};
