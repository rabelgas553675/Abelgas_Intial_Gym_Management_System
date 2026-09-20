<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserQrToken;
use Illuminate\Console\Command;

class BackfillUserQrTokens extends Command
{
    protected $signature = 'users:backfill-qr';
    protected $description = 'Generate QR tokens for existing admin/staff/instructor users that are missing one';

    public function handle(): int
    {
        $users = User::whereIn('role', ['admin', 'staff', 'instructor'])
            ->whereDoesntHave('qrToken')
            ->get();

        $count = 0;
        foreach ($users as $user) {
            UserQrToken::createForUser($user);
            $count++;
            $this->info("Generated QR for: {$user->name} ({$user->role})");
        }

        $this->info("Done. {$count} QR token(s) created.");
        return self::SUCCESS;
    }
}