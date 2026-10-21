<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Presensi;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Hanya admin yang boleh mengakses dashboard
        abort_unless($request->user()->role === 'admin', 403);

        $hariIni = now()->toDateString();

        $presensiHariIni = Presensi::where('tanggal', $hariIni);

        // Statistik presensi hari ini
        $statistik = [
            'total_peserta_aktif' => User::where('role', 'user')
                ->where('status_akun', 'aktif')
                ->count(),

            'total_presensi_hari_ini' => (clone $presensiHariIni)->count(),

            'tepat_waktu' => (clone $presensiHariIni)
                ->where('status_presensi', 'Tepat Waktu')
                ->count(),

            'terlambat' => (clone $presensiHariIni)
                ->where('status_presensi', 'Terlambat')
                ->count(),

            'menunggu_konfirmasi' => (clone $presensiHariIni)
                ->where('status_konfirmasi', 'Diajukan')
                ->count(),

            'wfo' => (clone $presensiHariIni)
                ->where('tipe_kerja', 'WFO')
                ->count(),

            'wfh' => (clone $presensiHariIni)
                ->where('tipe_kerja', 'WFH')
                ->count(),
        ];

        // Sepuluh presensi terbaru
        $presensiTerbaru = Presensi::with('user:id,nama,bidang_magang')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_presensi')
            ->limit(10)
            ->get()
            ->map(function ($presensi) {
                return [
                    'id_presensi' => $presensi->id_presensi,
                    'nama' => $presensi->user?->nama,
                    'bidang_magang' => $presensi->user?->bidang_magang,
                    'tanggal' => $presensi->tanggal,
                    'jam_presensi' => $presensi->jam_presensi,
                    'tipe_kerja' => $presensi->tipe_kerja,
                    'status_presensi' => $presensi->status_presensi,
                    'status_konfirmasi' => $presensi->status_konfirmasi,
                    'point_didapat' => $presensi->point_didapat,
                ];
            });

        // Daftar peserta magang
        $daftarPeserta = User::where('role', 'user')
            ->select(
                'id',
                'nama',
                'asal_instansi',
                'bidang_magang',
                'tanggal_mulai',
                'tanggal_selesai',
                'status_akun'
            )
            ->orderBy('nama')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => $hariIni,
                'statistik' => $statistik,
                'presensi_terbaru' => $presensiTerbaru,
                'daftar_peserta' => $daftarPeserta,
            ],
        ]);
    }


    public function dashboard(Request $request)
    {
        // Hanya admin yang boleh mengakses dashboard
        abort_unless($request->user()->role === 'admin', 403);

        $hariIni = now()->toDateString();

        $presensiHariIni = Presensi::where('tanggal', $hariIni);

        $statistik = [
            'total_peserta_aktif' => User::where('role', 'user')
                ->where('status_akun', 'aktif')
                ->count(),

            'total_presensi_hari_ini' => (clone $presensiHariIni)->count(),

            'tepat_waktu' => (clone $presensiHariIni)
                ->where('status_presensi', 'Tepat Waktu')
                ->count(),

            'terlambat' => (clone $presensiHariIni)
                ->where('status_presensi', 'Terlambat')
                ->count(),

            'menunggu_konfirmasi' => (clone $presensiHariIni)
                ->where('status_konfirmasi', 'Diajukan')
                ->count(),

            'wfo' => (clone $presensiHariIni)
                ->where('tipe_kerja', 'WFO')
                ->count(),

            'wfh' => (clone $presensiHariIni)
                ->where('tipe_kerja', 'WFH')
                ->count(),
        ];

        $presensiTerbaru = Presensi::with('user:id,nama,bidang_magang')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_presensi')
            ->limit(10)
            ->get();

        $daftarPeserta = User::where('role', 'user')
            ->select(
                'id',
                'nama',
                'asal_instansi',
                'bidang_magang',
                'tanggal_mulai',
                'tanggal_selesai',
                'status_akun'
            )
            ->orderBy('nama')
            ->get();

        return view('admin.dashboard', compact(
            'hariIni',
            'statistik',
            'presensiTerbaru',
            'daftarPeserta'
        ));
    }


    public function konfirmasiPresensi(Request $request, $id)
    {
        // Pastikan hanya admin yang dapat melakukan konfirmasi
        abort_unless($request->user()->role === 'admin', 403);

        $validated = $request->validate([
            'keputusan' => ['required', 'in:Disetujui,Ditolak'],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ]);

        $presensi = Presensi::findOrFail($id);

        // Hanya presensi yang menunggu konfirmasi yang dapat diproses
        if ($presensi->status_konfirmasi !== 'Diajukan') {
            return back()->with(
                'error',
                'Presensi ini sudah diproses atau tidak sedang menunggu konfirmasi.'
            );
        }

        $presensi->status_konfirmasi = $validated['keputusan'];
        $presensi->id_admin_konfirmasi = $request->user()->getKey();
        $presensi->catatan_admin = $validated['catatan_admin'] ?? null;

        // Jika disetujui, status menjadi tepat waktu sesuai aturan konfirmasi
        if ($validated['keputusan'] === 'Disetujui') {
            $presensi->status_presensi = 'Tepat Waktu';
            $presensi->point_didapat = 10;
        } else {
            $presensi->status_presensi = 'Terlambat';
            $presensi->point_didapat = 5;
        }

        $presensi->save();

        return back()->with(
            'success',
            'Konfirmasi presensi berhasil disimpan.'
        );
    }


    public function dataPresensi(\Illuminate\Http\Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $query = \App\Models\Presensi::with([
            'user',
            'lokasiKantor',
            'adminKonfirmasi',
        ]);

        // Pencarian nama peserta
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->whereHas('user', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            });
        }

        // Filter tanggal mulai
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate(
                'tanggal',
                '>=',
                $request->input('tanggal_mulai')
            );
        }

        // Filter tanggal akhir
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate(
                'tanggal',
                '<=',
                $request->input('tanggal_selesai')
            );
        }

        // Filter status presensi
        if ($request->filled('status_presensi')) {
            $query->where(
                'status_presensi',
                $request->input('status_presensi')
            );
        }

        // Filter tipe kerja
        if ($request->filled('tipe_kerja')) {
            $query->where(
                'tipe_kerja',
                $request->input('tipe_kerja')
            );
        }

        $presensi = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_presensi')
            ->paginate(15)
            ->withQueryString();

        return view('admin.data-presensi', [
            'presensi' => $presensi,
            'filter' => $request->only([
                'search',
                'tanggal_mulai',
                'tanggal_selesai',
                'status_presensi',
                'tipe_kerja',
            ]),
        ]);
    }
}
