<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lunch_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_registration_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamp('redeemed_at');
            $table->foreignId('redeemed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('redeemed_by_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lunch_redemptions');
    }
};
