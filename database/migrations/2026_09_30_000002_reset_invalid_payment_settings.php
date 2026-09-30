<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaults = [
            'gym_Monthly' => '800',
            'gym_Quarterly' => '2100',
            'gym_Semi-Annual' => '4500',
            'gym_Annually' => '7500',
            'coach_Monthly' => '300',
            'coach_Quarterly' => '1200',
            'coach_Semi-Annual' => '1800',
            'coach_Annually' => '3600',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('payment_settings')
                ->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
        }

        DB::table('payment_settings')
            ->whereIn('key', array_keys($defaults))
            ->where(function ($query) {
                $query->where('value', '')
                    ->orWhereRaw('CAST(value AS UNSIGNED) <= 0');
            })
            ->update(['value' => DB::raw("CASE `key`
                WHEN 'gym_Monthly' THEN '800'
                WHEN 'gym_Quarterly' THEN '2100'
                WHEN 'gym_Semi-Annual' THEN '4500'
                WHEN 'gym_Annually' THEN '7500'
                WHEN 'coach_Monthly' THEN '300'
                WHEN 'coach_Quarterly' THEN '1200'
                WHEN 'coach_Semi-Annual' THEN '1800'
                WHEN 'coach_Annually' THEN '3600'
                ELSE value
            END")]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank; this data reset is not destructive beyond restoring defaults.
    }
};
