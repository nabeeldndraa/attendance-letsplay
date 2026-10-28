<?php

namespace Tests\Feature;

use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-28 11:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_dashboard_displays_real_attendance_statistics_for_the_authenticated_user(): void
    {
        $user = $this->createUser();
        $this->createPresensi($user, '2026-10-26', 'Tepat Waktu', 10);
        $this->createPresensi($user, '2026-10-27', 'Terlambat', 5);
        $this->createPresensi($user, '2026-10-23', 'Tepat Waktu', 50);

        $response = $this->actingAs($user)->get('/dashboard-test');

        $response->assertOk()
            ->assertSee('33% dari hari kerja')
            ->assertViewHas('stat', function (array $stat): bool {
                return $stat['poin'] === 15
                    && $stat['poin_kemarin'] === 5
                    && $stat['hadir'] === 2
                    && $stat['hari_kerja'] === 3
                    && $stat['tepat'] === 1
                    && $stat['terlambat'] === 1
                    && $stat['alpha'] === 1;
            })
            ->assertViewHas('last', function (?array $last): bool {
                return $last !== null
                    && $last['status'] === 'Terlambat'
                    && $last['tanggal'] === '27'
                    && $last['poin'] === 5;
            });
    }

    public function test_dashboard_shows_an_empty_state_when_the_user_has_no_attendance(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/dashboard-test')
            ->assertOk()
            ->assertSee('Belum ada presensi tercatat.')
            ->assertViewHas('last', null);
    }

    private function createUser(): User
    {
        return User::create([
            'nama' => 'Peserta Magang',
            'asal_instansi' => 'Universitas Contoh',
            'bidang_magang' => 'Backend Developer',
            'tanggal_mulai' => '2026-10-26',
            'tanggal_selesai' => '2026-10-30',
            'no_wa' => '081234567890',
            'email' => 'peserta@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status_akun' => 'aktif',
        ]);
    }

    private function createPresensi(
        User $user,
        string $tanggal,
        string $status,
        int $poin
    ): Presensi {
        return Presensi::create([
            'id_user' => $user->getKey(),
            'tanggal' => $tanggal,
            'jam_presensi' => '09:00:00',
            'tipe_kerja' => 'WFH',
            'latitude_user' => 0,
            'longitude_user' => 0,
            'progress_hari_ini' => 'Mengerjakan tugas.',
            'status_presensi' => $status,
            'status_konfirmasi' => '-',
            'point_didapat' => $poin,
        ]);
    }
}
