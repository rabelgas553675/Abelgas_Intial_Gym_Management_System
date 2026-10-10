<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_change_requests', function (Blueprint $table) {
            $table->id();

            // History record: a member who has plan-change requests can't be hard-deleted
            // (same rule as payments / attendances / coach requests).
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();

            // The coach who must review it. Nullable so deleting a coach never deletes history.
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('current_plan', 50)->nullable();
            $table->string('requested_plan', 50);
            $table->text('reason')->nullable();                 // member's optional reason

            // Pending | Approved | Rejected | Cancelled
            $table->string('status', 20)->default('Pending')->index();

            // Database-level duplicate guard: holds the member id ONLY while the request is
            // Pending (NULL otherwise). A unique index allows many NULLs but only one id,
            // so a member can never have two pending requests even under a race.
            $table->unsignedBigInteger('pending_member_id')->nullable()->unique();

            $table->text('coach_feedback')->nullable();         // rejection reason / note
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['instructor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_change_requests');
    }
};