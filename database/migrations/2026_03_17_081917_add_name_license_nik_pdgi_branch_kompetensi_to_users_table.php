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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->constrained('countries')->after('id');
            $table->string('name_license')->nullable()->after('name');
            $table->string('nik')->nullable()->after('name_license');
            $table->string('pdgi_branch')->nullable()->after('nik');
            $table->string('kompetensi')->nullable()->after('pdgi_branch');
            $table->string('phone')->nullable()->after('kompetensi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
            $table->dropColumn(['name_license', 'nik', 'pdgi_branch', 'kompetensi', 'phone']);
        });
    }
};
