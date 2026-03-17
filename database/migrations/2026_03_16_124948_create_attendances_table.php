<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seminar_registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hands_on_registration_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('activity_type', ['seminar', 'hands_on']);
            $table->timestamp('checked_in_at');
            $table->foreignId('checked_in_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['seminar_registration_id', 'activity_type']);
            $table->unique(['seminar_registration_id', 'activity_type', 'hands_on_registration_id'], 'attendance_unique_check');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
