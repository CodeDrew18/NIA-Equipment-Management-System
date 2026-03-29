<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TransportationRequestFormModel;
use Illuminate\Http\Request;

class adminTransportationRequest extends Controller
{
    private const STATUS_OPTIONS = ['To be Signed', 'Signed'];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $requests = TransportationRequestFormModel::query()
            ->where('status', 'To be Signed')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('form_id', 'like', '%' . $search . '%')
                        ->orWhere('requested_by', 'like', '%' . $search . '%')
                        ->orWhere('destination', 'like', '%' . $search . '%')
                        ->orWhere('vehicle_type', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.transportation_request.admin_transportation_request', [
            'requests' => $requests,
            'search' => $search,
        ]);
    }

    public function updateStatus(Request $request, TransportationRequestFormModel $transportationRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUS_OPTIONS)],
        ]);

        $transportationRequest->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.transportation-request')
            ->with('admin_transportation_request_success', 'Request status updated to ' . $validated['status'] . '.');
    }
}
