<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transfer;
use Illuminate\Support\Facades\Http;

class TransferController extends Controller
{
    public function index()
    {
        $transfers = Transfer::orderBy('created_at', 'desc')->paginate(20);
        $totalTransfers = Transfer::count();
        $totalAmount = Transfer::where('status', 'completed')->sum('amount');

        return view('transfers.index', compact('transfers', 'totalTransfers', 'totalAmount'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'receiver_phone' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'message' => 'nullable|string|max:255',
        ]);

        $transferData = [
            'phone' => $request->receiver_phone,
            'amount' => $request->amount,
            'message' => $request->message ?: 'Transfer',
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('MOPAY_API_TOKEN'),
            ])->post(env('MOPAY_API_BASE') . '/transfer', $transferData);

            if ($response->successful() && $response->json()) {
                $data = $response->json();

                Transfer::create([
                    'sender_phone' => 'ADMIN', // Placeholder
                    'receiver_phone' => $request->receiver_phone,
                    'amount' => $request->amount,
                    'message' => $request->message,
                    'status' => $data['status'] ?? 'completed',
                    'transaction_id' => $data['transaction_id'] ?? null,
                ]);

                return back()->with('success', 'Transfer completed successfully!');
            } else {
                return back()->withErrors(['error' => 'Transfer failed. Please try again.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'API error: ' . $e->getMessage()]);
        }
    }

    public function showProcessForm()
    {
        return view('transfers.process');
    }
}
