<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class userRolesController extends Controller
{
    private const ROLE_OPTIONS = [
        'admin' => 'Admin',
        'chief_of_motorpool_section' => 'Chief of Motorpool',
        'operator' => 'Operator',
        'driver' => 'Driver',
        'user' => 'User',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('personnel_id', 'like', '%' . $search . '%')
                        ->orWhere('role', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.userAccess.user_roles', [
            'users' => $users,
            'search' => $search,
            'roleOptions' => self::ROLE_OPTIONS,
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'array', 'min:1'],
            'role.*' => ['required', Rule::in(array_keys(self::ROLE_OPTIONS))],
        ]);

        $selectedRoles = collect($validated['role'])
            ->map(fn($role) => (string) $role)
            ->unique()
            ->values();

        $rolesToSave = $selectedRoles->implode(',');

        $roleLabels = $selectedRoles
            ->map(fn(string $role) => self::ROLE_OPTIONS[$role] ?? ucfirst(str_replace('_', ' ', $role)))
            ->implode(', ');

        $user->update([
            'role' => $rolesToSave,
        ]);

        return redirect()
            ->route('admin.user_roles', $request->only('search', 'page'))
            ->with('user_role_success', 'Updated role for ' . $user->name . ' to ' . $roleLabels . '.');
    }
}
