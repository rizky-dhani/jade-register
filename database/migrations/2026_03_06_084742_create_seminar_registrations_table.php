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
        Schema::create('seminar_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_code')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('affiliation')->nullable();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->enum('registration_type', ['online', 'offline']);
            $table->enum('pricing_tier', [
                'online_local_snack_only',
                'online_local_snack_lunch',
                'online_international_snack_lunch',
                'offline_local_snack_lunch_1',
                'offline_local_snack_lunch_2',
                'offline_international_snack_lunch',
            ]);
            $table->unsignedBigInteger('amount');
            $table->enum('payment_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('payment_proof_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['email', 'registration_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seminar_registrations');
    }
};
