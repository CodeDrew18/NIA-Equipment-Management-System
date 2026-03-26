<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TransportationRequestFormModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class requestFormController extends Controller
{
    public function requestForm()
    {
        return view('letter_of_request/requestform');
    }

    public function submitRequestForm(Request $request)
    {
        $validated = $request->validate([
            'request_date' => ['required', 'date'],
            'requested_by' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'date_time_from' => ['required', 'date'],
            'date_time_to' => ['required', 'date', 'after:date_time_from'],
            'purpose' => ['required', 'string', 'max:2000'],
            'vehicle_type' => ['required', 'in:coaster,van,pickup'],
            'vehicle_quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'division_personnel' => ['required', 'array', 'min:1'],
            'division_personnel.*.id_number' => ['required', 'digits:6', 'exists:users,personnel_id'],
            'division_personnel.*.name' => ['required', 'string', 'max:255'],
            'vehicle_id' => ['nullable', 'string', 'max:100'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $storedAttachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $path = $attachment->store('request_attachments', 'public');
                $storedAttachments[] = [
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_path' => $path,
                ];
            }
        }

        TransportationRequestFormModel::create([
            'form_id' => 'REQ-' . now()->format('Y') . '-' . strtoupper(Str::random(4)),
            'request_date' => $validated['request_date'],
            'requested_by' => $validated['requested_by'],
            'destination' => $validated['destination'],
            'date_time_from' => $validated['date_time_from'],
            'date_time_to' => $validated['date_time_to'],
            'purpose' => $validated['purpose'],
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_quantity' => $validated['vehicle_quantity'],
            'division_personnel' => $validated['division_personnel'],
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'driver_name' => $validated['driver_name'] ?? null,
            'attachments' => $storedAttachments,
        ]);

        return redirect()
            ->route('request-form')
            ->with('success', 'Transportation request submitted successfully.');
    }

    public function personnelLookup(string $personnelId)
    {
        if (!preg_match('/^\d{6}$/', $personnelId)) {
            return response()->json(['message' => 'Invalid personnel ID format.'], 422);
        }

        $user = User::query()
            ->select('personnel_id', 'name')
            ->where('personnel_id', $personnelId)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Personnel not found.'], 404);
        }

        return response()->json([
            'personnel_id' => $user->personnel_id,
            'name' => $user->name,
        ]);
    }
}
