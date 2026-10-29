<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        if (
            ! config('services.google.client_id')
            || ! config('services.google.client_secret')
            || ! config('services.google.redirect')
        ) {
            return redirect()->route('login')
                ->with('error', 'Login Google belum dikonfigurasi. Hubungi administrator.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, AuthController $authController)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            Log::warning('Google sign-in state validation failed.');

            return redirect()->route('login')
                ->with('error', 'Sesi login Google berakhir. Silakan coba lagi.');
        }

        $email = $googleUser->getEmail();
        $googleProfile = $googleUser->getRaw();

        if (! $email || ($googleProfile['verified_email'] ?? false) !== true) {
            return redirect()->route('login')
                ->with('error', 'Google tidak dapat memverifikasi alamat email akun ini.');
        }

        $user = User::whereRaw('LOWER(email) = ?', [Str::lower($email)])->first();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Email Google belum terdaftar. Silakan hubungi administrator.');
        }

        if ($user->status_akun !== 'aktif') {
            return redirect()->route('login')
                ->with('error', 'Akun tidak aktif.');
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return $authController->redirectAfterLogin($user);
    }
}
