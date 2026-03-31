<?php

namespace App\Http\Controllers;

use App\Models\TransportationRequestFormModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class landingController extends Controller
{
    function landingPage()
    {
        $requesterMessages = collect();

        if (Auth::check()) {
            $requesterMessages = TransportationRequestFormModel::query()
                ->where('form_creator_id', Auth::user()->personnel_id)
                ->where('status', 'Rejected')
                ->whereNotNull('rejection_reason')
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get();
        }

        return view('landing', [
            'requesterMessages' => $requesterMessages,
        ]);
    }
}
