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
        Schema::create('hands_on_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('hands_on_event_id')->constrained()->onDelete('cascade');
            $table->string('registration_type')->default('combined');
            $table->string('payment_status')->default('pending');
            $table->string('payment_proof_path')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['seminar_registration_id', 'hands_on_event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hands_on_registrations');
    }
};
