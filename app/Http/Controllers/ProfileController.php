<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the admin's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /**
     * Update the admin's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'gender'    => 'nullable|in:Male,Female,Other',
            'address'   => 'nullable|string|max:500',
            'photo'     => 'nullable|image|max:3072',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('profiles', 'public');
        }

        $user->name      = $request->name;
        $user->phone     = $request->phone;
        $user->birthdate = $request->birthdate;
        $user->gender    = $request->gender;
        $user->address   = $request->address;

        // Reset email verification if email changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('admin.profile')->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}