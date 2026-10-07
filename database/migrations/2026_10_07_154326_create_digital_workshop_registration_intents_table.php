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
        Schema::create('digital_workshop_registration_intents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_workshop_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('awaiting_seminar');
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('name_license')->nullable();
            $table->string('nik')->nullable();
            $table->string('pdgi_branch')->nullable();
            $table->string('phone');
            $table->string('kompetensi')->nullable();
            $table->string('status_participant')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_method')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->string('language')->default('id');
            $table->foreignId('seminar_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('digital_workshop_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();

            // The observer's lookup path; without it every seminar verification
            // does a table scan.
            $table->index(['email', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_workshop_registration_intents');
    }
};
