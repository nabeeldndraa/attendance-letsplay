<?php

namespace App\Http\Controllers;

use App\Models\RegistrationSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegister()
    {
        if (! RegistrationSetting::query()->findOrFail(1)->isOpen()) {
            return redirect()->route('login')
                ->with('error', 'Pendaftaran akun baru sudah ditutup.');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (! RegistrationSetting::query()->findOrFail(1)->isOpen()) {
            return redirect()->route('login')
                ->with('error', 'Pendaftaran akun baru sudah ditutup.');
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'asal_instansi' => ['required', 'string', 'max:255'],
            'bidang_magang' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => [
                'required',
                'date',
                'after_or_equal:tanggal_mulai',
            ],
            'no_wa' => ['required', 'string', 'max:20'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ]);

        $validated['role'] = 'user';
        $validated['status_akun'] = 'aktif';

        $registered = DB::transaction(function () use ($validated): bool {
            $setting = RegistrationSetting::query()
                ->lockForUpdate()
                ->findOrFail(1);

            if (! $setting->isOpen()) {
                return false;
            }

            User::create($validated);
            $setting->update(['closed_at' => now()]);

            return true;
        });

        if (! $registered) {
            return redirect()->route('login')
                ->with('error', 'Pendaftaran akun baru sudah ditutup.');
        }

        return redirect('/login-test')
            ->with('success', 'Pendaftaran berhasil. Silakan login.');
    }
}
