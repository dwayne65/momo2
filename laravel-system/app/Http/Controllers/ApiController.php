<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MobileUser;
use App\Models\Payment;
use App\Models\Transfer;
use App\Models\Group;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function users(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'phone' => 'required|string',
            ]);

            $existingUser = MobileUser::where('phone', $request->phone)->first();
            if ($existingUser) {
                return response()->json($existingUser);
            }

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('MOPAY_API_TOKEN'),
                ])->get(env('MOPAY_API_BASE') . '/customer-info?phone=' . urlencode($request->phone));

                if ($response->successful() && $response->json()) {
                    $userData = $response->json();

                    $user = MobileUser::create([
                        'first_name' => $userData['firstName'],
                        'last_name' => $userData['lastName'],
                        'birth_date' => $userData['birthDate'],
                        'gender' => $userData['gender'],
                        'is_active' => $userData['isActive'],
                        'phone' => $request->phone,
                    ]);

                    return response()->json($user);
                } else {
                    return response()->json(['error' => 'User verification failed'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $users = MobileUser::select('id', 'first_name', 'last_name', 'birth_date', 'gender', 'is_active', 'phone', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($users);
    }

    public function payments(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'phone' => 'required|string',
                'amount' => 'required|numeric|min:0',
            ]);

            $phone = ltrim($request->phone, '+');
            $paymentData = [
                'amount' => $request->amount,
                'currency' => 'RWF',
                'phone' => $phone,
                'payment_mode' => 'momo',
                'message' => $request->message ?? 'Payment',
                'callback_url' => url('/callback'),
                'transfers' => [[
                    'phone' => $phone,
                    'amount' => $request->amount,
                    'message' => $request->message ?? 'Payment'
                ]]
            ];

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . env('MOPAY_API_TOKEN'),
                ])->post(env('MOPAY_API_BASE') . '/initiate-payment', $paymentData);

                if ($response->successful() && $response->json()) {
                    $data = $response->json();

                    $payment = Payment::create([
                        'phone' => $request->phone,
                        'amount' => $request->amount,
                        'currency' => 'RWF',
                        'reference' => $data['reference'] ?? 'REF-' . time(),
                        'status' => $data['status'] ?? 'completed',
                        'transaction_id' => $data['transaction_id'] ?? null,
                    ]);

                    return response()->json($payment);
                } else {
                    return response()->json(['error' => 'Payment failed'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $payments = Payment::orderBy('created_at', 'desc')->get();
        return response()->json($payments);
    }

    public function transfers(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'receiver_phone' => 'required|string',
                'amount' => 'required|numeric|min:0',
            ]);

            $transferData = [
                'phone' => $request->receiver_phone,
                'amount' => $request->amount,
                'message' => $request->message ?? 'Transfer',
            ];

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . env('MOPAY_API_TOKEN'),
                ])->post(env('MOPAY_API_BASE') . '/transfer', $transferData);

                if ($response->successful() && $response->json()) {
                    $data = $response->json();

                    $transfer = Transfer::create([
                        'sender_phone' => $request->sender_phone ?? 'ADMIN',
                        'receiver_phone' => $request->receiver_phone,
                        'amount' => $request->amount,
                        'message' => $request->message,
                        'status' => $data['status'] ?? 'completed',
                        'transaction_id' => $data['transaction_id'] ?? null,
                    ]);

                    return response()->json($transfer);
                } else {
                    return response()->json(['error' => 'Transfer failed'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $transfers = Transfer::orderBy('created_at', 'desc')->get();
        return response()->json($transfers);
    }

    public function groups(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'group_name' => 'required|string|max:255',
            ]);

            $group = Group::create($request->only('group_name'));
            return response()->json($group);
        }

        $groups = Group::withCount('users as member_count')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($groups);
    }

    public function verifyUser(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('MOPAY_API_TOKEN'),
            ])->get(env('MOPAY_API_BASE') . '/customer-info?phone=' . urlencode($request->phone));

            if ($response->successful() && $response->json()) {
                return response()->json($response->json());
            } else {
                return response()->json(['error' => 'User verification failed'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $phone = ltrim($request->phone, '+');
        $paymentData = [
            'amount' => $request->amount,
            'currency' => $request->currency ?? 'RWF',
            'phone' => $phone,
            'payment_mode' => $request->payment_mode ?? 'momo',
            'message' => $request->message ?? 'Payment',
            'callback_url' => $request->callback_url ?? url('/callback'),
            'transfers' => $request->transfers ?? [[
                'phone' => $phone,
                'amount' => $request->amount,
                'message' => $request->message ?? 'Payment'
            ]]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('MOPAY_API_TOKEN'),
            ])->post(env('MOPAY_API_BASE') . '/initiate-payment', $paymentData);

            return response()->json($response->json() ?? ['error' => 'Payment processing failed']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processTransfer(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $transferData = [
            'phone' => $request->phone,
            'amount' => $request->amount,
            'message' => $request->message ?? 'Transfer',
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('MOPAY_API_TOKEN'),
            ])->post(env('MOPAY_API_BASE') . '/transfer', $transferData);

            return response()->json($response->json() ?? ['error' => 'Transfer processing failed']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
