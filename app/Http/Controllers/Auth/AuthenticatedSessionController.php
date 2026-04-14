<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('landing');
    }

    /**
     * Handle an incoming authentication request.
     *
     * Role is determined entirely from the database after authentication.
     * No role input is accepted from the UI.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $role = Auth::user()->role;

        // Role-based redirect — determined from backend, never from UI input
        return match($role) {
            'member'     => redirect()->intended(route('member.dashboard')),
            'instructor' => redirect()->intended(route('instructor.dashboard')),
            'admin'      => redirect()->intended(route('admin.dashboard')),
            'staff'      => redirect()->intended(route('dashboard')),
            default      => redirect()->intended(route('dashboard')),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}