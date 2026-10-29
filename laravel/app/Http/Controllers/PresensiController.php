<?php

namespace App\Http\Controllers;

use App\Models\LokasiKantor;
use App\Models\Presensi;
use App\Models\UserNotification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PresensiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tipe_kerja' => ['required', 'in:WFO,WFH'],
            'latitude_user' => ['nullable', 'numeric'],
            'longitude_user' => ['nullable', 'numeric'],
            'progress_hari_ini' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $tanggal = Carbon::now('Asia/Jakarta')->toDateString();
        $jam = Carbon::now('Asia/Jakarta');

        // Cek apakah user sudah presensi hari ini
        $sudahPresensi = Presensi::where('id_user', $user->id)
            ->where('tanggal', $tanggal)
            ->exists();

        if ($sudahPresensi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi hari ini.',
            ], 422);
        }

        // Presensi mulai pukul 06.00
        if ($jam->hour < 6) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi belum dibuka. Presensi dimulai pukul 06.00 WIB.',
            ], 422);
        }

        // Presensi ditutup pukul 15.00
        if ($jam->hour >= 15) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu presensi sudah ditutup.',
            ], 422);
        }

        // Menentukan status presensi
        if (
            $jam->hour < 10 ||
            ($jam->hour == 10 && $jam->minute == 0)
        ) {
            $status = 'Tepat Waktu';
            $point = 10;
        } else {
            $status = 'Terlambat';
            $point = 5;
        }

        $jarakDariKantor = null;
        $lokasiTerdeteksi = null;
        $idLokasi = null;

        /*
        |--------------------------------------------------------------------------
        | WFO
        |--------------------------------------------------------------------------
        | Lokasi kantor ditentukan otomatis berdasarkan GPS user.
        */
        if ($request->tipe_kerja === 'WFO') {

            if (! $request->latitude_user || ! $request->longitude_user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi pengguna tidak ditemukan.',
                ], 422);
            }

            $daftarLokasi = LokasiKantor::all();

            $lokasiTerdekat = null;
            $jarakTerdekat = null;

            foreach ($daftarLokasi as $lokasi) {

                $jarak = $this->hitungJarak(
                    $request->latitude_user,
                    $request->longitude_user,
                    $lokasi->latitude,
                    $lokasi->longitude
                );

                // Simpan lokasi dengan jarak paling dekat
                if ($jarakTerdekat === null || $jarak < $jarakTerdekat) {
                    $jarakTerdekat = $jarak;
                    $lokasiTerdekat = $lokasi;
                }
            }

            // Tidak ada lokasi kantor
            if (! $lokasiTerdekat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data lokasi kantor belum tersedia.',
                ], 422);
            }

            // Cek apakah lokasi terdekat masih berada dalam radius
            if ($jarakTerdekat > $lokasiTerdekat->radius_meter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar radius lokasi kantor.',
                    'lokasi_terdekat' => $lokasiTerdekat->nama_lokasi,
                    'jarak' => $jarakTerdekat.' meter',
                    'radius' => $lokasiTerdekat->radius_meter.' meter',
                ], 422);
            }

            // Lokasi berhasil terdeteksi
            $lokasiTerdeteksi = $lokasiTerdekat;
            $jarakDariKantor = $jarakTerdekat;
            $idLokasi = $lokasiTerdekat->id_lokasi;
        }

        /*
        |--------------------------------------------------------------------------
        | WFH
        |--------------------------------------------------------------------------
        | Tidak membutuhkan lokasi kantor.
        */
        if ($request->tipe_kerja === 'WFH') {
            $idLokasi = null;
            $jarakDariKantor = null;
        }

        [$presensi] = DB::transaction(function () use (
            $user,
            $tanggal,
            $jam,
            $request,
            $idLokasi,
            $jarakDariKantor,
            $status,
            $point
        ) {
            $presensi = Presensi::create([
                'id_user' => $user->id,
                'tanggal' => $tanggal,
                'jam_presensi' => $jam->format('H:i:s'),
                'tipe_kerja' => $request->tipe_kerja,
                'id_lokasi' => $idLokasi,
                'latitude_user' => $request->latitude_user,
                'longitude_user' => $request->longitude_user,
                'jarak_dari_kantor' => $jarakDariKantor,
                'progress_hari_ini' => $request->input('progress_hari_ini')
                    ?: 'Tidak ada catatan kegiatan.',
                'status_presensi' => $status,
                'status_konfirmasi' => '-',
                'id_admin_konfirmasi' => null,
                'catatan_admin' => null,
                'point_didapat' => $point,
            ]);

            $tanggalLabel = $jam->locale('id')->translatedFormat('j F Y');
            $waktuLabel = $jam->format('H:i').' WIB';
            $judulPresensi = $status === 'Terlambat'
                ? 'Presensi Terlambat'
                : 'Presensi Berhasil';
            $isiPresensi = $status === 'Terlambat'
                ? "Kamu melakukan presensi terlambat pada {$tanggalLabel} pukul {$waktuLabel}."
                : "Presensi {$request->tipe_kerja} kamu berhasil pada {$tanggalLabel} pukul {$waktuLabel}.";

            UserNotification::create([
                'id_user' => $user->getKey(),
                'kategori' => 'presensi',
                'judul' => $judulPresensi,
                'isi' => $isiPresensi,
                'url_tujuan' => url('/history-test'),
            ]);

            UserNotification::create([
                'id_user' => $user->getKey(),
                'kategori' => 'poin',
                'judul' => 'Poin Bertambah',
                'isi' => "Kamu mendapatkan +{$point} poin dari presensi pada {$tanggalLabel}.",
                'url_tujuan' => url('/history-test'),
            ]);

            return [$presensi];
        });

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil disimpan.',
            'data' => $presensi,
            'lokasi' => $lokasiTerdeteksi
                ? [
                    'id_lokasi' => $lokasiTerdeteksi->id_lokasi,
                    'nama_lokasi' => $lokasiTerdeteksi->nama_lokasi,
                    'jarak_meter' => $jarakDariKantor,
                ]
                : null,
        ], 201);
    }

    public function statusHariIni()
    {
        $user = Auth::user();
        $tanggal = Carbon::now('Asia/Jakarta')->toDateString();

        $presensi = Presensi::where('id_user', $user->id)
            ->where('tanggal', $tanggal)
            ->first();

        if (! $presensi) {
            return response()->json([
                'success' => true,
                'sudah_presensi' => false,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'sudah_presensi' => true,
            'data' => [
                'jam_presensi' => $presensi->jam_presensi,
                'tipe_kerja' => $presensi->tipe_kerja,
                'status_presensi' => $presensi->status_presensi,
                'point_didapat' => $presensi->point_didapat,
            ],
        ]);
    }

    public function riwayatSaya()
    {
        $user = Auth::user();
        $tanggalHariIni = Carbon::now('Asia/Jakarta')->startOfDay();
        $tanggalMulai = Carbon::parse(
            $user->tanggal_mulai->toDateString(),
            'Asia/Jakarta'
        )->startOfDay();
        $tanggalSelesai = min(
            Carbon::parse(
                $user->tanggal_selesai->toDateString(),
                'Asia/Jakarta'
            )->startOfDay(),
            $tanggalHariIni
        );

        $riwayat = Presensi::with('lokasiKantor')
            ->where('id_user', $user->id)
            ->whereBetween('tanggal', [
                $tanggalMulai->toDateString(),
                $tanggalSelesai->toDateString(),
            ])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_presensi', 'desc')
            ->get()
            ->keyBy(fn ($presensi) => Carbon::parse($presensi->tanggal)->toDateString());

        $dataRiwayat = [];

        if ($tanggalMulai->lte($tanggalSelesai)) {
            foreach (CarbonPeriod::create($tanggalMulai, $tanggalSelesai) as $tanggal) {
                if (! $tanggal->isWeekday()) {
                    continue;
                }

                $tanggalIso = $tanggal->toDateString();
                $presensi = $riwayat->get($tanggalIso);

                if (! $presensi) {
                    $dataRiwayat[] = [
                        'id' => 'alpha-'.$tanggal->format('Ymd'),
                        'tanggal' => $tanggalIso,
                        'jam' => null,
                        'status' => 'alpha',
                        'mode' => null,
                        'lokasi' => null,
                        'poin' => null,
                        'progress' => null,
                        'alasan' => null,
                        'keterangan' => null,
                        'lat' => null,
                        'lng' => null,
                        'tercatat' => false,
                    ];

                    continue;
                }

                $status = match ($presensi->status_presensi) {
                    'Tepat Waktu' => 'tepat',
                    'Terlambat' => 'terlambat',
                    'Menunggu Konfirmasi' => 'menunggu',
                    'Alpha' => 'alpha',
                    default => 'alpha',
                };

                $dataRiwayat[] = [
                    'id' => $presensi->id_presensi,
                    'tanggal' => $tanggalIso,
                    'jam' => $presensi->jam_presensi
                        ? Carbon::parse($presensi->jam_presensi)->format('H:i')
                        : null,
                    'status' => $status,
                    'mode' => $presensi->tipe_kerja,
                    'lokasi' => $presensi->tipe_kerja === 'WFH'
                        ? 'WFH'
                        : $presensi->lokasiKantor?->nama_lokasi,
                    'poin' => $presensi->point_didapat,
                    'progress' => $presensi->progress_hari_ini,
                    'alasan' => $presensi->catatan_admin,
                    'keterangan' => $presensi->catatan_admin,
                    'lat' => $presensi->latitude_user !== null
                        ? (float) $presensi->latitude_user
                        : null,
                    'lng' => $presensi->longitude_user !== null
                        ? (float) $presensi->longitude_user
                        : null,
                    'tercatat' => true,
                ];
            }
        }

        $dataRiwayat = array_reverse($dataRiwayat);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat presensi berhasil diambil.',
            'meta' => [
                'tanggal_mulai' => $tanggalMulai->toDateString(),
                'tanggal_selesai' => $tanggalSelesai->toDateString(),
                'tanggal_hari_ini' => $tanggalHariIni->toDateString(),
            ],
            'data' => $dataRiwayat,
        ]);
    }

    public function cekLokasi(Request $request)
    {
        $request->validate([
            'latitude_user' => ['required', 'numeric'],
            'longitude_user' => ['required', 'numeric'],
        ]);

        $daftarLokasi = LokasiKantor::all();

        $lokasiTerdekat = null;
        $jarakTerdekat = null;

        foreach ($daftarLokasi as $lokasi) {

            $jarak = $this->hitungJarak(
                $request->latitude_user,
                $request->longitude_user,
                $lokasi->latitude,
                $lokasi->longitude
            );

            if ($jarakTerdekat === null || $jarak < $jarakTerdekat) {
                $jarakTerdekat = $jarak;
                $lokasiTerdekat = $lokasi;
            }
        }

        if (! $lokasiTerdekat) {
            return response()->json([
                'success' => false,
                'message' => 'Data lokasi kantor belum tersedia.',
            ], 422);
        }

        if ($jarakTerdekat > $lokasiTerdekat->radius_meter) {
            return response()->json([
                'success' => false,
                'message' => 'Anda berada di luar radius lokasi kantor.',
                'lokasi_terdekat' => $lokasiTerdekat->nama_lokasi,
                'jarak' => $jarakTerdekat,
                'radius' => $lokasiTerdekat->radius_meter,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lokasi berhasil terdeteksi.',
            'lokasi' => [
                'id_lokasi' => $lokasiTerdekat->id_lokasi,
                'nama_lokasi' => $lokasiTerdekat->nama_lokasi,
                'jarak_meter' => $jarakTerdekat,
                'radius_meter' => $lokasiTerdekat->radius_meter,
            ],
        ]);
    }

    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2)
            + cos($lat1) * cos($lat2)
            * sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c);
    }
}
