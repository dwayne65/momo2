<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MobileUser;
use App\Models\Payment;
use App\Models\Transfer;
use App\Models\Group;

class DashboardController extends Controller
{
    public function index()
    {
        $userCount = MobileUser::count();
        $paymentCount = Payment::count();
        $transferCount = Transfer::count();
        $groupCount = Group::count();

        $totalPayments = Payment::where('status', 'completed')->sum('amount');
        $totalTransfers = Transfer::where('status', 'completed')->sum('amount');

        $recentPayments = Payment::orderBy('created_at', 'desc')->limit(5)->get();
        $recentTransfers = Transfer::orderBy('created_at', 'desc')->limit(5)->get();

        return view('dashboard.index', compact(
            'userCount',
            'paymentCount',
            'transferCount',
            'groupCount',
            'totalPayments',
            'totalTransfers',
            'recentPayments',
            'recentTransfers'
        ));
    }
}
