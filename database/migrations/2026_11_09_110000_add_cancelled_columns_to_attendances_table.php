<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cancelling a check-in keeps the attendance row and marks it cancelled.
 *
 * The desk occasionally checks the wrong participant in. Deleting the row would
 * erase the evidence that a check-in ever happened, and the codebase already
 * treats attendance rows as evidence (check-out never overwrites a stamp), so a
 * cancelled row is retained with who cancelled it and when.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('checked_out_by');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Cancelled rows cannot be represented without these columns, and
        // un-cancelling them would fabricate check-ins that the desk removed.
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn('cancelled_at');
        });
    }
};
