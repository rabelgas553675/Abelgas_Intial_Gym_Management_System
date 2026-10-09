<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use App\Models\UserQrToken;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * GymDemoUsersSeeder
 *
 * Creates (or aligns) exactly the eight designated demo accounts:
 *   1 admin, 1 staff, 2 instructors, 4 members.
 *
 * This seeder ONLY creates / updates those eight accounts. It never deletes
 * anything. Removing every other account is the job of the guarded
 * `php artisan gym:reset-users` command, which calls this seeder afterwards.
 *
 * Idempotent: users are matched by e-mail, member profiles by user_id, so
 * running it repeatedly never creates duplicates.
 *
 *   php artisan db:seed --class=Database\\Seeders\\GymDemoUsersSeeder
 *
 * Shapes follow what the application itself produces:
 *   - roles are the exact strings the app checks: admin|staff|instructor|member
 *   - admin / staff / instructor get a UserQrToken (UserController::store)
 *   - members get a Member row shaped like MemberController::store() does for a
 *     brand-new account (no plan, fee 0, Pending, no coach) plus a member QR code.
 *   - NO payments, attendance, coach requests or audit rows are fabricated.
 */
class GymDemoUsersSeeder extends Seeder
{
    public const PASSWORD = 'password';

    /** Single source of truth — also used by the reset command. Order = display order. */
    public const ACCOUNTS = [
        [
            'name' => 'System Administrator', 'email' => 'admin@gym.com', 'role' => 'admin',
        ],
        [
            'name' => 'Gym Staff', 'email' => 'staff@gym.com', 'role' => 'staff',
        ],
        [
            'name' => 'Rico Valdez', 'email' => 'rico@gym.com', 'role' => 'instructor',
            'phone' => '+63 912 100 0001', 'gender' => 'Male',
            'specialization' => 'Strength & Conditioning', 'experience_years' => 5,
        ],
        [
            'name' => 'Carla Mendoza', 'email' => 'carla@gym.com', 'role' => 'instructor',
            'phone' => '+63 917 100 0002', 'gender' => 'Female',
            'specialization' => 'Yoga & Flexibility', 'experience_years' => 3,
        ],
        [
            'name' => 'Ana Reyes', 'email' => 'ana@gym.com', 'role' => 'member',
            'first_name' => 'Ana', 'last_name' => 'Reyes',
            'phone' => '+63 921 111 2222', 'gender' => 'Female', 'birthdate' => '2000-05-18',
        ],
        [
            'name' => 'Carlos Bautista', 'email' => 'carlos@gym.com', 'role' => 'member',
            'first_name' => 'Carlos', 'last_name' => 'Bautista',
            'phone' => '+63 933 444 5555', 'gender' => 'Male', 'birthdate' => '1990-11-05',
        ],
        [
            'name' => 'Lisa Tan', 'email' => 'lisa@gym.com', 'role' => 'member',
            'first_name' => 'Lisa', 'last_name' => 'Tan',
            'phone' => '+63 908 777 8888', 'gender' => 'Female', 'birthdate' => '1997-01-30',
        ],
        [
            'name' => 'Maria Santos', 'email' => 'maria@gym.com', 'role' => 'member',
            'first_name' => 'Maria', 'last_name' => 'Santos',
            'phone' => '+63 917 234 5678', 'gender' => 'Female', 'birthdate' => '1998-07-22',
        ],
    ];

    /** Roles that scan in with a UserQrToken (matches user_qr_tokens.role enum). */
    private const TOKEN_ROLES = ['admin', 'staff', 'instructor'];

    /** @return string[] lower-cased designated e-mails */
    public static function emails(): array
    {
        return array_map(fn ($a) => strtolower($a['email']), self::ACCOUNTS);
    }

    /** @return string[] lower-cased e-mails of the designated MEMBER accounts */
    public static function memberEmails(): array
    {
        return array_values(array_map(
            fn ($a) => strtolower($a['email']),
            array_filter(self::ACCOUNTS, fn ($a) => $a['role'] === 'member')
        ));
    }

    public function run(): void
    {
        DB::transaction(function () {
            foreach (self::ACCOUNTS as $account) {
                $user = $this->upsertUser($account);

                if (in_array($user->role, self::TOKEN_ROLES, true)) {
                    $this->ensureStaffQrToken($user);
                }

                if ($user->role === 'member') {
                    $this->upsertMemberProfile($user, $account);
                }
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────

    private function upsertUser(array $a): User
    {
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($a['email'])], 'and')->first() ?? new User();

        $user->fill(array_filter([
            'name'             => $a['name'],
            'email'            => $a['email'],
            'role'             => $a['role'],
            'phone'            => $a['phone'] ?? null,
            'gender'           => $a['gender'] ?? null,
            'birthdate'        => $a['birthdate'] ?? null,
            'specialization'   => $a['specialization'] ?? null,
            'experience_years' => $a['experience_years'] ?? null,
        ], fn ($v) => $v !== null));

        // Always leave the documented default password in place (hashed).
        // The 'hashed' cast leaves an already-hashed value untouched.
        if (! $user->exists || ! Hash::check(self::PASSWORD, (string) $user->password)) {
            $user->password = Hash::make(self::PASSWORD);
        }

        $user->save();

        return $user;
    }

    private function ensureStaffQrToken(User $user): void
    {
        $existing = UserQrToken::where('user_id', '=', $user->id, 'and')->first();

        // createForUser() rotates the token, so only call it when needed.
        if (! $existing || $existing->role !== $user->role || ! $existing->qr_token) {
            UserQrToken::createForUser($user);
        }
    }

    private function upsertMemberProfile(User $user, array $a): void
    {
        $member = Member::where('user_id', '=', $user->id, 'and')->first();

        if (! $member) {
            // Adopt an orphan profile with the same e-mail, but never steal one
            // that belongs to a different account.
            $byEmail = Member::whereRaw('LOWER(email) = ?', [strtolower($a['email'])], 'and')->first();

            if ($byEmail && $byEmail->user_id !== null && (int) $byEmail->user_id !== (int) $user->id) {
                throw new RuntimeException(
                    "Member profile #{$byEmail->id} already uses {$a['email']} but belongs to user #{$byEmail->user_id}. "
                    . 'Resolve it (or run gym:reset-users) before seeding.'
                );
            }

            $member = $byEmail ?? new Member();
            $isNew  = ! $member->exists;
        } else {
            $isNew = false;
        }

        // Identity fields are always aligned…
        $member->fill([
            'user_id'    => $user->id,
            'name'       => $a['name'],
            'first_name' => $a['first_name'],
            'last_name'  => $a['last_name'],
            'email'      => $a['email'],
            'phone'      => $a['phone'] ?? null,
            'gender'     => $a['gender'] ?? null,
            'birthdate'  => $a['birthdate'] ?? null,
        ]);

        // …but plan / fee / coach data is only initialised for a NEW profile, so
        // re-running the seeder never overwrites real membership or payment state.
        if ($isNew) {
            $member->fill([
                'membership_type' => null,
                'start_date'      => null,
                'end_date'        => null,
                'fee'             => 0,
                'status'          => 'Pending',
                'instructor_id'   => null,
                'coach_status'    => 'none',
            ]);
        }

        $member->save();

        if (! $member->qr_token || ! $member->qr_id) {
            Member::generateQrCode($member);
        }
    }
}