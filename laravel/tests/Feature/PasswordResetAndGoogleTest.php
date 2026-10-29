<?php

namespace Tests\Feature;

use App\Mail\PasswordResetCode;
use App\Models\PasswordResetCode as PasswordResetCodeModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\PendingMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class PasswordResetAndGoogleTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_code_is_emailed_hashed_expires_and_can_only_be_used_once(): void
    {
        Mail::fake();
        $user = $this->createUser();
        $code = null;

        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('KIRIM KODE VERIFIKASI');

        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect(route('password.verify'))
            ->assertSessionHas('success');

        Mail::assertSent(PasswordResetCode::class, function (PasswordResetCode $mail) use (&$code): bool {
            $code = $mail->code;

            return $mail->hasTo('peserta@example.com');
        });

        $this->assertNotNull($code);
        $resetCode = PasswordResetCodeModel::query()->where('email', $user->email)->firstOrFail();
        $this->assertNotSame($code, $resetCode->code_hash);
        $this->assertTrue(Hash::check($code, $resetCode->code_hash));
        $this->assertTrue($resetCode->expires_at->isFuture());

        $this->get('/forgot-password/verify')
            ->assertOk()
            ->assertSee('name="email" value="peserta@example.com"', false);

        $this->post('/forgot-password/reset', [
            'email' => $user->email,
            'code' => $code,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_codes', ['email' => $user->email]);
    }

    public function test_invalid_password_reset_code_is_limited_and_expired_codes_are_rejected(): void
    {
        $user = $this->createUser();
        $resetCode = PasswordResetCodeModel::create([
            'email' => $user->email,
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->subMinute(),
        ]);

        $this->post('/forgot-password/reset', [
            'email' => $user->email,
            'code' => '123456',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('code');

        $this->assertDatabaseHas('password_reset_codes', [
            'id' => $resetCode->id,
            'attempts' => 0,
        ]);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));

        $resetCode->update(['expires_at' => now()->addMinutes(10)]);

        $this->post('/forgot-password/reset', [
            'email' => $user->email,
            'code' => '000000',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('code');

        $this->assertDatabaseHas('password_reset_codes', [
            'id' => $resetCode->id,
            'attempts' => 1,
        ]);

        foreach (range(1, 4) as $attempt) {
            $this->post('/forgot-password/reset', [
                'email' => $user->email,
                'code' => '000000',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])->assertSessionHasErrors('code');
        }

        $this->post('/forgot-password/reset', [
            'email' => $user->email,
            'code' => '123456',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('code');

        $this->assertDatabaseHas('password_reset_codes', [
            'id' => $resetCode->id,
            'attempts' => 5,
        ]);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_reset_request_does_not_disclose_unknown_emails(): void
    {
        Mail::fake();

        $this->post('/forgot-password', ['email' => 'unknown@example.com'])
            ->assertRedirect(route('password.verify'))
            ->assertSessionHas('success', 'Jika email terdaftar, kode verifikasi telah dikirim.');

        Mail::assertNothingSent();
        $this->assertDatabaseCount('password_reset_codes', 0);
    }

    public function test_smtp_failure_returns_a_friendly_error_and_removes_the_unsent_code(): void
    {
        $user = $this->createUser();
        $pendingMail = \Mockery::mock(PendingMail::class);
        $pendingMail->shouldReceive('send')
            ->once()
            ->andThrow(new TransportException('SMTP server disconnected.'));

        Mail::shouldReceive('to')
            ->once()
            ->with($user->email)
            ->andReturn($pendingMail);

        $this->from('/forgot-password')->post(
            '/forgot-password',
            ['email' => $user->email]
        )
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('error', 'Kode belum bisa dikirim. Coba lagi beberapa saat, atau hubungi administrator.');

        $this->assertDatabaseMissing('password_reset_codes', [
            'email' => $user->email,
        ]);
    }

    public function test_google_login_authenticates_only_verified_existing_accounts(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $user = $this->createUser();
        $googleUser = GoogleUser::fake([
            'email' => 'PESERTA@example.com',
            'verified_email' => true,
        ]);
        $provider = \Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect('/dashboard-test');

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
    }

    public function test_google_login_does_not_create_accounts_for_unknown_or_unverified_emails(): void
    {
        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $user = $this->createUser();
        $unverifiedProvider = \Mockery::mock(Provider::class);
        $unverifiedProvider->shouldReceive('user')->once()->andReturn(GoogleUser::fake([
            'email' => $user->email,
            'verified_email' => false,
        ]));
        $unknownProvider = \Mockery::mock(Provider::class);
        $unknownProvider->shouldReceive('user')->once()->andReturn(GoogleUser::fake([
            'email' => 'unknown@example.com',
            'verified_email' => true,
        ]));

        Socialite::shouldReceive('driver')
            ->twice()
            ->with('google')
            ->andReturn($unverifiedProvider, $unknownProvider);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_google_login_reports_missing_oauth_configuration(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
            'services.google.redirect' => null,
        ]);

        $this->get('/auth/google')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');
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
