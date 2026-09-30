<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('coach_membership_type')->nullable()->after('membership_type');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('coach_membership_type');
        });
    }
};
