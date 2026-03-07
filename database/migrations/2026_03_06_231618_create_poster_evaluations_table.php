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
        Schema::create('poster_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poster_submission_id')->constrained()->onDelete('cascade');
            $table->foreignId('judge_id')->constrained('users')->onDelete('cascade');
            $table->integer('content_score')->nullable();      // max 40
            $table->integer('creativity_score')->nullable();    // max 20
            $table->integer('visual_score')->nullable();        // max 20
            $table->integer('presentation_score')->nullable();  // max 20 (for finalists)
            $table->integer('total_score')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();
            $table->unique(['poster_submission_id', 'judge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poster_evaluations');
    }
};
