<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\LokasiKantor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
                'message' => 'Anda sudah melakukan presensi hari ini.'
            ], 422);
        }

        // Presensi mulai pukul 06.00
        if ($jam->hour < 6) {
            return response()->json([
                'success' => false,
                'message' => 'Presensi belum dibuka. Presensi dimulai pukul 06.00 WIB.'
            ], 422);
        }

        // Presensi ditutup pukul 15.00
        if ($jam->hour >= 15) {
            return response()->json([
                'success' => false,
                'message' => 'Waktu presensi sudah ditutup.'
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

            if (!$request->latitude_user || !$request->longitude_user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi pengguna tidak ditemukan.'
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
            if (!$lokasiTerdekat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data lokasi kantor belum tersedia.'
                ], 422);
            }

            // Cek apakah lokasi terdekat masih berada dalam radius
            if ($jarakTerdekat > $lokasiTerdekat->radius_meter) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda berada di luar radius lokasi kantor.',
                    'lokasi_terdekat' => $lokasiTerdekat->nama_lokasi,
                    'jarak' => $jarakTerdekat . ' meter',
                    'radius' => $lokasiTerdekat->radius_meter . ' meter'
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

        // Simpan presensi
        $presensi = Presensi::create([
            'id_user' => $user->id,
            'tanggal' => $tanggal,
            'jam_presensi' => $jam->format('H:i:s'),
            'tipe_kerja' => $request->tipe_kerja,
            'id_lokasi' => $idLokasi,
            'latitude_user' => $request->latitude_user,
            'longitude_user' => $request->longitude_user,
            'jarak_dari_kantor' => $jarakDariKantor,
            'progress_hari_ini' => $request->progress_hari_ini,
            'status_presensi' => $status,
            'status_konfirmasi' => '-',
            'id_admin_konfirmasi' => null,
            'catatan_admin' => null,
            'point_didapat' => $point,
        ]);

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

        if (!$presensi) {
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

        if (!$lokasiTerdekat) {
            return response()->json([
                'success' => false,
                'message' => 'Data lokasi kantor belum tersedia.'
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
            ]
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
