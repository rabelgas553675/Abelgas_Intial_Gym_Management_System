<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables whose member_id must NEVER cascade-delete when a member is removed.
     * With RESTRICT the database itself refuses to delete a member who still has
     * payments, coach fees, attendance, workout plans or coach requests.
     */
    private array $tables = [
        'payments',
        'attendances',
        'instructor_fees',
        'workout_plans',
        'coach_requests',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            $this->recreateMemberForeignKey($table, restrict: true);
        }
    }

    public function down(): void
    {
        // Restore the previous (cascading) behaviour.
        foreach ($this->tables as $table) {
            $this->recreateMemberForeignKey($table, restrict: false);
        }
    }

    private function recreateMemberForeignKey(string $tableName, bool $restrict): void
    {
        // Older / partial schemas may not have every table or column — skip safely.
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'member_id')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Find the existing member_id → members FK by column (don't guess its name).
        $existing = collect(Schema::getForeignKeys($tableName))->first(
            fn (array $fk) => $fk['columns'] === ['member_id'] && $fk['foreign_table'] === 'members'
        );

        Schema::table($tableName, function (Blueprint $table) use ($existing, $restrict) {
            if ($existing) {
                $table->dropForeign($existing['name']);
            }

            $foreign = $table->foreign('member_id')->references('id')->on('members');

            $restrict ? $foreign->restrictOnDelete() : $foreign->cascadeOnDelete();
        });
    }
};