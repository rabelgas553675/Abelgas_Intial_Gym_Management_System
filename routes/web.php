<?php

use App\Http\Controllers\MemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberDashboardController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\WorkoutPlanController;
use Illuminate\Support\Facades\Route;

// Landing page (public)
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
});

Route::middleware(['auth'])->group(function () {

    // ── Smart redirect based on role ────────────────────────────
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;
        return match($role) {
            'member'     => redirect()->route('member.dashboard'),
            'instructor' => redirect()->route('instructor.dashboard'),
            'staff'      => redirect()->route('staff.dashboard'),
            default      => app(DashboardController::class)->index(),
        };
    })->name('dashboard');

    // ── Plans (public view) ─────────────────────────────────────
    Route::get('/plans', fn() => view('plans'))->name('plans');

    // ── MEMBER PORTAL ───────────────────────────────────────────
    Route::prefix('my')->name('member.')->middleware('member')->group(function () {
        Route::get('/dashboard',              [MemberDashboardController::class, 'index'])            ->name('dashboard');
        Route::get('/profile',                [MemberDashboardController::class, 'editProfile'])       ->name('profile');
        Route::post('/profile',               [MemberDashboardController::class, 'updateProfile'])     ->name('profile.update');
        Route::get('/select-plan',            [MemberDashboardController::class, 'selectPlan'])        ->name('select-plan');
        Route::post('/subscribe',             [MemberDashboardController::class, 'subscribePlan'])     ->name('subscribe');
        Route::get('/receipt/{payment}',      [MemberDashboardController::class, 'receipt'])           ->name('receipt');
        Route::get('/payments',               [MemberDashboardController::class, 'paymentHistory'])    ->name('payments'); 
        Route::post('/subscription/update',  [MemberDashboardController::class, 'updateSubscription'])->name('subscription.update');
        
        // Fixed: Moved inside group to resolve 'member.schedule' naming
        Route::get('/schedule', [WorkoutPlanController::class, 'memberSchedule'])->name('schedule');
    });

    // ── INSTRUCTOR PORTAL ───────────────────────────────────────
    Route::prefix('instructor')->name('instructor.')->middleware('instructor')->group(function () {
        Route::get('/dashboard',       [InstructorController::class, 'dashboard'])     ->name('dashboard');
        Route::get('/members/{member}', [InstructorController::class, 'showMember'])   ->name('member.show');
        Route::get('/profile',         [InstructorController::class, 'profile'])       ->name('profile');
        Route::post('/profile',        [InstructorController::class, 'updateProfile'])->name('profile.update');
        Route::get('/payments',        [InstructorController::class, 'payments'])      ->name('payments');
    });

    // ── STAFF PORTAL ─────────────────────────────────────────────
    Route::prefix('staff')->name('staff.')->middleware('auth')->group(function () {
        Route::get('/dashboard',  [StaffController::class, 'dashboard'])     ->name('dashboard');
        Route::get('/payments',   [StaffController::class, 'payments'])      ->name('payments');
        Route::get('/profile',    [StaffController::class, 'profile'])       ->name('profile');
        Route::post('/profile',   [StaffController::class, 'updateProfile']) ->name('profile.update');
    });

    // ── WORKOUT MANAGEMENT ───────────────────────────────────────
    Route::get('/workout',                  [WorkoutPlanController::class, 'index'])  ->name('workout.index');
    Route::post('/workout',                 [WorkoutPlanController::class, 'store'])  ->name('workout.store');
    Route::put('/workout/{workoutPlan}',   [WorkoutPlanController::class, 'update']) ->name('workout.update');
    Route::delete('/workout/{workoutPlan}',[WorkoutPlanController::class, 'destroy'])->name('workout.destroy');

    // ── ADMIN AREA ──────────────────────────────────────────────
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create',  [MemberController::class, 'create'])->name('members.create');
    Route::post('/members',        [MemberController::class, 'store'])  ->name('members.store');

    Route::middleware('admin')->group(function () {
        Route::get('/members/{member}/edit',    [MemberController::class, 'edit'])   ->name('members.edit');
        Route::put('/members/{member}',         [MemberController::class, 'update']) ->name('members.update');
        Route::delete('/members/{member}',      [MemberController::class, 'destroy'])->name('members.destroy');
        Route::get('/members/{member}/receipt', [MemberController::class, 'receipt'])->name('members.receipt');

        Route::get('/admin/profile',  [DashboardController::class, 'profile'])      ->name('admin.profile');
        Route::post('/admin/profile', [DashboardController::class, 'updateProfile']) ->name('admin.profile.update');

        Route::get('/payments',            [PaymentController::class, 'index'])  ->name('payments.index');
        Route::post('/payments',           [PaymentController::class, 'store'])  ->name('payments.store');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        Route::get('/users',                        [UserController::class, 'index'])          ->name('users.index');
        Route::post('/users',                       [UserController::class, 'store'])          ->name('users.store');
        Route::patch('/users/{user}/promote',        [UserController::class, 'promoteToAdmin'])->name('users.promote');
        Route::patch('/users/{user}/make-instructor', [UserController::class, 'makeInstructor'])->name('users.make-instructor');
        Route::delete('/users/{user}',               [UserController::class, 'destroy'])        ->name('users.destroy');
    });

    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');

});

require __DIR__.'/auth.php';