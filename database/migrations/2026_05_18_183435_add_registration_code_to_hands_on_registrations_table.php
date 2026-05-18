<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hands_on_registrations', function (Blueprint $table) {
            $table->string('registration_code')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('hands_on_registrations', function (Blueprint $table) {
            $table->dropColumn('registration_code');
        });
    }
};
