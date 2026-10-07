<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Throwable;

/**
 * Change password for the CURRENTLY AUTHENTICATED user.
 *
 * One endpoint (PUT /password, name: password.update) serves every role
 * (Admin, Staff, Instructor, Member). It never accepts a user id, so a user can
 * only ever change their own password.
 */
class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(8)->letters()->numbers(),
            ],
        ], [
            'current_password.required'         => 'The current password is required.',
            'current_password.current_password' => 'The current password is incorrect.',
            'password.required'                 => 'The new password field is required.',
            'password.confirmed'                => 'The new password confirmation does not match.',
            'password.different'                => 'The new password must be different from your current password.',
            'password.min'                      => 'The new password must be at least 8 characters.',
            'password.letters'                  => 'The new password must contain at least one letter.',
            'password.numbers'                  => 'The new password must contain at least one number.',
        ]);

        try {
            // Only the authenticated user is ever updated.
            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);
        } catch (Throwable $e) {
            // Never log request data / passwords — only who and what kind of failure.
            Log::error('Password update failed', [
                'user_id'   => $request->user()?->id,
                'exception' => get_class($e),
            ]);

            return back()->with('password_error', 'Something went wrong while changing your password. Please try again.');
        }

        return back()
            ->with('status', 'password-updated') // kept for the stock Breeze form
            ->with('password_success', 'Your password has been changed successfully.');
    }
}
