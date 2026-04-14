<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add qr_token to members table
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'qr_token')) {
                $table->string('qr_token', 64)->nullable()->unique()->after('photo')
                      ->comment('Unique token encoded in member QR code');
            }
        });

        // Create user_qr_tokens table for admin/staff/instructor
        if (!Schema::hasTable('user_qr_tokens')) {
            Schema::create('user_qr_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->enum('role', ['admin', 'staff', 'instructor']);
                $table->string('name');
                $table->string('qr_token', 64)->unique();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Create attendance table if it doesn't exist
        if (!Schema::hasTable('attendance')) {
            Schema::create('attendance', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable();
                $table->dateTime('time_in');
                $table->dateTime('time_out')->nullable();
                $table->date('date');
                $table->integer('duration_minutes')->nullable();
                $table->string('scanned_by', 64)->nullable()->comment('Who processed this scan');
                $table->enum('entry_method', ['qr_scan', 'manual'])->default('qr_scan');
                $table->timestamps();

                $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');
            });
        } else {
            // Add missing columns to existing attendance table
            Schema::table('attendance', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance', 'scanned_by')) {
                    $table->string('scanned_by', 64)->nullable()->after('duration_minutes');
                }
                if (!Schema::hasColumn('attendance', 'entry_method')) {
                    $table->enum('entry_method', ['qr_scan', 'manual'])->default('qr_scan')->after('scanned_by');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
        Schema::dropIfExists('user_qr_tokens');
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn(['scanned_by', 'entry_method']);
        });
    }
};