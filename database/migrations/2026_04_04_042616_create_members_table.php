<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up(): void
{
    Schema::create('members', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
        $table->foreignId('instructor_id')->nullable()->constrained('users')->onDelete('set null');
        $table->string('name')->nullable();
        $table->string('first_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('email')->unique();
        $table->string('phone')->nullable();
        $table->string('gender')->nullable();
        $table->date('birthdate')->nullable();
        $table->text('address')->nullable();
        $table->string('membership_type')->nullable(); // Monthly, Quarterly, Annually
        $table->string('fitness_plan')->nullable();    // Calisthenics, Bodybuilding, etc.
        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable();
        $table->decimal('fee', 10, 2)->default(0);
        $table->enum('status', ['Active', 'Expired', 'Pending'])->default('Active');
        $table->string('photo')->nullable();
        $table->string('qr_code')->nullable();
        $table->timestamps();
    });
}

    public function down(): void {
        Schema::dropIfExists('members');
    }
};