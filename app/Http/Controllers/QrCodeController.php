<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    /**
     * Handle the Reset/Regenerate button on the Member show page.
     * 
     * Note: The parameter name ($member) should match the placeholder 
     * in your route, e.g., Route::post('/qr/regenerate/{member}', ...)
     */
    public function regenerate(Member $member)
    {
        // This calls the static method we added to the Member model
        Member::generateQrCode($member);

        return back()->with('success', 'QR Code generated/reset successfully!');
    }

    /**
     * Handle the Print Card button.
     * Displays a dedicated view optimized for printing the membership card.
     */
    public function printCard(Member $member)
    {
        // Based on your snippets, returning the 'qr.print' view
        return view('qr.print-card', [
            'member' => $member,
            'user'   => $member // Included for compatibility if your view uses $user
        ]);
    }
}