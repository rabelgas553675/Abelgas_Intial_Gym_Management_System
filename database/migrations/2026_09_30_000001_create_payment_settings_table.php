<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });

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
            DB::table('payment_settings')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
