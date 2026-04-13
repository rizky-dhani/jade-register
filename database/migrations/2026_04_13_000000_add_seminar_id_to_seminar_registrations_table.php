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
            // Check if column exists (SQLite may have created it during a failed migration)
            if (! Schema::hasColumn('seminar_registrations', 'seminar_id')) {
                $table->foreignId('seminar_id')
                    ->nullable()
                    ->after('selected_seminar')
                    ->constrained('seminars')
                    ->nullOnDelete();
            } else {
                // Add foreign key to existing column
                $table->foreign('seminar_id')
                    ->references('id')->on('seminars')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_registrations', function (Blueprint $table) {
            $table->dropForeign(['seminar_id']);

            // Only drop column if not using SQLite (which has limited ALTER TABLE support)
            if (! in_array(config('database.default'), ['sqlite'])) {
                $table->dropColumn('seminar_id');
            }
        });
    }
};
