<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::table('members', function (Blueprint $table) {
        $table->date('start_date')->nullable()->after('membership_type');
        $table->date('end_date')->nullable()->after('start_date');
        $table->decimal('fee', 10, 2)->default(0)->after('end_date');
        $table->enum('status', ['Active', 'Expired'])->default('Active')->after('fee');
    });
}

    public function down(): void {
        Schema::dropIfExists('members');
    }
};