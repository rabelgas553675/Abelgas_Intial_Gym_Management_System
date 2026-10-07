<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Legacy coach_fee rows may have payments.instructor_id = NULL.
     * Backfill it from the member's assigned instructor (members.instructor_id)
     * only where it is missing and the source value exists. Never overwrites data.
     */
    public function up(): void
    {
        DB::table('payments')
            ->where('payment_type', 'coach_fee')
            ->whereNull('instructor_id')
            ->orderBy('id')
            ->each(function ($payment) {
                $instructorId = DB::table('members')->where('id', $payment->member_id)->value('instructor_id');
                if ($instructorId && DB::table('users')->where('id', $instructorId)->exists()) {
                    DB::table('payments')->where('id', $payment->id)->update(['instructor_id' => $instructorId]);
                }
            });
    }

    public function down(): void
    {
        // Data repair only; intentionally not reversible.
    }
};