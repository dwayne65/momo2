<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::orderBy('created_at', 'desc')->paginate(20);
        $totalPayments = Payment::count();
        $totalAmount = Payment::where('status', 'completed')->sum('amount');

        return view('payments.index', compact('payments', 'totalPayments', 'totalAmount'));
    }

    public function process(Request $request)
    {
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
            'message' => 'Payment',
            'callback_url' => url('/callback'),
            'transfers' => [[
                'phone' => $phone,
                'amount' => $request->amount,
                'message' => 'Payment'
            ]]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('MOPAY_API_TOKEN'),
            ])->post(env('MOPAY_API_BASE') . '/initiate-payment', $paymentData);

            if ($response->successful() && $response->json()) {
                $data = $response->json();

                Payment::create([
                    'phone' => $request->phone,
                    'amount' => $request->amount,
                    'currency' => 'RWF',
                    'reference' => $data['reference'] ?? 'REF-' . time(),
                    'status' => $data['status'] ?? 'completed',
                    'transaction_id' => $data['transaction_id'] ?? null,
                ]);

                return back()->with('success', 'Payment processed successfully!');
            } else {
                return back()->withErrors(['error' => 'Payment failed. Please try again.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'API error: ' . $e->getMessage()]);
        }
    }

    public function showProcessForm()
    {
        return view('payments.process');
    }
}
