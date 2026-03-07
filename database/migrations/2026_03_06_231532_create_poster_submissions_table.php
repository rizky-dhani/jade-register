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
        Schema::create('poster_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('seminar_registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('poster_category_id')->constrained()->onDelete('restrict');
            $table->foreignId('poster_topic_id')->constrained()->onDelete('restrict');
            $table->string('title');
            $table->text('abstract_text');
            $table->string('author_names');
            $table->string('author_emails');
            $table->string('affiliation');
            $table->string('presenter_name');
            $table->string('poster_file_path')->nullable();
            $table->enum('status', ['draft', 'submitted', 'under_review', 'accepted', 'finalist', 'winner', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->integer('total_score')->nullable();
            $table->integer('rank')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('finalist_announced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poster_submissions');
    }
};
