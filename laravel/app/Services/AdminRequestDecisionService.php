<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\LeaveRequest;
use App\Models\Presensi;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminRequestDecisionService
{
    public function decideAttendance(
        int $presensiId,
        User $admin,
        string $decision,
        ?string $note = null
    ): bool {
        return DB::transaction(function () use ($presensiId, $admin, $decision, $note) {
            $presensi = Presensi::query()
                ->lockForUpdate()
                ->findOrFail($presensiId);

            if ($presensi->status_konfirmasi !== 'Diajukan') {
                return false;
            }

            $pointsBefore = $presensi->point_didapat;
            $presensi->status_konfirmasi = $decision;
            $presensi->id_admin_konfirmasi = $admin->getKey();
            $presensi->catatan_admin = $note;
            $presensi->status_presensi = $decision === 'Disetujui'
                ? 'Tepat Waktu'
                : 'Terlambat';
            $presensi->point_didapat = $decision === 'Disetujui' ? 10 : 5;
            $presensi->save();
            AdminNotification::query()
                ->where('sumber_tipe', 'presensi')
                ->where('sumber_id', $presensi->getKey())
                ->where('status', 'Diajukan')
                ->update([
                    'status' => $decision,
                    'dibaca_pada' => now(),
                ]);

            $tanggalLabel = Carbon::parse($presensi->tanggal)
                ->locale('id')
                ->translatedFormat('j F Y');
            $disetujui = $decision === 'Disetujui';

            UserNotification::create([
                'id_user' => $presensi->id_user,
                'kategori' => 'presensi',
                'judul' => $disetujui
                    ? 'Presensi Disetujui'
                    : 'Presensi Ditolak',
                'isi' => $disetujui
                    ? "Pengajuan perubahan presensi tanggal {$tanggalLabel} telah disetujui."
                    : "Pengajuan perubahan presensi tanggal {$tanggalLabel} ditolak.",
                'url_tujuan' => url('/history-test'),
            ]);

            $additionalPoints = $presensi->point_didapat - $pointsBefore;

            if ($additionalPoints > 0) {
                UserNotification::create([
                    'id_user' => $presensi->id_user,
                    'kategori' => 'poin',
                    'judul' => 'Poin Bertambah',
                    'isi' => "Kamu mendapatkan tambahan +{$additionalPoints} poin setelah presensi tanggal {$tanggalLabel} disetujui.",
                    'url_tujuan' => url('/history-test'),
                ]);
            }

            return true;
        });
    }

    public function decideLeave(
        int $leaveRequestId,
        User $admin,
        string $decision,
        ?string $note = null
    ): bool {
        return DB::transaction(function () use ($leaveRequestId, $admin, $decision, $note) {
            $leaveRequest = LeaveRequest::query()
                ->lockForUpdate()
                ->findOrFail($leaveRequestId);

            if ($leaveRequest->status !== 'Diajukan') {
                return false;
            }

            $leaveRequest->status = $decision;
            $leaveRequest->id_admin_konfirmasi = $admin->getKey();
            $leaveRequest->catatan_admin = $note;
            $leaveRequest->save();
            AdminNotification::query()
                ->where('sumber_tipe', 'izin')
                ->where('sumber_id', $leaveRequest->getKey())
                ->where('status', 'Diajukan')
                ->update([
                    'status' => $decision,
                    'dibaca_pada' => now(),
                ]);

            $tanggalLabel = $leaveRequest->tanggal
                ->locale('id')
                ->translatedFormat('j F Y');
            $disetujui = $decision === 'Disetujui';

            UserNotification::create([
                'id_user' => $leaveRequest->id_user,
                'kategori' => 'presensi',
                'judul' => $disetujui
                    ? 'Pengajuan Izin Disetujui'
                    : 'Pengajuan Izin Ditolak',
                'isi' => $disetujui
                    ? "Pengajuan {$leaveRequest->jenis} tanggal {$tanggalLabel} telah disetujui."
                    : "Pengajuan {$leaveRequest->jenis} tanggal {$tanggalLabel} ditolak."
                        .($note ? " Catatan admin: {$note}" : ''),
                'url_tujuan' => url('/notifikasi-test'),
            ]);

            return true;
        });
    }
}
