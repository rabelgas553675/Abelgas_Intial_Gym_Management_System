<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
 ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin'      => \App\Http\Middleware\AdminMiddleware::class,
        'instructor' => \App\Http\Middleware\InstructorMiddleware::class,
        'member'     => \App\Http\Middleware\MemberMiddleware::class,
        'coach.approved' => \App\Http\Middleware\CoachApproved::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();