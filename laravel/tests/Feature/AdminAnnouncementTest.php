<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_an_announcement_and_notify_active_participants(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $participant = $this->createUser('peserta@example.com');
        $inactiveParticipant = $this->createUser('nonaktif@example.com', 'user', 'nonaktif');
        $otherAdmin = $this->createUser('admin-lain@example.com', 'admin');

        $this->actingAs($admin)
            ->post(route('admin.pengumuman.store'), [
                'isi' => 'Jadwal meeting mingguan dimulai pukul 09.00.',
            ])
            ->assertRedirect(route('admin.pengumuman'))
            ->assertSessionHas('success');

        $announcement = Announcement::firstOrFail();
        $this->assertSame(
            'Jadwal meeting mingguan dimulai pukul 09.00.',
            $announcement->judul
        );
        $this->assertSame($admin->getKey(), $announcement->id_admin);
        $this->assertSame('terbit', $announcement->status);

        $this->assertDatabaseHas('user_notifications', [
            'id_user' => $participant->getKey(),
            'kategori' => 'pengumuman',
            'judul' => $announcement->judul,
            'isi' => $announcement->isi,
        ]);
        $this->assertDatabaseMissing('user_notifications', [
            'id_user' => $inactiveParticipant->getKey(),
            'kategori' => 'pengumuman',
        ]);
        $this->assertDatabaseMissing('user_notifications', [
            'id_user' => $otherAdmin->getKey(),
            'kategori' => 'pengumuman',
        ]);
    }

    public function test_announcement_list_searches_orders_and_shows_persisted_announcements(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');
        $older = Announcement::create([
            'id_admin' => $admin->getKey(),
            'judul' => 'Info kegiatan',
            'isi' => 'Kegiatan lama untuk peserta.',
            'status' => 'terbit',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $newer = Announcement::create([
            'id_admin' => $admin->getKey(),
            'judul' => 'Perubahan jadwal',
            'isi' => 'Jadwal terbaru untuk peserta.',
            'status' => 'terbit',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pengumuman', ['q' => 'peserta', 'urut' => 'terlama']))
            ->assertOk()
            ->assertSeeText($older->judul)
            ->assertSeeText($newer->judul)
            ->assertSeeInOrder([$older->judul, $newer->judul])
            ->assertSeeText('Total Pengumuman')
            ->assertSee(route('admin.pengumuman.store'));
    }

    public function test_only_admins_can_access_announcement_management(): void
    {
        $participant = $this->createUser('peserta@example.com');

        $this->actingAs($participant)
            ->get(route('admin.pengumuman'))
            ->assertForbidden();

        $this->actingAs($participant)
            ->post(route('admin.pengumuman.store'), ['isi' => 'Pengumuman.'])
            ->assertForbidden();

        $this->assertDatabaseCount('announcements', 0);
    }

    public function test_announcement_content_is_required(): void
    {
        $admin = $this->createUser('admin@example.com', 'admin');

        $this->actingAs($admin)
            ->from(route('admin.pengumuman'))
            ->post(route('admin.pengumuman.store'), ['isi' => '   '])
            ->assertSessionHasErrors('isi');

        $this->assertDatabaseCount('announcements', 0);
    }

    private function createUser(
        string $email,
        string $role = 'user',
        string $status = 'aktif'
    ): User {
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
            'status_akun' => $status,
        ]);
    }
}
