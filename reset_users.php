<?php
/*
 * RESET USERS + THEIR DATA
 *
 * Keeps exactly 4 accounts (password = "password"):
 *   admin@gym.com, staff@gym.com, instructor@gym.com, member@gym.com (Test Member)
 * Deletes every other user and every other member, with all their
 * payments, coach fees, attendance, workout plans, coach requests,
 * notifications, sessions, photos and QR files.
 *
 * Run from the project folder:
 *   php artisan tinker reset_users.php
 *
 * Set $dryRun = true to only preview (nothing is changed).
 */

$dryRun   = false;
$password = 'password';

$accounts = [
    ['role' => 'admin',      'name' => 'Admin User',  'email' => 'admin@gym.com'],
    ['role' => 'staff',      'name' => 'Staff User',  'email' => 'staff@gym.com'],
    ['role' => 'instructor', 'name' => 'Instructor',  'email' => 'instructor@gym.com'],
    ['role' => 'member',     'name' => 'Test Member', 'email' => 'member@gym.com'],
];
$keepEmails = array_column($accounts, 'email');
$history    = ['payments', 'instructor_fees', 'attendances', 'workout_plans', 'coach_requests'];

// 0. Sanity: a kept email must not already belong to a different role
foreach ($accounts as $a) {
    $u = \App\Models\User::where('email', $a['email'])->first();
    if ($u && $u->role !== $a['role']) {
        throw new \Exception("{$a['email']} exists with role '{$u->role}', expected '{$a['role']}'. Nothing changed.");
    }
}

// 1. Work out what will be removed
$existing    = \App\Models\User::whereIn('email', $keepEmails)->get();
$keepUserIds = $existing->pluck('id');
$testUser    = $existing->firstWhere('email', 'member@gym.com');
$keepMember  = $testUser ? \App\Models\Member::where('user_id', $testUser->id)->first() : null;

$userIds   = \App\Models\User::whereNotIn('id', $keepUserIds)->pluck('id');
$memberIds = \App\Models\Member::when($keepMember, fn ($q) => $q->where('id', '!=', $keepMember->id))->pluck('id');

echo "Accounts to create: " . (collect($keepEmails)->diff($existing->pluck('email'))->implode(', ') ?: 'none') . PHP_EOL;
echo "Users to delete:    {$userIds->count()}" . PHP_EOL;
echo "Members to delete:  {$memberIds->count()}" . PHP_EOL;
foreach ($history as $t) {
    echo str_pad($t, 18) . \Illuminate\Support\Facades\DB::table($t)->whereIn('member_id', $memberIds)->count() . PHP_EOL;
}

if ($dryRun) {
    echo PHP_EOL . "DRY RUN - nothing changed." . PHP_EOL;
    return;
}

// 2. Collect files to remove once the DB work has succeeded
$files = collect()
    ->merge(\App\Models\Member::whereIn('id', $memberIds)->get(['photo', 'qr_code_path'])
        ->flatMap(fn ($m) => [$m->photo, $m->qr_code_path]))
    ->merge(\App\Models\User::whereIn('id', $userIds)->pluck('photo'))
    ->merge(\Illuminate\Support\Facades\DB::table('user_qr_tokens')->whereIn('user_id', $userIds)->pluck('qr_code_path'))
    ->filter()->unique()->values()->all();

// 3. Delete everyone else and all their data (one transaction)
\Illuminate\Support\Facades\DB::transaction(function () use ($memberIds, $userIds, $history) {
    foreach ($history as $t) {
        \Illuminate\Support\Facades\DB::table($t)->whereIn('member_id', $memberIds)->delete();
    }
    \Illuminate\Support\Facades\DB::table('members')->whereIn('id', $memberIds)->delete();
    \Illuminate\Support\Facades\DB::table('notifications')
        ->where('notifiable_type', \App\Models\User::class)
        ->whereIn('notifiable_id', $userIds)->delete();
    \Illuminate\Support\Facades\DB::table('sessions')->whereIn('user_id', $userIds)->delete();
    \Illuminate\Support\Facades\DB::table('users')->whereIn('id', $userIds)->delete();
});
\Illuminate\Support\Facades\Storage::disk('public')->delete($files);

// 4. Make sure the 4 accounts exist, all with the same password
foreach ($accounts as $a) {
    $u = \App\Models\User::where('email', $a['email'])->first();

    if (!$u) {
        $u = \App\Models\User::create($a + ['password' => $password]);
        if (in_array($a['role'], ['admin', 'staff', 'instructor'])) {
            \App\Models\UserQrToken::createForUser($u);
        }
    } else {
        $u->password = $password;      // hashed by the User model cast
        $u->save();
    }

    if ($a['role'] === 'member' && !\App\Models\Member::where('user_id', $u->id)->exists()) {
        $m = \App\Models\Member::create([
            'user_id' => $u->id, 'name' => $u->name,
            'first_name' => 'Test', 'last_name' => 'Member',
            'email' => $u->email, 'gender' => 'Male', 'birthdate' => '2000-01-01',
            'membership_type' => null, 'start_date' => null, 'end_date' => null,
            'fee' => 0, 'status' => 'Pending', 'instructor_id' => null, 'coach_status' => 'none',
        ]);
        \App\Models\Member::generateQrCode($m);
    }
}

// 5. Result
echo PHP_EOL . "DONE." . PHP_EOL;
foreach (\App\Models\User::orderBy('id')->get(['email', 'role']) as $u) {
    echo "  {$u->role}: {$u->email}" . PHP_EOL;
}
echo "Members: " . \App\Models\Member::pluck('name')->implode(', ') . PHP_EOL;
