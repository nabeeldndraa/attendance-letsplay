<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Presensi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Pastikan lokasi kantor tersedia
        |--------------------------------------------------------------------------
        */

        DB::table('lokasi_kantor')->updateOrInsert(
            ['nama_lokasi' => 'Kolektive Space'],
            [
                'latitude' => -7.94858616,
                'longitude' => 112.64314358,
                'radius_meter' => 100,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('lokasi_kantor')->updateOrInsert(
            ['nama_lokasi' => 'Malang Creative Center'],
            [
                'latitude' => -7.94082471,
                'longitude' => 112.64241174,
                'radius_meter' => 100,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $lokasiKantor = DB::table('lokasi_kantor')
            ->pluck('id_lokasi', 'nama_lokasi');

        /*
        |--------------------------------------------------------------------------
        | 2. Data 20 peserta dummy
        |--------------------------------------------------------------------------
        */

        $peserta = [
            [
                'nama' => 'Dimas Pratama',
                'email' => 'dimas.demo@example.com',
                'asal_instansi' => 'Politeknik Negeri Jember',
                'bidang_magang' => 'Frontend Developer',
            ],
            [
                'nama' => 'Alya Putri',
                'email' => 'alya.demo@example.com',
                'asal_instansi' => 'Universitas Brawijaya',
                'bidang_magang' => 'UI/UX Designer',
            ],
            [
                'nama' => 'Raka Saputra',
                'email' => 'raka.demo@example.com',
                'asal_instansi' => 'Politeknik Negeri Malang',
                'bidang_magang' => 'Backend Developer',
            ],
            [
                'nama' => 'Salsa Maharani',
                'email' => 'salsa.demo@example.com',
                'asal_instansi' => 'Universitas Negeri Malang',
                'bidang_magang' => 'Graphic Designer',
            ],
            [
                'nama' => 'Fajar Ramadhan',
                'email' => 'fajar.demo@example.com',
                'asal_instansi' => 'Universitas Jember',
                'bidang_magang' => 'Quality Assurance',
            ],
            [
                'nama' => 'Bima Pratama',
                'email' => 'bima.demo@example.com',
                'asal_instansi' => 'Politeknik Negeri Jember',
                'bidang_magang' => 'Backend Developer',
            ],
            [
                'nama' => 'Nadia Putri',
                'email' => 'nadia.demo@example.com',
                'asal_instansi' => 'Universitas Brawijaya',
                'bidang_magang' => 'UI/UX Designer',
            ],
            [
                'nama' => 'Rizky Ramadhan',
                'email' => 'rizky.demo@example.com',
                'asal_instansi' => 'Politeknik Negeri Malang',
                'bidang_magang' => 'Frontend Developer',
            ],
            [
                'nama' => 'Putri Amelia',
                'email' => 'putri.demo@example.com',
                'asal_instansi' => 'Universitas Negeri Malang',
                'bidang_magang' => 'Quality Assurance',
            ],
            [
                'nama' => 'Daffa Saputra',
                'email' => 'daffa.demo@example.com',
                'asal_instansi' => 'Politeknik Negeri Jember',
                'bidang_magang' => 'Backend Developer',
            ],
            [
                'nama' => 'Citra Lestari',
                'email' => 'citra.demo@example.com',
                'asal_instansi' => 'Universitas Brawijaya',
                'bidang_magang' => 'Graphic Designer',
            ],
            [
                'nama' => 'Yoga Firmansyah',
                'email' => 'yoga.demo@example.com',
                'asal_instansi' => 'Politeknik Negeri Malang',
                'bidang_magang' => 'Frontend Developer',
            ],
            [
                'nama' => 'Intan Permata',
                'email' => 'intan.demo@example.com',
                'asal_instansi' => 'Universitas Jember',
                'bidang_magang' => 'UI/UX Designer',
            ],
            [
                'nama' => 'Farhan Akbar',
                'email' => 'farhan.demo@example.com',
                'asal_instansi' => 'Politeknik Negeri Jember',
                'bidang_magang' => 'Backend Developer',
            ],
            [
                'nama' => 'Dinda Maharani',
                'email' => 'dinda.demo@example.com',
                'asal_instansi' => 'Universitas Negeri Malang',
                'bidang_magang' => 'Content Creator',
            ],
            [
                'nama' => 'Arif Setiawan',
                'email' => 'arif.demo@example.com',
                'asal_instansi' => 'Universitas Brawijaya',
                'bidang_magang' => 'Frontend Developer',
            ],
            [
                'nama' => 'Melati Safitri',
                'email' => 'melati.demo@example.com',
                'asal_instansi' => 'Universitas Jember',
                'bidang_magang' => 'UI/UX Designer',
            ],
            [
                'nama' => 'Reza Maulana',
                'email' => 'reza.demo@example.com',
                'asal_instansi' => 'Politeknik Negeri Malang',
                'bidang_magang' => 'Backend Developer',
            ],
            [
                'nama' => 'Anisa Rahma',
                'email' => 'anisa.demo@example.com',
                'asal_instansi' => 'Politeknik Negeri Jember',
                'bidang_magang' => 'Quality Assurance',
            ],
            [
                'nama' => 'Kevin Andika',
                'email' => 'kevin.demo@example.com',
                'asal_instansi' => 'Universitas Brawijaya',
                'bidang_magang' => 'Frontend Developer',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 3. Buat akun peserta tanpa menghapus akun yang sudah ada
        |--------------------------------------------------------------------------
        */

        $daftarUser = [];

        foreach ($peserta as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nama' => $data['nama'],
                    'asal_instansi' => $data['asal_instansi'],
                    'bidang_magang' => $data['bidang_magang'],
                    'tanggal_mulai' => Carbon::today()->subDays(30)->toDateString(),
                    'tanggal_selesai' => Carbon::today()->addDays(60)->toDateString(),
                    'no_wa' => '08' . str_pad(
                        (string) random_int(100000000, 999999999),
                        9,
                        '0',
                        STR_PAD_LEFT
                    ),
                    'password' => Hash::make('Demo12345!'),
                    'role' => 'user',
                    'status_akun' => 'aktif',
                ]
            );

            $daftarUser[] = $user;
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Buat presensi untuk 5 hari terakhir, termasuk hari ini
        |--------------------------------------------------------------------------
        */

        foreach ($daftarUser as $indexUser => $user) {
            for ($hari = 0; $hari < 5; $hari++) {
                $tanggal = Carbon::today()
                    ->subDays($hari)
                    ->toDateString();

                // Variasi tipe kerja
                $wfo = (($indexUser + $hari) % 3 !== 0);
                $tipeKerja = $wfo ? 'WFO' : 'WFH';

                // Variasi jam dan status presensi
                $variasi = ($indexUser + $hari) % 4;

                if ($variasi === 0) {
                    $jam = '08:00:00';
                    $status = 'Tepat Waktu';
                    $poin = 10;
                } elseif ($variasi === 1) {
                    $jam = '09:15:00';
                    $status = 'Tepat Waktu';
                    $poin = 10;
                } elseif ($variasi === 2) {
                    $jam = '10:30:00';
                    $status = 'Terlambat';
                    $poin = 5;
                } else {
                    $jam = '11:15:00';
                    $status = 'Terlambat';
                    $poin = 5;
                }

                // Sebagian presensi terlambat menunggu konfirmasi
                $konfirmasi = (
                    $status === 'Terlambat'
                    && (($indexUser + $hari) % 2 === 0)
                ) ? 'Diajukan' : '-';

                // Variasi lokasi WFO
                $namaLokasi = ($indexUser % 2 === 0)
                    ? 'Malang Creative Center'
                    : 'Kolektive Space';

                $idLokasi = $wfo
                    ? ($lokasiKantor[$namaLokasi] ?? null)
                    : null;

                if ($wfo && $namaLokasi === 'Malang Creative Center') {
                    $latitude = -7.94082471;
                    $longitude = 112.64241174;
                } elseif ($wfo) {
                    $latitude = -7.94858616;
                    $longitude = 112.64314358;
                } else {
                    // Koordinat contoh untuk presensi WFH
                    $latitude = -7.95000000;
                    $longitude = 112.63000000;
                }

                // Hindari presensi tanggal mendatang atau akhir pekan.
                // Hari akhir pekan dilewati agar data lebih realistis.
                $tanggalCarbon = Carbon::parse($tanggal);

                if ($tanggalCarbon->isWeekend()) {
                    continue;
                }

                Presensi::updateOrCreate(
                    [
                        'id_user' => $user->getKey(),
                        'tanggal' => $tanggal,
                    ],
                    [
                        'jam_presensi' => $jam,
                        'tipe_kerja' => $tipeKerja,
                        'id_lokasi' => $idLokasi,
                        'latitude_user' => $latitude,
                        'longitude_user' => $longitude,
                        'jarak_dari_kantor' => $wfo ? 10 : null,
                        'progress_hari_ini' => 'Mengerjakan tugas magang',
                        'status_presensi' => $status,
                        'status_konfirmasi' => $konfirmasi,
                        'id_admin_konfirmasi' => null,
                        'catatan_admin' => null,
                        'point_didapat' => $poin,
                    ]
                );
            }
        }

        $this->command?->info(
            'Seeder selesai. 20 akun dummy dipastikan tersedia; presensi dibuat untuk hari kerja dalam 5 hari terakhir.'
        );

        $this->command?->info(
            'Password akun dummy baru: Demo12345!'
        );
    }
}
