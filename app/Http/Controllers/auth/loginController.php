<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class loginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'personnel_id' => ['required', 'digits:6'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            $user = User::query()->where('personnel_id', $credentials['personnel_id'])->first();

            AuditLogger::record(
                $user,
                $request,
                'LOGIN',
                'Login attempt failed for personnel ID ' . $credentials['personnel_id'] . '.',
                'FAILED'
            );

            return back()
                ->withErrors(['personnel_id' => 'Invalid credentials.'])
                ->onlyInput('personnel_id');
        }

        $request->session()->regenerate();

        AuditLogger::record(
            Auth::user(),
            $request,
            'LOGIN',
            'User login successful.'
        );

        return $this->redirectByRole((string) Auth::user()->role);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        AuditLogger::record(
            $user,
            $request,
            'LOGOUT',
            'User logged out from the system.'
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectByRole(string $role)
    {
        $roles = collect(explode(',', $role))
            ->map(fn($value) => strtolower(trim((string) $value)))
            ->filter();

        if ($roles->contains('admin') || $roles->contains('chief_of_motorpool_section')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('user.dashboard');
    }
}
