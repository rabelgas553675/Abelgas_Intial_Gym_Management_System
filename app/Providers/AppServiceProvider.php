<?php

namespace App\Providers;

use App\Listeners\LogAuthEvents;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
        Event::listen(Login::class,   [LogAuthEvents::class, 'onLogin']);
        Event::listen(Logout::class,  [LogAuthEvents::class, 'onLogout']);
        Event::listen(Failed::class,  [LogAuthEvents::class, 'onFailed']);
        Event::listen(Lockout::class, [LogAuthEvents::class, 'onLockout']);
    }
}