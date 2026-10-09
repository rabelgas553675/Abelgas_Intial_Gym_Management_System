<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        return view('landing');
    }

    /**
     * Handle an incoming registration request.
     *
     * Public registration always creates a member account.
     * Admin / Staff / Instructor accounts must be created by an Admin
     * from inside the admin panel — never via this public endpoint.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email:rfc', 'max:255', 'unique:' . User::class, 'unique:members,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request) {
            // Role is ALWAYS member for public registrations — never trust UI input
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'member',   // hardcoded — no role field from request
            ]);

            // Every member account needs a profile row, otherwise they never appear
            // on the Members page and the Select Plan page has nothing to work with.
            [$first, $last] = array_pad(explode(' ', trim($user->name), 2), 2, null);

            $member = Member::create([
                'user_id'         => $user->id,
                'name'            => $user->name,
                'first_name'      => $first,
                'last_name'       => $last,
                'email'           => $user->email,
                'membership_type' => null,
                'start_date'      => null,
                'end_date'        => null,
                'fee'             => 0,
                'status'          => 'Pending',
                'instructor_id'   => null,
                'coach_status'    => 'none',
            ]);

            Member::generateQrCode($member);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        // Members always go to the member dashboard
        return redirect()->route('member.dashboard');
    }
}