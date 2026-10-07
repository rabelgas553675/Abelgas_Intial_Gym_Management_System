<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
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

    public function printUserCard(User $user)
    {
        $qrRecord = $user->qrToken()->first();

        return view('qr.print-card', [
            'person' => $user,
            'user' => $user,
            'member' => null,
            'qrRecord' => $qrRecord,
            'items' => collect([$user]),
            'singlePrint' => true,
            'title' => 'QR Card',
        ]);
    }

    public function printMemberCard(Member $member)
    {
        return view('qr.print-card', [
            'person' => $member,
            'member' => $member,
            'user' => $member->user,
            'qrRecord' => null,
            'items' => collect([$member]),
            'singlePrint' => true,
            'title' => 'QR Card',
        ]);
    }

    /**
     * Handle the Print Card button.
     * Displays a dedicated view optimized for printing the membership card.
     */
    public function printCard(Member $member)
    {
        return $this->printMemberCard($member);
    }
}