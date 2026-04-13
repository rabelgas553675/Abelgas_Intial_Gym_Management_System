<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email:rfc', 'max:255', 'unique:'.User::class],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'role'          => ['required', 'in:admin,staff,instructor,member'],
            'instructor_id' => ['nullable', 'exists:users,id'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        // If registering as member and selected an instructor,
        // create their member profile immediately with the instructor assigned
        if ($request->role === 'member' && $request->instructor_id) {
            \App\Models\Member::create([
                'user_id'       => $user->id,
                'instructor_id' => $request->instructor_id,
                'name'          => $user->name,
                'email'         => $user->email,
                'status'        => 'Pending',
            ]);
        }

        event(new Registered($user));
        Auth::login($user);

        return match($user->role) {
            'member'     => redirect()->route('member.dashboard'),
            'instructor' => redirect()->route('instructor.dashboard'),
            default      => redirect()->route('dashboard'),
        };
    }
}