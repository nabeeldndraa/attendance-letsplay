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
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        if ($user->status_akun !== 'aktif') {
            return response()->json([
                'message' => 'Akun tidak aktif.'
            ], 403);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/login-test')->with('success', 'Login berhasil.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout berhasil.'
        ]);
    }
}
