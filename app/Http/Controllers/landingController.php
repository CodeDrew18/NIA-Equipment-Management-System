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
            $roles = collect(explode(',', (string) Auth::user()->role))
                ->map(function ($value) {
                    return strtolower(trim((string) $value));
                })
                ->filter();

            $adminRoles = collect(['admin', 'chief_of_motorpool_section']);
            $hasAdminRole = $roles->intersect($adminRoles)->isNotEmpty();
            $hasNonAdminRole = $roles->diff($adminRoles)->isNotEmpty();

            // Restrict landing only for admin-only accounts.
            if ($hasAdminRole && !$hasNonAdminRole) {
                return redirect()->route('admin.dashboard');
            }

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
