<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class landingController extends Controller
{
    function landingPage()
    {
        $authUser = Auth::user();

        if ($authUser) {
            $roles = collect(explode(',', (string) $authUser->role))
                ->map(function ($value) {
                    return strtolower(trim((string) $value));
                })
                ->filter();

            $adminRoles = collect(['admin', 'chief_of_motorpool_section']);
            $hasAdminRole = $roles->intersect($adminRoles)->isNotEmpty();

            if ($hasAdminRole) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('user.dashboard');
        }

        return view('landing');
    }
}
