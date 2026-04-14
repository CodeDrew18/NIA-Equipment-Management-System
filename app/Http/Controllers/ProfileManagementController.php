<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileManagementController extends Controller
{
    public function index(Request $request): View
    {
        $authUser = $request->user();

        $normalizedRoles = collect(explode(',', (string) ($authUser?->role ?? '')))
            ->map(function (string $role) {
                return strtolower(trim($role));
            })
            ->filter()
            ->values();

        $isAdminArea = $normalizedRoles->intersect(['admin', 'chief_of_motorpool_section'])->isNotEmpty();

        return view('profile.profile_management', [
            'authUser' => $authUser,
            'isAdminArea' => $isAdminArea,
        ]);
    }
}
