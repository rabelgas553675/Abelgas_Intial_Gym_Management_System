<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coach_requests', function (Blueprint $table) {
            $table->string('source', 20)->default('member');
            $table->string('coach_membership_type', 20)->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('starts_on')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->text('rejection_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('coach_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
            $table->dropConstrainedForeignId('requested_by');
            $table->dropColumn([
                'source',
                'coach_membership_type',
                'starts_on',
                'responded_at',
                'activated_at',
                'rejection_reason',
            ]);
        });
    }
};