<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MobileUser;
use App\Models\Payment;
use App\Models\Transfer;
use App\Models\Group;

class ExportController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => MobileUser::count(),
            'payments' => Payment::count(),
            'transfers' => Transfer::count(),
            'groups' => Group::count(),
        ];

        return view('export.index', compact('stats'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'export_type' => 'required|string',
            'format' => 'required|in:csv,json',
        ]);

        $data = null;

        switch ($request->export_type) {
            case 'users':
                $data = MobileUser::select('id', 'first_name', 'last_name', 'birth_date', 'gender', 'is_active', 'phone', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->get();
                break;

            case 'payments':
                $data = Payment::select('id', 'phone', 'amount', 'currency', 'reference', 'status', 'transaction_id', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->get();
                break;

            case 'transfers':
                $data = Transfer::select('id', 'sender_phone', 'receiver_phone', 'amount', 'message', 'status', 'transaction_id', 'created_at')
                    ->orderBy('created_at', 'desc')
                    ->get();
                break;

            case 'groups':
                $data = Group::select('id', 'group_name', 'created_at')
                    ->withCount('users as member_count')
                    ->orderBy('created_at', 'desc')
                    ->get();
                break;

            default:
                return back()->withErrors(['error' => 'Invalid export type']);
        }

        if ($request->format === 'csv') {
            return $this->exportCsv($data, $request->export_type);
        } elseif ($request->format === 'json') {
            return $this->exportJson($data, $request->export_type);
        }
    }

    private function exportCsv($data, $type)
    {
        $filename = $type . '_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()->toArray()));
                foreach ($data as $row) {
                    fputcsv($file, $row->toArray());
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportJson($data, $type)
    {
        $filename = $type . '_export_' . date('Y-m-d_H-i-s') . '.json';

        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->json($data, 200, $headers);
    }
}
