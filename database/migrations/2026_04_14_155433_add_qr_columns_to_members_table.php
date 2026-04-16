<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'qr_id')) {
                $table->string('qr_id')->nullable()->unique()->after('photo');
            }
            if (!Schema::hasColumn('members', 'qr_code_path')) {
                $table->string('qr_code_path')->nullable()->after('qr_id');
            }
            if (!Schema::hasColumn('members', 'qr_token')) {
                $table->string('qr_token')->nullable()->after('qr_code_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['qr_id', 'qr_code_path', 'qr_token']);
        });
    }
};