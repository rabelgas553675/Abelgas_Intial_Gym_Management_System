<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('member_id')->constrained()->onDelete('cascade');
        $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
        $table->string('receipt_number')->unique();
        $table->string('fitness_plan')->nullable();
        $table->string('membership_type')->nullable();
        $table->decimal('amount', 10, 2);
        $table->date('payment_date');
        $table->string('method')->default('Cash');
        $table->enum('status', ['Paid', 'Pending', 'Expired'])->default('Paid');
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
