<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\LeaveRequest;
use App\Models\Presensi;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-10-28 11:15:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_late_attendance_creates_an_actionable_admin_notification(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $participant = $this->createUser('peserta@example.com');

        $this->actingAs($participant)
            ->postJson('/presensi', [
                'tipe_kerja' => 'WFH',
                'latitude_user' => 0,
                'longitude_user' => 0,
                'progress_hari_ini' => 'Mengerjakan laporan.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status_konfirmasi', 'Diajukan');

        $notification = AdminNotification::where('id_admin', $admin->getKey())
            ->where('kategori', 'keterlambatan')
            ->firstOrFail();

        $this->assertSame('Diajukan', $notification->status);
        $this->assertSame('presensi', $notification->sumber_tipe);

        $this->actingAs($admin)
            ->getJson(route('admin.notifikasi.data'))
            ->assertOk()
            ->assertJsonPath('data.0.kategori', 'keterlambatan')
            ->assertJsonPath('data.0.perlu_keputusan', true)
            ->assertJsonPath('data.0.status', 'menunggu');
    }

    public function test_admin_can_approve_or_reject_a_late_attendance_from_its_notification(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $participant = $this->createUser('peserta@example.com');
        $presensi = $this->createPendingPresensi($participant);
        $notification = $this->createAdminNotification($admin, 'keterlambatan', 'presensi', $presensi->getKey());

        $this->actingAs($admin)
            ->postJson(route('admin.notifikasi.keputusan', [
                'id' => $notification->getKey(),
                'action' => 'setujui',
            ]))
            ->assertOk()
            ->assertJsonPath('status', 'Disetujui');

        $this->assertSame('Disetujui', $presensi->fresh()->status_konfirmasi);
        $this->assertSame('Tepat Waktu', $presensi->fresh()->status_presensi);
        $this->assertSame(10, $presensi->fresh()->point_didapat);
        $this->assertSame('Disetujui', $notification->fresh()->status);
        $this->assertDatabaseHas('user_notifications', [
            'id_user' => $participant->getKey(),
            'judul' => 'Presensi Disetujui',
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'id_user' => $participant->getKey(),
            'judul' => 'Poin Bertambah',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.notifikasi.keputusan', [
                'id' => $notification->getKey(),
                'action' => 'tolak',
            ]))
            ->assertStatus(409);

        $this->assertDatabaseCount('user_notifications', 2);
    }

    public function test_participant_can_submit_leave_and_admin_can_reject_it(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $participant = $this->createUser('peserta@example.com');

        $this->actingAs($participant)
            ->postJson('/presensi/izin', [
                'jenis' => 'sakit',
                'keterangan' => 'Demam dan perlu istirahat.',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $leaveRequest = LeaveRequest::firstOrFail();
        $notification = AdminNotification::where('id_admin', $admin->getKey())
            ->where('kategori', 'perizinan')
            ->firstOrFail();

        $this->assertSame('Diajukan', $leaveRequest->status);
        $this->assertSame('izin', $notification->sumber_tipe);
        $this->assertSame((string) $leaveRequest->getKey(), (string) $notification->sumber_id);

        $this->actingAs($participant)
            ->getJson('/presensi/status-hari-ini')
            ->assertOk()
            ->assertJsonPath('sudah_presensi', true)
            ->assertJsonPath('data.is_izin', true);

        $this->actingAs($admin)
            ->postJson(route('admin.notifikasi.keputusan', [
                'id' => $notification->getKey(),
                'action' => 'tolak',
            ]), ['catatan_admin' => 'Mohon lampirkan surat keterangan.'])
            ->assertOk()
            ->assertJsonPath('status', 'Ditolak');

        $this->assertSame('Ditolak', $leaveRequest->fresh()->status);
        $resultNotification = UserNotification::where('id_user', $participant->getKey())
            ->where('judul', 'Pengajuan Izin Ditolak')
            ->firstOrFail();
        $this->assertStringContainsString('Catatan admin:', $resultNotification->isi);
    }

    public function test_leave_request_cannot_be_submitted_after_attendance_or_more_than_once_per_day(): void
    {
        $participant = $this->createUser('peserta@example.com');
        $this->createPendingPresensi($participant)->update(['tanggal' => '2026-10-28']);

        $this->actingAs($participant)
            ->postJson('/presensi/izin', [
                'jenis' => 'izin',
                'keterangan' => 'Keperluan keluarga.',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('leave_requests', 0);

        $anotherParticipant = $this->createUser('peserta-lain@example.com');
        $payload = [
            'jenis' => 'izin',
            'keterangan' => 'Keperluan keluarga.',
        ];

        $this->actingAs($anotherParticipant)
            ->postJson('/presensi/izin', $payload)
            ->assertCreated();

        $this->actingAs($anotherParticipant)
            ->postJson('/presensi/izin', $payload)
            ->assertUnprocessable();

        $this->actingAs($anotherParticipant)
            ->postJson('/presensi', [
                'tipe_kerja' => 'WFH',
                'latitude_user' => 0,
                'longitude_user' => 0,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('leave_requests', 1);
    }

    public function test_admin_notifications_are_private_and_non_admins_cannot_decide_requests(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $otherAdmin = $this->createUser('admin-lain@example.com', 'admin');
        $participant = $this->createUser('peserta@example.com');
        $leaveRequest = LeaveRequest::create([
            'id_user' => $participant->getKey(),
            'tanggal' => '2026-10-28',
            'jenis' => 'izin',
            'keterangan' => 'Keperluan kampus.',
            'status' => 'Diajukan',
        ]);
        $notification = $this->createAdminNotification($admin, 'perizinan', 'izin', $leaveRequest->getKey());

        $this->actingAs($otherAdmin)
            ->getJson(route('admin.notifikasi.data'))
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->actingAs($participant)
            ->postJson(route('admin.notifikasi.keputusan', [
                'id' => $notification->getKey(),
                'action' => 'setujui',
            ]))
            ->assertForbidden();

        $this->assertSame('Diajukan', $leaveRequest->fresh()->status);
    }

    public function test_public_registration_creates_an_informational_account_notification(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');

        $this->post('/register', [
            'nama' => 'Peserta Baru',
            'asal_instansi' => 'Universitas Contoh',
            'bidang_magang' => 'Backend Developer',
            'tanggal_mulai' => '2026-10-26',
            'tanggal_selesai' => '2026-10-30',
            'no_wa' => '081234567890',
            'email' => 'peserta-baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/login-test');

        $this->assertDatabaseHas('admin_notifications', [
            'id_admin' => $admin->getKey(),
            'kategori' => 'akun',
            'judul' => 'Akun Intern Baru',
        ]);
    }

    public function test_admin_created_accounts_also_appear_in_the_account_category(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');

        $this->actingAs($admin)
            ->post(route('admin.data-user.store'), [
                'nama' => 'Peserta Baru',
                'asal_instansi' => 'Universitas Contoh',
                'bidang_magang' => 'Backend Developer',
                'tanggal_mulai' => '2026-10-26',
                'tanggal_selesai' => '2026-10-30',
                'no_wa' => '081234567890',
                'email' => 'peserta-baru@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.data-user'));

        $this->assertDatabaseHas('admin_notifications', [
            'id_admin' => $admin->getKey(),
            'kategori' => 'akun',
            'judul' => 'Akun Intern Baru',
        ]);
    }

    public function test_existing_attendance_confirmation_route_updates_admin_notification_status(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $participant = $this->createUser('peserta@example.com');
        $presensi = $this->createPendingPresensi($participant);
        $notification = $this->createAdminNotification($admin, 'keterlambatan', 'presensi', $presensi->getKey());

        $this->actingAs($admin)
            ->post('/admin/presensi/'.$presensi->getKey().'/konfirmasi', [
                'keputusan' => 'Ditolak',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('Ditolak', $notification->fresh()->status);
        $this->assertNotNull($notification->fresh()->dibaca_pada);
    }

    public function test_admin_can_mark_its_notifications_read_but_not_another_admins(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $otherAdmin = $this->createUser('admin-lain@example.com', 'admin');
        $participant = $this->createUser('peserta@example.com');
        $presensi = $this->createPendingPresensi($participant);
        $notification = $this->createAdminNotification($admin, 'keterlambatan', 'presensi', $presensi->getKey());
        $otherNotification = $this->createAdminNotification(
            $otherAdmin,
            'keterlambatan',
            'presensi',
            $presensi->getKey()
        );

        $this->actingAs($admin)
            ->postJson(route('admin.notifikasi.dibaca', $notification->getKey()))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertNotNull($notification->fresh()->dibaca_pada);
        $this->assertNull($otherNotification->fresh()->dibaca_pada);

        $this->actingAs($admin)
            ->postJson(route('admin.notifikasi.dibaca-semua'))
            ->assertOk();
        $this->assertNotNull($notification->fresh()->dibaca_pada);
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
            'status_presensi' => 'Terlambat',
            'status_konfirmasi' => 'Diajukan',
            'point_didapat' => 5,
        ]);
    }

    private function createAdminNotification(
        User $admin,
        string $category,
        string $sourceType,
        int $sourceId
    ): AdminNotification {
        return AdminNotification::create([
            'id_admin' => $admin->getKey(),
            'kategori' => $category,
            'judul' => 'Pengajuan menunggu',
            'deskripsi' => 'Permintaan peserta menunggu keputusan.',
            'sumber_tipe' => $sourceType,
            'sumber_id' => $sourceId,
            'status' => 'Diajukan',
        ]);
    }
}
