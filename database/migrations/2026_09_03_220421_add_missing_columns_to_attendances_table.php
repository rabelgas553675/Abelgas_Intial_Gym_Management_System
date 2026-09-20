<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'entry_method')) {
                $table->enum('entry_method', ['qr_scan', 'manual'])
                      ->default('qr_scan')
                      ->after('date');
            }

            if (!Schema::hasColumn('attendances', 'scanned_by')) {
                $table->string('scanned_by', 64)->nullable()->after('entry_method');
            }

            if (!Schema::hasColumn('attendances', 'staff_user_id')) {
                $table->unsignedBigInteger('staff_user_id')->nullable()->after('scanned_by');

                $table->foreign('staff_user_id')
                      ->references('id')->on('users')
                      ->onDelete('set null');
            }

            if (!Schema::hasColumn('attendances', 'duration_minutes')) {
                $table->integer('duration_minutes')->nullable()->after('time_out');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'staff_user_id')) {
                $table->dropForeign(['staff_user_id']);
                $table->dropColumn('staff_user_id');
            }
            if (Schema::hasColumn('attendances', 'scanned_by')) {
                $table->dropColumn('scanned_by');
            }
            if (Schema::hasColumn('attendances', 'entry_method')) {
                $table->dropColumn('entry_method');
            }
            if (Schema::hasColumn('attendances', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
        });
    }
};