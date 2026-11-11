<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\User;

class AdminNotificationService
{
    public function notifyNewAccount(User $user, string $action): void
    {
        $this->notifyAdmins([
            'kategori' => 'akun',
            'judul' => 'Akun Intern Baru',
            'deskripsi' => 'Peserta magang '.$user->nama.' '.$action.'.',
            'detail' => 'Nama: '.$user->nama
                ."\nEmail: ".$user->email
                ."\nAsal instansi: ".$user->asal_instansi
                ."\nBidang magang: ".$user->bidang_magang,
            'sumber_tipe' => 'akun',
            'sumber_id' => $user->getKey(),
            'status' => null,
        ]);
    }

    public function notifyAdmins(array $notification): void
    {
        $now = now();

        User::query()
            ->where('role', 'admin')
            ->select('id')
            ->chunkById(500, function ($admins) use ($notification, $now) {
                $rows = $admins->map(fn (User $admin) => array_merge(
                    $notification,
                    [
                        'id_admin' => $admin->getKey(),
                        'dibaca_pada' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                ))->all();

                AdminNotification::insert($rows);
            });
    }
}
