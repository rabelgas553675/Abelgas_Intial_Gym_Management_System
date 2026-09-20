<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop the existing FK constraint first, then re-add it as nullable
            $table->dropForeign(['member_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('member_id')
                ->nullable()
                ->change();

            $table->foreign('member_id')
                ->references('id')->on('members')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('member_id')
                ->nullable(false)
                ->change();

            $table->foreign('member_id')
                ->references('id')->on('members')
                ->onDelete('cascade');
        });
    }
};