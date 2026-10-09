<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use App\Models\UserQrToken;

class QrCodeController extends Controller
{
    /**
     * Regenerate a MEMBER's QR code.
     * Route: POST /members/{member}/regenerate-qr  (members.qr.regenerate)
     */
    public function regenerate(Member $member)
    {
        Member::generateQrCode($member);

        return back()->with('success', 'QR Code generated/reset successfully!');
    }

    /**
     * Regenerate a STAFF / INSTRUCTOR / ADMIN QR code.
     * Route: POST /users/{user}/qr/regenerate  (users.qr.regenerate)
     */
    public function regenerateUser(User $user)
    {
        UserQrToken::createForUser($user);

        return back()->with('success', 'QR Code generated/reset successfully!');
    }

    /**
     * Print card for a staff / instructor / admin user.
     * Route: GET /users/{user}/qr/print  (users.qr.print)
     */
    public function printUserCard(User $user)
    {
        $qrRecord = $user->qrToken()->first();

        return view('qr.print-card', [
            'person'      => $user,
            'user'        => $user,
            'member'      => null,
            'qrRecord'    => $qrRecord,
            'items'       => collect([$user]),
            'singlePrint' => true,
            'title'       => 'QR Card',
        ]);
    }

    /**
     * Print card for a member.
     * Route: GET /members/{member}/print-card  (members.qr.print)
     */
    public function printMemberCard(Member $member)
    {
        return view('qr.print-card', [
            'person'      => $member,
            'member'      => $member,
            'user'        => $member->user,
            'qrRecord'    => null,
            'items'       => collect([$member]),
            'singlePrint' => true,
            'title'       => 'QR Card',
        ]);
    }

    /**
     * Kept for the existing members.qr.print route.
     */
    public function printCard(Member $member)
    {
        return $this->printMemberCard($member);
    }
}