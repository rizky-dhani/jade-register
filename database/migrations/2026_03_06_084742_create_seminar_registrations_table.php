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
            $table->string('registration_code')->unique()->nullable();
            $table->string('email');
            $table->string('name');
            $table->string('name_license');
            $table->string('nik');
            $table->string('npa');
            $table->string('pdgi_branch');
            $table->string('phone');
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('registration_type')->default('online');
            $table->string('pricing_tier')->nullable();
            $table->string('currency')->default('IDR');
            $table->string('payment_status')->default('pending');
            $table->string('payment_proof_path')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
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
