<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        // Create admin user
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@gym.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Create regular user
        User::create([
            'name'     => 'Regular User',
            'email'    => 'user@gym.com',
            'password' => Hash::make('password'),
            'role'     => 'user',
        ]);

        // Sample members
        $members = [
            ['name'=>'Juan Dela Cruz', 'email'=>'juan@email.com', 'phone'=>'+63 912 345 6789', 'membership_type'=>'Monthly', 'status'=>'Active', 'start_date'=>'2025-01-15'],
            ['name'=>'Maria Santos',   'email'=>'maria@email.com', 'phone'=>'+63 917 234 5678', 'membership_type'=>'Yearly',  'status'=>'Active', 'start_date'=>'2025-01-10'],
            ['name'=>'Carlos Bautista', 'email'=>'carlos@email.com', 'phone'=>'+63 933 444 5555', 'membership_type'=>'Yearly',  'status'=>'Active', 'start_date'=>'2024-12-01'],
            ['name'=>'Ana Reyes',       'email'=>'ana@email.com',   'phone'=>'+63 921 111 2222', 'membership_type'=>'Monthly', 'status'=>'Expired','start_date'=>'2024-10-05'],
            ['name'=>'Lisa Tan',        'email'=>'lisa@email.com',  'phone'=>'+63 908 777 8888', 'membership_type'=>'Monthly', 'status'=>'Active', 'start_date'=>'2025-02-20'],
        ];
        foreach ($members as $m) { Member::create($m); }
    }
}