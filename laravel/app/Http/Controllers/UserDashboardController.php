<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $yesterday = $today->copy()->subDay();
        $periodStart = Carbon::parse(
            $user->tanggal_mulai->toDateString(),
            'Asia/Jakarta'
        )->startOfDay();
        $periodEnd = min(
            Carbon::parse(
                $user->tanggal_selesai->toDateString(),
                'Asia/Jakarta'
            )->startOfDay(),
            $today
        );

        $hariKerja = 0;

        if ($periodStart->lte($periodEnd)) {
            foreach (CarbonPeriod::create($periodStart, $periodEnd) as $tanggal) {
                if ($tanggal->isWeekday()) {
                    $hariKerja++;
                }
            }
        }

        $presensi = $user->presensi()
            ->whereBetween('tanggal', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ]);
        $statusHadir = ['Tepat Waktu', 'Terlambat', 'Menunggu Konfirmasi'];
        $statusTerlambat = ['Terlambat', 'Menunggu Konfirmasi'];

        $hadir = (clone $presensi)
            ->whereIn('status_presensi', $statusHadir)
            ->count();
        $hariHadir = (clone $presensi)
            ->whereIn('status_presensi', $statusHadir)
            ->distinct()
            ->pluck('tanggal')
            ->filter(fn ($tanggal) => Carbon::parse($tanggal, 'Asia/Jakarta')->isWeekday())
            ->count();

        $poin = (clone $presensi)
            ->whereIn('status_presensi', $statusHadir)
            ->sum('point_didapat');
        $poinKemarin = (clone $presensi)
            ->whereIn('status_presensi', $statusHadir)
            ->whereDate('tanggal', $yesterday->toDateString())
            ->sum('point_didapat');

        $presensiTerakhir = (clone $presensi)
            ->with('lokasiKantor')
            ->whereIn('status_presensi', $statusHadir)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_presensi')
            ->first();

        $stat = [
            'poin' => $poin,
            'poin_kemarin' => $poinKemarin,
            'hadir' => $hadir,
            'hari_kerja' => $hariKerja,
            'tepat' => (clone $presensi)
                ->where('status_presensi', 'Tepat Waktu')
                ->count(),
            'terlambat' => (clone $presensi)
                ->whereIn('status_presensi', $statusTerlambat)
                ->count(),
            'alpha' => max(0, $hariKerja - $hariHadir),
        ];

        $last = null;

        if ($presensiTerakhir) {
            $tanggalPresensi = Carbon::parse(
                $presensiTerakhir->tanggal,
                'Asia/Jakarta'
            )->locale('id');
            $namaLokasi = $presensiTerakhir->tipe_kerja === 'WFH'
                ? 'WFH'
                : 'WFO - '.($presensiTerakhir->lokasiKantor?->nama_lokasi ?? 'Lokasi tidak tersedia');

            $last = [
                'status' => $presensiTerakhir->status_presensi === 'Menunggu Konfirmasi'
                    ? 'Menunggu Konfirmasi'
                    : $presensiTerakhir->status_presensi,
                'hari' => mb_strtoupper($tanggalPresensi->translatedFormat('l')),
                'tanggal' => $tanggalPresensi->format('j'),
                'bulan' => mb_strtoupper($tanggalPresensi->translatedFormat('M')),
                'lokasi' => $namaLokasi,
                'jam' => Carbon::parse($presensiTerakhir->jam_presensi)->format('H:i').' WIB',
                'kegiatan' => $presensiTerakhir->progress_hari_ini ?: 'Tidak ada catatan kegiatan.',
                'poin' => $presensiTerakhir->point_didapat,
            ];
        }

        return view('dashboard-test', [
            'stat' => $stat,
            'last' => $last,
        ]);
    }
}
