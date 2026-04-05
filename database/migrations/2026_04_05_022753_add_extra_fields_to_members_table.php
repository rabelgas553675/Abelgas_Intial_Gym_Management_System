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
    Schema::table('members', function (Blueprint $table) {
        $table->string('first_name')->nullable()->after('id');
        $table->string('last_name')->nullable()->after('first_name');
        $table->enum('gender', ['Male','Female','Other'])->nullable()->after('phone');
        $table->date('birthdate')->nullable()->after('gender');
        $table->text('address')->nullable()->after('birthdate');
        $table->string('photo')->nullable()->after('address');
        $table->decimal('fee', 10, 2)->default(0)->after('end_date');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            //
        });
    }
};
