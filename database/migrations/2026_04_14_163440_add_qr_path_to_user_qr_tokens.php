<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_qr_tokens', function (Blueprint $table) {
            // Check if column exists first to avoid "Duplicate column" errors
            if (!Schema::hasColumn('user_qr_tokens', 'qr_code_path')) {
                $table->string('qr_code_path')->nullable()->after('qr_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_qr_tokens', function (Blueprint $table) {
            $table->dropColumn('qr_code_path');
        });
    }
};