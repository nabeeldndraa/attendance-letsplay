<?php

namespace Tests\Feature;

use App\Models\Presensi;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-28 09:15:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_notification_feed_and_unread_count_only_include_the_authenticated_user(): void
    {
        $user = $this->createUser('peserta@example.com');
        $otherUser = $this->createUser('lain@example.com');

        $notification = $this->createNotification($user);
        $this->createNotification($otherUser);

        $this->actingAs($user)
            ->getJson('/notifikasi/data')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $notification->getKey());

        $this->actingAs($user)
            ->getJson('/notifikasi/belum-dibaca')
            ->assertOk()
            ->assertJsonPath('unread_count', 1);
    }

    public function test_user_can_mark_their_notification_as_read_but_cannot_mark_another_users_notification(): void
    {
        $user = $this->createUser('peserta@example.com');
        $otherUser = $this->createUser('lain@example.com');
        $ownNotification = $this->createNotification($user);
        $otherNotification = $this->createNotification($otherUser);

        $this->actingAs($user)
            ->postJson('/notifikasi/'.$ownNotification->getKey().'/dibaca')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->actingAs($user)
            ->postJson('/notifikasi/'.$otherNotification->getKey().'/dibaca')
            ->assertNotFound();

        $this->assertNotNull($ownNotification->fresh()->dibaca_pada);
        $this->assertNull($otherNotification->fresh()->dibaca_pada);
    }

    public function test_successful_attendance_creates_attendance_and_points_notifications(): void
    {
        $user = $this->createUser('peserta@example.com');

        $this->actingAs($user)
            ->postJson('/presensi', [
                'tipe_kerja' => 'WFH',
                'latitude_user' => 0,
                'longitude_user' => 0,
                'progress_hari_ini' => 'Mengerjakan fitur notifikasi.',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('user_notifications', [
            'id_user' => $user->getKey(),
            'kategori' => 'presensi',
            'judul' => 'Presensi Berhasil',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'id_user' => $user->getKey(),
            'kategori' => 'poin',
            'judul' => 'Poin Bertambah',
        ]);
    }

    public function test_late_attendance_creates_a_late_attendance_notification(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-28 11:15:00', 'Asia/Jakarta'));
        $user = $this->createUser('peserta@example.com');

        $this->actingAs($user)
            ->postJson('/presensi', [
                'tipe_kerja' => 'WFH',
                'latitude_user' => 0,
                'longitude_user' => 0,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status_presensi', 'Terlambat');

        $this->assertDatabaseHas('user_notifications', [
            'id_user' => $user->getKey(),
            'kategori' => 'presensi',
            'judul' => 'Presensi Terlambat',
        ]);
    }

    public function test_user_can_mark_all_their_notifications_as_read(): void
    {
        $user = $this->createUser('peserta@example.com');
        $otherUser = $this->createUser('lain@example.com');
        $ownNotification = $this->createNotification($user);
        $otherNotification = $this->createNotification($otherUser);

        $this->actingAs($user)
            ->postJson('/notifikasi/dibaca-semua')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull($ownNotification->fresh()->dibaca_pada);
        $this->assertNull($otherNotification->fresh()->dibaca_pada);
    }

    public function test_admin_approval_notifies_the_user_of_the_decision_and_added_points(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $user = $this->createUser('peserta@example.com');
        $presensi = $this->createPendingPresensi($user);

        $this->actingAs($admin)
            ->post('/admin/presensi/'.$presensi->getKey().'/konfirmasi', [
                'keputusan' => 'Disetujui',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('user_notifications', [
            'id_user' => $user->getKey(),
            'kategori' => 'presensi',
            'judul' => 'Presensi Disetujui',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'id_user' => $user->getKey(),
            'kategori' => 'poin',
            'judul' => 'Poin Bertambah',
        ]);
    }

    private function createUser(string $email, string $role = 'user'): User
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
            'role' => $role,
            'status_akun' => 'aktif',
        ]);
    }

    private function createNotification(User $user): UserNotification
    {
        return UserNotification::create([
            'id_user' => $user->getKey(),
            'kategori' => 'presensi',
            'judul' => 'Presensi Berhasil',
            'isi' => 'Presensi berhasil dicatat.',
        ]);
    }

    private function createPendingPresensi(User $user): Presensi
    {
        return Presensi::create([
            'id_user' => $user->getKey(),
            'tanggal' => '2026-10-27',
            'jam_presensi' => '10:30:00',
            'tipe_kerja' => 'WFH',
            'latitude_user' => 0,
            'longitude_user' => 0,
            'progress_hari_ini' => 'Mengerjakan tugas.',
            'status_presensi' => 'Menunggu Konfirmasi',
            'status_konfirmasi' => 'Diajukan',
            'point_didapat' => 5,
        ]);
    }
}
