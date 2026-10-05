<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Walk-In / Day Pass payments live in their OWN table.
 * They are never linked to members, memberships or the `payments` table,
 * so they cannot activate, extend or appear in member payment history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('walk_in_payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->string('customer_name');
            $table->string('contact_number', 20)->nullable();
            $table->string('pass_type', 30)->default('Day Pass');
            $table->decimal('day_pass_rate', 10, 2);   // snapshot of the rate at the time of sale
            $table->decimal('amount', 10, 2);          // amount actually paid
            $table->string('method', 30)->default('Cash');
            $table->date('payment_date');
            $table->string('status', 20)->default('Paid'); // Paid | Partial
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('payment_date');
            $table->index('customer_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('walk_in_payments');
    }
};