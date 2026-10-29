<?php

namespace Tests\Feature;

use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserHistoryTest extends TestCase
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

    public function test_history_endpoint_returns_real_user_attendance_and_inferred_workday_absences(): void
    {
        $user = $this->createUser('peserta@example.com');
        $otherUser = $this->createUser('lain@example.com');

        $this->createPresensi($user, '2026-10-27');
        $this->createPresensi($otherUser, '2026-10-26');

        $response = $this->actingAs($user)->getJson('/presensi/riwayat-saya');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.tanggal_mulai', '2026-10-26')
            ->assertJsonPath('meta.tanggal_selesai', '2026-10-28')
            ->assertJsonPath('data.0.tanggal', '2026-10-28')
            ->assertJsonPath('data.0.status', 'alpha')
            ->assertJsonPath('data.0.tercatat', false)
            ->assertJsonPath('data.1.tanggal', '2026-10-27')
            ->assertJsonPath('data.1.status', 'tepat')
            ->assertJsonPath('data.1.progress', 'Mengerjakan tugas.')
            ->assertJsonPath('data.2.tanggal', '2026-10-26')
            ->assertJsonPath('data.2.status', 'alpha')
            ->assertJsonCount(3, 'data');
    }

    public function test_history_page_loads_the_authenticated_user_history_endpoint(): void
    {
        $user = $this->createUser('peserta@example.com');

        $this->actingAs($user)
            ->get('/history-test')
            ->assertOk()
            ->assertSee('/presensi/riwayat-saya', false)
            ->assertSee('Persentase dihitung dari hari kerja', false);
    }

    private function createUser(string $email): User
    {
        return User::create([
            'nama' => 'Peserta Magang',
            'asal_instansi' => 'Universitas Contoh',
            'bidang_magang' => 'Backend Developer',
            'tanggal_mulai' => '2026-10-26',
            'tanggal_selesai' => '2026-10-30',
            'no_wa' => '081234567890',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'user',
            'status_akun' => 'aktif',
        ]);
    }

    private function createPresensi(User $user, string $tanggal): Presensi
    {
        return Presensi::create([
            'id_user' => $user->getKey(),
            'tanggal' => $tanggal,
            'jam_presensi' => '09:00:00',
            'tipe_kerja' => 'WFH',
            'latitude_user' => 0,
            'longitude_user' => 0,
            'progress_hari_ini' => 'Mengerjakan tugas.',
            'status_presensi' => 'Tepat Waktu',
            'status_konfirmasi' => '-',
            'point_didapat' => 10,
        ]);
    }
}
