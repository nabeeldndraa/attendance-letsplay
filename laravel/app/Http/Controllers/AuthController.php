<?php

namespace App\Http\Controllers;

use App\Models\RegistrationSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectForUser(Auth::user());
        }

        return view('login-test', [
            'registrationOpen' => RegistrationSetting::query()
                ->findOrFail(1)
                ->isOpen(),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors([
                    'login' => 'Email atau password salah.',
                ])
                ->withInput();
        }

        if ($user->status_akun !== 'aktif') {
            return back()
                ->withErrors([
                    'login' => 'Akun tidak aktif.',
                ])
                ->withInput();
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return $this->redirectForUser($user);
    }

    private function redirectForUser(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect('/dashboard-test');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login-test')
            ->with('success', 'Logout berhasil.');
    }

    public function redirectAfterLogin(User $user)
    {
        return $this->redirectForUser($user);
    }
}
