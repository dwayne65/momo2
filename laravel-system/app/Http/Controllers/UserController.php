<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MobileUser;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function index()
    {
        $users = MobileUser::orderBy('created_at', 'desc')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $request->phone;

        // Check if user already exists
        $existingUser = MobileUser::where('phone', $phone)->first();
        if ($existingUser) {
            return view('users.verify', compact('existingUser'));
        }

        // Call MOPAY API to verify user
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('MOPAY_API_TOKEN'),
                'Content-Type' => 'application/json',
            ])->get(env('MOPAY_API_BASE') . '/customer-info?phone=' . urlencode($phone));

            if ($response->successful() && $response->json()) {
                $userData = $response->json();

                // Save user to database
                $user = MobileUser::create([
                    'first_name' => $userData['firstName'],
                    'last_name' => $userData['lastName'],
                    'birth_date' => $userData['birthDate'],
                    'gender' => $userData['gender'],
                    'is_active' => $userData['isActive'],
                    'phone' => $phone,
                ]);

                return view('users.verify', compact('user'));
            } else {
                return back()->withErrors(['error' => 'Failed to verify user. Please check the phone number.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'API error: ' . $e->getMessage()]);
        }
    }

    public function showVerifyForm()
    {
        return view('users.verify');
    }
}
