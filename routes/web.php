<?php

use App\Http\Controllers\MemberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberDashboardController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\WorkoutPlanController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\CoachRequestController;
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

    // ── QR CODE MANAGEMENT ──────────────────────────────────────
    Route::get('/users/{user}/qr/print',       [QrCodeController::class, 'printCard'])->name('qr.print');
    Route::post('/users/{user}/qr/regenerate', [QrCodeController::class, 'regenerate'])->name('qr.regenerate');

    Route::post('/members/{member}/regenerate-qr', [QrCodeController::class, 'regenerate'])->name('members.qr.regenerate');
    Route::get('/members/{member}/print-card',     [QrCodeController::class, 'printCard'])->name('members.qr.print');

    // ── MEMBER PORTAL ───────────────────────────────────────────
    Route::prefix('my')->name('member.')->middleware('member')->group(function () {
        Route::get('/dashboard',              [MemberDashboardController::class, 'index'])            ->name('dashboard');
        Route::get('/profile',                [MemberDashboardController::class, 'editProfile'])       ->name('profile');
        Route::post('/profile',               [MemberDashboardController::class, 'updateProfile'])     ->name('profile.update');
        Route::get('/select-plan',            [MemberDashboardController::class, 'selectPlan'])        ->name('select-plan');
        Route::post('/subscribe',             [MemberDashboardController::class, 'subscribePlan'])     ->name('subscribe');
        Route::get('/receipt/{payment}',      [MemberDashboardController::class, 'receipt'])           ->name('receipt');
        Route::get('/payments',               [MemberDashboardController::class, 'paymentHistory'])    ->name('payments');
        Route::post('/subscription/update',   [MemberDashboardController::class, 'updateSubscription'])->name('subscription.update');
        Route::get('/schedule',               [WorkoutPlanController::class, 'memberSchedule'])        ->name('schedule');
    });

    // ── INSTRUCTOR PORTAL ───────────────────────────────────────
    Route::prefix('instructor')->name('instructor.')->middleware('instructor')->group(function () {
        Route::get('/dashboard',        [InstructorController::class, 'dashboard'])       ->name('dashboard');
        Route::get('/members/{member}', [InstructorController::class, 'showMember'])      ->name('member.show');
        Route::get('/profile',          [InstructorController::class, 'profile'])          ->name('profile');
        Route::post('/profile',         [InstructorController::class, 'updateProfile'])    ->name('profile.update');
        Route::get('/payments',         [InstructorController::class, 'paymentHistory'])   ->name('payments');

        // ── Coach Requests ──────────────────────────────────────
        Route::get('/requests',                         [CoachRequestController::class, 'index'])  ->name('requests');
        Route::post('/requests/{coachRequest}/approve', [CoachRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{coachRequest}/reject',  [CoachRequestController::class, 'reject']) ->name('requests.reject');
    });

    // ── STAFF PORTAL ─────────────────────────────────────────────
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard',  [StaffController::class, 'dashboard'])     ->name('dashboard');
        Route::get('/payments',   [StaffController::class, 'payments'])      ->name('payments');
        Route::get('/profile',    [StaffController::class, 'profile'])       ->name('profile');
        Route::post('/profile',   [StaffController::class, 'updateProfile']) ->name('profile.update');
    });

    // ── WORKOUT MANAGEMENT ───────────────────────────────────────
    Route::get('/workout',                  [WorkoutPlanController::class, 'index'])  ->name('workout.index');
    Route::post('/workout',                 [WorkoutPlanController::class, 'store'])  ->name('workout.store');
    Route::put('/workout/{workoutPlan}',    [WorkoutPlanController::class, 'update']) ->name('workout.update');
    Route::delete('/workout/{workoutPlan}', [WorkoutPlanController::class, 'destroy'])->name('workout.destroy');

    // ── ATTENDANCE MANAGEMENT ────────────────────────────────────
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/scan',            [AttendanceController::class, 'scan'])         ->name('scan');
        Route::post('/scan/process',   [AttendanceController::class, 'processQr'])    ->name('scan.process');
        Route::post('/manual',         [AttendanceController::class, 'manualEntry'])  ->name('manual');
        Route::get('/',                [AttendanceController::class, 'index'])         ->name('index');
        Route::post('/timeout',        [AttendanceController::class, 'timeOut'])       ->name('timeout');
        Route::post('/destroy',        [AttendanceController::class, 'destroy'])       ->name('destroy');
        Route::post('/add-manual',     [AttendanceController::class, 'addManual'])     ->name('add-manual');
        Route::get('/qr-list',         [AttendanceController::class, 'qrList'])        ->name('qr-list');

        Route::get('/generate-tokens', [AttendanceController::class, 'generateTokens'])
             ->middleware('admin')
             ->name('generate-tokens');
    });

    // ── MEMBER MANAGEMENT (Auth Only - Staff & Admin) ───────────
    Route::get('/members',                [MemberController::class, 'index'])  ->name('members.index');
    Route::get('/members/create',         [MemberController::class, 'create']) ->name('members.create');
    Route::post('/members',               [MemberController::class, 'store'])  ->name('members.store');
    Route::get('/members/{member}',       [MemberController::class, 'show'])   ->name('members.show');
    Route::get('/members/{member}/edit',  [MemberController::class, 'edit'])   ->name('members.edit');
    Route::put('/members/{member}',       [MemberController::class, 'update']) ->name('members.update');
    Route::delete('/members/{member}',    [MemberController::class, 'destroy'])->name('members.destroy');

    // ── USER PROFILE (shared — any authenticated user can view) ─
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

    // ── ADMIN AREA (Admin Only) ─────────────────────────────────
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/profile',   [DashboardController::class, 'profile'])->name('admin.profile');
        Route::post('/admin/profile',  [DashboardController::class, 'updateProfile'])->name('admin.profile.update');

        // Sensitive Member/Payment actions
        Route::get('/members/{member}/receipt', [MemberController::class, 'receipt'])->name('members.receipt');

        // Admin Payment Routes
        Route::get('/payments',                 [PaymentController::class, 'index'])  ->name('payments.index');
        Route::post('/payments',                [PaymentController::class, 'store'])  ->name('payments.store');
        Route::delete('/payments/{payment}',    [PaymentController::class, 'destroy'])->name('payments.destroy');

        // User Management
        Route::get('/users',                          [UserController::class, 'index'])         ->name('users.index');
        Route::post('/users',                         [UserController::class, 'store'])         ->name('users.store');
        Route::patch('/users/{user}/promote',         [UserController::class, 'promoteToAdmin'])->name('users.promote');
        Route::patch('/users/{user}/make-instructor', [UserController::class, 'makeInstructor'])->name('users.make-instructor');
        Route::delete('/users/{user}',                [UserController::class, 'destroy'])       ->name('users.destroy');
    });

});

require __DIR__.'/auth.php';