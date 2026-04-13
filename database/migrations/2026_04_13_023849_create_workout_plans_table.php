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
    Schema::create('workout_plans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('member_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('description')->nullable();
        $table->date('scheduled_date');
        $table->string('category')->nullable(); // Strength, Cardio, Flexibility, etc.
        $table->enum('intensity', ['Light', 'Moderate', 'Intense'])->default('Moderate');
        $table->text('exercises')->nullable(); // JSON string
        $table->boolean('is_completed')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_plans');
    }
};
