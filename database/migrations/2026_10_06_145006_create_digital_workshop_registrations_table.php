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
        Schema::create('digital_workshop_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_code')->unique()->nullable();
            $table->string('qr_token', 64)->unique()->nullable();
            $table->timestamp('qr_expires_at')->nullable();
            $table->foreignId('digital_workshop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seminar_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->string('registration_type')->default('standalone');
            $table->integer('amount')->default(1199000);
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('name_license')->nullable();
            $table->string('nik')->nullable();
            $table->string('pdgi_branch')->nullable();
            $table->string('phone');
            $table->string('kompetensi')->nullable();
            $table->string('status')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->default('bank_transfer');
            $table->string('payment_proof_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('confirmation_email_sent_at')->nullable();
            $table->string('language')->default('id');
            $table->timestamps();

            $table->index(['digital_workshop_id', 'payment_status']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_workshop_registrations');
    }
};
