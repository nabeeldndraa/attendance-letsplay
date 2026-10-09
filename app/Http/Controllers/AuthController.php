<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {

            return back()
                ->withErrors([
                    'login' => 'Email atau password salah.'
                ])
                ->withInput();
        }

        if ($user->status_akun !== 'aktif') {

            return back()
                ->withErrors([
                    'login' => 'Akun tidak aktif.'
                ])
                ->withInput();
        }

        Auth::login($user);

        $request->session()->regenerate();

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
}