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
            $table->string('name_license')->nullable()->change();
            $table->string('nik')->nullable()->change();
            $table->string('pdgi_branch')->nullable()->change();
            $table->string('kompetensi')->nullable()->change();
            $table->string('status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seminar_registrations', function (Blueprint $table) {
            $table->string('name_license')->nullable(false)->change();
            $table->string('nik')->nullable(false)->change();
            $table->string('pdgi_branch')->nullable(false)->change();
            $table->string('kompetensi')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
        });
    }
};
