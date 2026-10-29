<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetCode;
use App\Models\PasswordResetCode as PasswordResetCodeModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PasswordResetController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.passwords.request');
    }

    public function sendCode(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = Str::lower($validated['email']);
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user) {
            $code = (string) random_int(100000, 999999);

            $resetCode = PasswordResetCodeModel::updateOrCreate(
                ['email' => $email],
                [
                    'code_hash' => Hash::make($code),
                    'expires_at' => now()->addMinutes(10),
                    'attempts' => 0,
                ]
            );

            try {
                Mail::to($user->email)->send(new PasswordResetCode($code));
            } catch (TransportExceptionInterface $exception) {
                PasswordResetCodeModel::query()
                    ->whereKey($resetCode->getKey())
                    ->where('code_hash', $resetCode->code_hash)
                    ->delete();

                Log::error('Password reset email delivery failed.', [
                    'exception' => $exception,
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                ]);

                return back()
                    ->withInput(['email' => $email])
                    ->with('error', 'Kode belum bisa dikirim. Coba lagi beberapa saat, atau hubungi administrator.');
            }
        }

        return redirect()
            ->route('password.verify')
            ->with('reset_email', $email)
            ->with(
                'success',
                'Jika email terdaftar, kode verifikasi telah dikirim.'
            );
    }

    public function showVerificationForm()
    {
        return view('auth.passwords.verify', [
            'email' => session('reset_email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $email = Str::lower($validated['email']);

        $reset = DB::transaction(function () use ($validated, $email): bool {
            $resetCode = PasswordResetCodeModel::query()
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (
                ! $resetCode
                || $resetCode->expires_at->isPast()
                || $resetCode->attempts >= 5
            ) {
                return false;
            }

            if (! Hash::check($validated['code'], $resetCode->code_hash)) {
                $resetCode->increment('attempts');

                return false;
            }

            $user = User::whereRaw('LOWER(email) = ?', [$email])
                ->lockForUpdate()
                ->first();

            if (! $user) {
                return false;
            }

            $user->password = $validated['password'];
            $user->setRememberToken(Str::random(60));
            $user->save();
            $resetCode->delete();

            return true;
        });

        if (! $reset) {
            return back()
                ->withErrors([
                    'code' => 'Kode tidak valid atau sudah kedaluwarsa. Minta kode baru jika diperlukan.',
                ])
                ->withInput(['email' => $email]);
        }

        return redirect()
            ->route('login')
            ->with('success', 'Password berhasil diubah. Silakan login.');
    }
}
