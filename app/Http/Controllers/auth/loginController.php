<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
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
            return back()
                ->withErrors(['personnel_id' => 'Invalid credentials.'])
                ->onlyInput('personnel_id');
        }

        $request->session()->regenerate();

        return $this->redirectByRole((string) Auth::user()->role);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectByRole(string $role)
    {
        $roles = collect(explode(',', $role))
            ->map(fn($value) => trim((string) $value))
            ->filter();

        if ($roles->contains('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('landing-page');
    }
}
