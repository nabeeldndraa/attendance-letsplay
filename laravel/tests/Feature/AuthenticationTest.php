<?php

namespace Tests\Feature;

use App\Models\RegistrationSetting;
use App\Models\User;
use Illuminate\Auth\SessionGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_public_registration_closes_future_registration(): void
    {
        $this->post('/register', $this->registrationData())
            ->assertRedirect('/login-test')
            ->assertSessionHas('success');

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('registration_settings', [
            'id' => 1,
        ]);
        $this->assertNotNull(RegistrationSetting::query()->findOrFail(1)->closed_at);

        $this->get('/login-test')
            ->assertOk()
            ->assertDontSee('Create Account')
            ->assertSee('Pendaftaran akun baru sudah ditutup.');

        $this->get('/register')
            ->assertRedirect('/login-test')
            ->assertSessionHas('error');

        $this->post('/register', $this->registrationData([
            'email' => 'another@example.com',
        ]))->assertRedirect('/login-test');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_remembers_the_user_when_remember_is_selected(): void
    {
        $user = $this->createUser();

        $this->get('/login-test')
            ->assertOk()
            ->assertSee('name="remember" value="1" checked', false);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect('/dashboard-test')
            ->assertCookie('remember_web_'.sha1(SessionGuard::class));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_authenticated_users_are_redirected_away_from_login_and_logout_clears_authentication(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get('/login-test')
            ->assertRedirect('/dashboard-test');

        $this->post('/logout')->assertRedirect('/login-test');

        $this->assertGuest();
    }

    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'nama' => 'Peserta Magang',
            'asal_instansi' => 'Universitas Contoh',
            'bidang_magang' => 'Backend Developer',
            'tanggal_mulai' => '2026-10-26',
            'tanggal_selesai' => '2026-10-30',
            'no_wa' => '081234567890',
            'email' => 'peserta@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
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
}
