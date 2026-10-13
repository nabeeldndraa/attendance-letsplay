<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'asal_instansi',
        'bidang_magang',
        'tanggal_mulai',
        'tanggal_selesai',
        'no_wa',
        'email',
        'password',
        'role',
        'status_akun',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'id_user', 'id');
    }

    public function riwayatPoin(): HasMany
    {
        return $this->hasMany(RiwayatPoin::class, 'id_user', 'id');
    }

    public function presensiDikonfirmasi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'id_admin_konfirmasi', 'id');
    }
}
