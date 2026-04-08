<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find and remove duplicate emails, keeping only the oldest (original) registration per email
        $duplicates = DB::table('seminar_registrations')
            ->select('email', DB::raw('MIN(id) as min_id'))
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('seminar_registrations')
                ->where('email', $duplicate->email)
                ->where('id', '!=', $duplicate->min_id)
                ->delete();
        }

        // Now add the unique constraint
        Schema::table('seminar_registrations', function (Blueprint $table) {
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_registrations', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });
    }
};
