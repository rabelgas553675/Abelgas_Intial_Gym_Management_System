<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * members.status was ENUM('Active','Expired','Pending'), so saving the
     * staff-controlled states "Inactive" / "Suspended" failed with
     * SQLSTATE[01000] 1265 "Data truncated for column 'status'".
     *
     * A plain string column accepts every state the app uses
     * (Active, Expired, Pending, Inactive, Suspended). Existing values are kept.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('status', 20)->default('Active')->change();
        });
    }

    public function down(): void
    {
        // Map unsupported values back to something the old ENUM accepts.
        DB::table('members')
            ->whereIn('status', ['Inactive', 'Suspended'])
            ->update(['status' => 'Expired']);

        Schema::table('members', function (Blueprint $table) {
            $table->enum('status', ['Active', 'Expired', 'Pending'])->default('Active')->change();
        });
    }
};