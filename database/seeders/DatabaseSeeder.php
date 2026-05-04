<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ────────────────────────────────────────────────────────────
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@gym.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // ── Staff ────────────────────────────────────────────────────────────
        User::create([
            'name'     => 'Staff User',
            'email'    => 'staff@gym.com',
            'password' => Hash::make('password'),
            'role'     => 'staff',
        ]);

        // ── Instructors ──────────────────────────────────────────────────────
        $instructorData = [
            [
                'name'             => 'Rico Valdez',
                'email'            => 'rico@gym.com',
                'specialization'   => 'Strength & Conditioning',
                'experience_years' => 5,
                'gender'           => 'Male',
                'phone'            => '+63 912 100 0001',
            ],
            [
                'name'             => 'Carla Mendoza',
                'email'            => 'carla@gym.com',
                'specialization'   => 'Yoga & Flexibility',
                'experience_years' => 3,
                'gender'           => 'Female',
                'phone'            => '+63 917 100 0002',
            ],
        ];

        $instructors = [];
        foreach ($instructorData as $data) {
            $instructors[] = User::create([
                'name'             => $data['name'],
                'email'            => $data['email'],
                'password'         => Hash::make('password'),
                'role'             => 'instructor',
                'phone'            => $data['phone'],
                'gender'           => $data['gender'],
                'specialization'   => $data['specialization'],
                'experience_years' => $data['experience_years'],
            ]);
        }

        // ── Members ──────────────────────────────────────────────────────────
        // Each member gets a User account (portal login) + a Member record.
        // instructor_id links the member to one of the seeded instructors.
        $membersData = [
            [
                'first_name'      => 'Juan',
                'last_name'       => 'Dela Cruz',
                'email'           => 'juan@email.com',
                'phone'           => '+63 912 345 6789',
                'gender'          => 'Male',
                'birthdate'       => '1995-03-12',
                'membership_type' => 'Monthly',
                'fitness_plan'    => 'Weight Loss',
                'start_date'      => '2025-01-15',
                'instructor'      => 0, // index into $instructors
            ],
            [
                'first_name'      => 'Maria',
                'last_name'       => 'Santos',
                'email'           => 'maria@email.com',
                'phone'           => '+63 917 234 5678',
                'gender'          => 'Female',
                'birthdate'       => '1998-07-22',
                'membership_type' => 'Annually',
                'fitness_plan'    => 'General Fitness',
                'start_date'      => '2025-01-10',
                'instructor'      => 1,
            ],
            [
                'first_name'      => 'Carlos',
                'last_name'       => 'Bautista',
                'email'           => 'carlos@email.com',
                'phone'           => '+63 933 444 5555',
                'gender'          => 'Male',
                'birthdate'       => '1990-11-05',
                'membership_type' => 'Annually',
                'fitness_plan'    => 'Muscle Building',
                'start_date'      => '2024-12-01',
                'instructor'      => 0,
            ],
            [
                'first_name'      => 'Ana',
                'last_name'       => 'Reyes',
                'email'           => 'ana@email.com',
                'phone'           => '+63 921 111 2222',
                'gender'          => 'Female',
                'birthdate'       => '2000-05-18',
                'membership_type' => 'Monthly',
                'fitness_plan'    => 'Cardio & Endurance',
                'start_date'      => '2024-10-05',
                'instructor'      => null, // no coach
            ],
            [
                'first_name'      => 'Lisa',
                'last_name'       => 'Tan',
                'email'           => 'lisa@email.com',
                'phone'           => '+63 908 777 8888',
                'gender'          => 'Female',
                'birthdate'       => '1997-01-30',
                'membership_type' => 'Monthly',
                'fitness_plan'    => 'Flexibility & Core',
                'start_date'      => '2025-02-20',
                'instructor'      => 1,
            ],
        ];

        foreach ($membersData as $m) {
            $fullName  = $m['first_name'] . ' ' . $m['last_name'];
            $startDate = Carbon::parse($m['start_date']);

            // Compute end_date from membership_type (mirrors GreedyScheduler logic)
            $months = match($m['membership_type']) {
                'Monthly'     => 1,
                'Quarterly'   => 3,
                'Semi-Annual' => 6,
                'Annually'    => 12,
                default       => 1,
            };
            $endDate = $startDate->copy()->addMonths($months);

            // Fee from Payment constants
            $fee = match($m['membership_type']) {
                'Monthly'     => 800,
                'Quarterly'   => 2100,
                'Semi-Annual' => 4500,
                'Annually'    => 7500,
                default       => 800,
            };

            $instructorId = isset($m['instructor']) && $m['instructor'] !== null
                ? $instructors[$m['instructor']]->id
                : null;

            // 1. Create portal User account
            $user = User::create([
                'name'      => $fullName,
                'email'     => $m['email'],
                'password'  => Hash::make('password'),
                'role'      => 'member',
                'phone'     => $m['phone'],
                'gender'    => $m['gender'],
                'birthdate' => $m['birthdate'],
            ]);

            // 2. Create Member record linked to User
            Member::create([
                'user_id'         => $user->id,
                'instructor_id'   => $instructorId,
                'name'            => $fullName,
                'first_name'      => $m['first_name'],
                'last_name'       => $m['last_name'],
                'email'           => $m['email'],
                'phone'           => $m['phone'],
                'gender'          => $m['gender'],
                'birthdate'       => $m['birthdate'],
                'membership_type' => $m['membership_type'],
                'fitness_plan'    => $m['fitness_plan'],
                'start_date'      => $startDate,
                'end_date'        => $endDate,
                'fee'             => $fee,
                'coach_status'    => $instructorId ? 'approved' : 'none',
            ]);
        }

        // ── Dedicated test member account ────────────────────────────────────
        $testUser = User::create([
            'name'      => 'Test Member',
            'email'     => 'member@gym.com',
            'password'  => Hash::make('password'),
            'role'      => 'member',
            'phone'     => '+63 900 000 0001',
            'gender'    => 'Male',
            'birthdate' => '1995-01-01',
        ]);

        Member::create([
            'user_id'         => $testUser->id,
            'instructor_id'   => null,
            'name'            => 'Test Member',
            'first_name'      => 'Test',
            'last_name'       => 'Member',
            'email'           => 'member@gym.com',
            'phone'           => '+63 900 000 0001',
            'gender'          => 'Male',
            'birthdate'       => '1995-01-01',
            'membership_type' => 'Monthly',
            'fitness_plan'    => 'General Fitness',
            'start_date'      => Carbon::now(),
            'end_date'        => Carbon::now()->addMonth(),
            'fee'             => 800,
            'coach_status'    => 'none',
        ]);
    }
}