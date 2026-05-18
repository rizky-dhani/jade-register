<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the foreign key so we can make the column nullable
        Schema::table('hands_on_registrations', function (Blueprint $table) {
            $table->dropForeign(['seminar_registration_id']);
        });

        // Make seminar_registration_id nullable
        if (DB::getDriverName() === 'sqlite') {
            // SQLite needs a table rebuild for column modification
            Schema::create('hands_on_registrations_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seminar_registration_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('hands_on_id')->constrained()->cascadeOnDelete();
                $table->string('registration_type')->default('combined');
                $table->string('payment_status')->default('pending');
                $table->string('payment_proof_path')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
                $table->unique(['seminar_registration_id', 'hands_on_id']);
            });

            // Copy existing data
            DB::statement('INSERT INTO hands_on_registrations_new SELECT * FROM hands_on_registrations');

            Schema::drop('hands_on_registrations');
            Schema::rename('hands_on_registrations_new', 'hands_on_registrations');
        } else {
            Schema::table('hands_on_registrations', function (Blueprint $table) {
                $table->foreignId('seminar_registration_id')->nullable()->change();
                $table->foreign('seminar_registration_id')->references('id')->on('seminar_registrations')->onDelete('cascade');
            });
        }

        // Add new participant columns (for standalone registrations)
        Schema::table('hands_on_registrations', function (Blueprint $table) {
            $table->string('name')->nullable()->after('verified_at');
            $table->string('name_license')->nullable()->after('name');
            $table->string('email')->nullable()->after('name_license');
            $table->string('phone')->nullable()->after('email');
            $table->string('nik', 16)->nullable()->after('phone');
            $table->string('pdgi_branch')->nullable()->after('nik');
            $table->string('kompetensi')->nullable()->after('pdgi_branch');
            $table->string('status')->nullable()->after('kompetensi');
            $table->foreignId('country_id')->nullable()->constrained()->after('status');
            $table->string('payment_method')->nullable()->after('country_id');
            $table->string('language', 5)->default('en')->after('payment_method');
        });
    }

    public function down(): void
    {
        // Drop new columns
        Schema::table('hands_on_registrations', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn([
                'name', 'name_license', 'email', 'phone', 'nik',
                'pdgi_branch', 'kompetensi', 'status', 'country_id',
                'payment_method', 'language',
            ]);
        });

        // Revert seminar_registration_id back to NOT NULL
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('hands_on_registrations_old', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seminar_registration_id')->constrained()->cascadeOnDelete();
                $table->foreignId('hands_on_id')->constrained()->cascadeOnDelete();
                $table->string('registration_type')->default('combined');
                $table->string('payment_status')->default('pending');
                $table->string('payment_proof_path')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
                $table->unique(['seminar_registration_id', 'hands_on_id']);
            });

            DB::statement('INSERT INTO hands_on_registrations_old SELECT id, seminar_registration_id, hands_on_id, registration_type, payment_status, payment_proof_path, verified_at, created_at, updated_at FROM hands_on_registrations');

            Schema::drop('hands_on_registrations');
            Schema::rename('hands_on_registrations_old', 'hands_on_registrations');
        } else {
            Schema::table('hands_on_registrations', function (Blueprint $table) {
                $table->dropForeign(['seminar_registration_id']);
                $table->foreignId('seminar_registration_id')->change();
                $table->foreign('seminar_registration_id')->references('id')->on('seminar_registrations')->onDelete('cascade');
            });
        }
    }
};
