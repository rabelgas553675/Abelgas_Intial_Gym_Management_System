<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY status VARCHAR(30) NOT NULL DEFAULT 'Paid'");
    }

    public function down(): void
    {
        // Left empty on purpose: narrowing back to the old enum would
        // fail or truncate rows holding 'Awaiting Coach' / 'Rejected'.
    }
};