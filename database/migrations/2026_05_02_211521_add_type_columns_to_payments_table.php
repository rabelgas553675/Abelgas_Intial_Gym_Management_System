<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{public function up(): void
{
    Schema::table('payments', function (Blueprint $table) {
        if (!Schema::hasColumn('payments', 'payment_type')) {
            $table->string('payment_type')->default('gym_fee')->after('member_id');
        }
        if (!Schema::hasColumn('payments', 'fitness_plan')) {
            $table->string('fitness_plan')->nullable()->after('payment_type');
        }
        if (!Schema::hasColumn('payments', 'membership_type')) {
            $table->string('membership_type')->nullable()->after('fitness_plan');
        }
        // add checks for any other columns in this migration
    });
}

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->dropColumn(['payment_type', 'instructor_id', 'platform_fee']);
        });
    }
};