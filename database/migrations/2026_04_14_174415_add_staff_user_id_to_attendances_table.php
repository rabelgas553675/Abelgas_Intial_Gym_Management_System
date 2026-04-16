<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add staff_user_id to attendances table so staff/admin/instructor
     * time-in/out can be tracked by user ID rather than the fragile
     * qr_token string stored in `scanned_by`.
     *
     * Also ensures qr_code_path exists on user_qr_tokens table so
     * QR images render on the qr-list page for staff.
     */
    public function up(): void
    {
        // ── attendances table ──────────────────────────────────────────────
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'staff_user_id')) {
                // Nullable FK to users — null when the log is for a member
                $table->unsignedBigInteger('staff_user_id')->nullable()->after('member_id');
                $table->foreign('staff_user_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        // ── user_qr_tokens table ───────────────────────────────────────────
        Schema::table('user_qr_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('user_qr_tokens', 'qr_code_path')) {
                $table->string('qr_code_path')->nullable()->after('qr_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['staff_user_id']);
            $table->dropColumn('staff_user_id');
        });

        Schema::table('user_qr_tokens', function (Blueprint $table) {
            $table->dropColumn('qr_code_path');
        });
    }
};