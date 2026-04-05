<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total'   => Member::count(),
            'active'  => Member::where('status', 'Active')->count(),
            'monthly' => Member::where('membership_type', 'Monthly')->count(),
            'yearly'  => Member::where('membership_type', 'Yearly')->count(),
        ];

        $recent = Member::latest()->take(5)->get();

        $thisMonth = Payment::whereMonth('payment_date', now()->month)
                            ->whereYear('payment_date', now()->year)
                            ->sum('amount');

        $totalCollected = Payment::sum('amount');

        return view('dashboard', compact(
            'stats',
            'recent',
            'thisMonth',
            'totalCollected'
        ));
    }
}