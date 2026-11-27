<?php

namespace App\Http\Controllers;

use App\Models\RegistrationSetting;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    private const BIDANG_MAGANG_OPTIONS = [
        'Ilustrator 2D',
        'Ilustrator 3D',
        'Animator',
        'Content Creator',
        'Videografer',
        'Video Editor',
        'Digital Marketing',
        'Public Relation',
        'Game Designer',
        'UI/UX Designer',
        'Programmer',
        'Human Resource',
    ];

    public function showRegister()
    {
        if (! RegistrationSetting::query()->findOrFail(1)->isOpen()) {
            return redirect()->route('login')
                ->with('error', 'Pendaftaran akun baru sudah ditutup.');
        }

        return view('auth.register', [
            'bidangMagangOptions' => self::BIDANG_MAGANG_OPTIONS,
        ]);
    }

    public function register(Request $request, AdminNotificationService $adminNotifications)
    {
        if (! RegistrationSetting::query()->findOrFail(1)->isOpen()) {
            return redirect()->route('login')
                ->with('error', 'Pendaftaran akun baru sudah ditutup.');
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'asal_instansi' => ['required', 'string', 'max:255'],
            'bidang_magang_select' => [
                'required',
                'string',
                Rule::in([...self::BIDANG_MAGANG_OPTIONS, 'lainnya']),
            ],
            'bidang_magang_custom' => [
                'nullable',
                'required_if:bidang_magang_select,lainnya',
                'string',
                'max:255',
            ],
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

        $validated['bidang_magang'] = $validated['bidang_magang_select'] === 'lainnya'
            ? trim($validated['bidang_magang_custom'])
            : $validated['bidang_magang_select'];
        unset($validated['bidang_magang_select'], $validated['bidang_magang_custom']);

        $validated['role'] = 'user';
        $validated['status_akun'] = 'aktif';

        $registered = DB::transaction(function () use ($validated, $adminNotifications): bool {
            $setting = RegistrationSetting::query()
                ->lockForUpdate()
                ->findOrFail(1);

            if (! $setting->isOpen()) {
                return false;
            }

            $user = User::create($validated);
            $adminNotifications->notifyNewAccount($user, 'berhasil mendaftar');
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
