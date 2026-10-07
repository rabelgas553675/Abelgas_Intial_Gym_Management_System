<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'payment_type')) {
                $table->string('payment_type')->default('gym_fee')->after('member_id');
            }

            if (! Schema::hasColumn('payments', 'fitness_plan')) {
                $table->string('fitness_plan')->nullable()->after('payment_type');
            }

            if (! Schema::hasColumn('payments', 'membership_type')) {
                $table->string('membership_type')->nullable()->after('fitness_plan');
            }

            if (! Schema::hasColumn('payments', 'platform_fee')) {
                $table->decimal('platform_fee', 10, 2)->nullable()->after('payment_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'platform_fee')) {
                $table->dropColumn('platform_fee');
            }

            if (Schema::hasColumn('payments', 'membership_type')) {
                $table->dropColumn('membership_type');
            }

            if (Schema::hasColumn('payments', 'fitness_plan')) {
                $table->dropColumn('fitness_plan');
            }

            if (Schema::hasColumn('payments', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
        });
    }
};