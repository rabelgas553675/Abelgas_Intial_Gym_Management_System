<?php

namespace App\Listeners;

use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthEvents
{
    public function onLogin(Login $e): void
    {
        AuditLogger::log('login', 'Authentication', 'User logged in', null, null, null, $e->user->getAuthIdentifier());
    }

    public function onLogout(Logout $e): void
    {
        if ($e->user) {
            AuditLogger::log('logout', 'Authentication', 'User logged out', null, null, null, $e->user->getAuthIdentifier());
        }
    }

    public function onFailed(Failed $e): void
    {
        $email = $e->credentials['email'] ?? 'unknown';

        AuditLogger::log(
            'failed_login', 'Authentication', "Failed login attempt for {$email}",
            null, null, ['email' => $email],
            $e->user?->getAuthIdentifier(),
        );
    }

    public function onLockout(Lockout $e): void
    {
        $email = $e->request->input('email', 'unknown');

        AuditLogger::log('lockout', 'Authentication', "Too many login attempts for {$email}", null, null, ['email' => $email]);
    }
}