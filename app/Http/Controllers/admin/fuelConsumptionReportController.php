<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class fuelConsumptionReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'office_name' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'prepared_by' => ['nullable', 'string', 'max:255'],
            'prepared_position' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'string', 'max:255'],
        ]);

        $selectedMonth = (string) ($validated['month'] ?? now()->format('Y-m'));
        $monthLabel = Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y');

        $rows = [
            ['user' => 'Region I NIS & CIS Office'],
        ];

        for ($i = 0; $i < 11; $i++) {
            $rows[] = ['user' => ''];
        }

        return view('admin.travel_reports.fuel_consumption_report', [
            'selectedMonth' => $selectedMonth,
            'monthLabel' => strtoupper($monthLabel),
            'officeName' => (string) ($validated['office_name'] ?? 'National Irrigation Administration'),
            'addressLine1' => (string) ($validated['address_line_1'] ?? 'Bayaoas, Urdaneta City'),
            'addressLine2' => (string) ($validated['address_line_2'] ?? 'Pangasinan'),
            'preparedBy' => (string) ($validated['prepared_by'] ?? ''),
            'preparedPosition' => (string) ($validated['prepared_position'] ?? ''),
            'approvedBy' => (string) ($validated['approved_by'] ?? ''),
            'rows' => $rows,
        ]);
    }
}
