<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Member;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users        = User::latest()->get();
        $memberCount  = Member::count(); // total members in system
        return view('users.index', compact('users', 'memberCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,staff',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role'     => $request->role,
        ]);

        return back()->with('success', 'User added successfully!');
    }

    public function promoteToAdmin(User $user)
    {
        $user->update(['role' => 'admin']);
        return back()->with('success', "{$user->name} promoted to Admin.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}