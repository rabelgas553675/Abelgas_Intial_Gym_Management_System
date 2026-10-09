<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_earnings_summary AS
            SELECT 
                'admin' AS role,
                NULL AS user_id,
                SUM(CASE WHEN payment_type IN ('gym_fee', 'platform_fee') THEN amount ELSE 0 END) AS total_earned,
                SUM(CASE WHEN payment_type IN ('gym_fee', 'platform_fee') 
                    AND MONTH(payment_date) = MONTH(CURDATE()) 
                    AND YEAR(payment_date) = YEAR(CURDATE()) 
                    THEN amount ELSE 0 END) AS this_month
            FROM payments

            UNION ALL

            SELECT 
                'instructor' AS role,
                instructor_id AS user_id,
                SUM(amount) AS total_earned,
                SUM(CASE WHEN MONTH(payment_date) = MONTH(CURDATE()) 
                    AND YEAR(payment_date) = YEAR(CURDATE()) 
                    THEN amount ELSE 0 END) AS this_month
            FROM payments
            WHERE payment_type = 'coach_fee'
            AND instructor_id IS NOT NULL
            GROUP BY instructor_id
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_earnings_summary");
    }
};