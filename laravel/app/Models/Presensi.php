<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $primaryKey = 'id_presensi';

    protected $fillable = [
        'id_user',
        'tanggal',
        'jam_presensi',
        'tipe_kerja',
        'id_lokasi',
        'latitude_user',
        'longitude_user',
        'jarak_dari_kantor',
        'progress_hari_ini',
        'status_presensi',
        'status_konfirmasi',
        'id_admin_konfirmasi',
        'catatan_admin',
        'point_didapat',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function lokasiKantor(): BelongsTo
    {
        return $this->belongsTo(LokasiKantor::class, 'id_lokasi', 'id_lokasi');
    }

    public function adminKonfirmasi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin_konfirmasi', 'id');
    }

    public function riwayatPoin(): HasMany
    {
        return $this->hasMany(RiwayatPoin::class, 'id_presensi', 'id_presensi');
    }
}
