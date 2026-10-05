<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPoin extends Model
{
    protected $table = 'riwayat_poin';

    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_user',
        'id_presensi',
        'poin',
        'keterangan',
        'periode_bulan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function presensi(): BelongsTo
    {
        return $this->belongsTo(Presensi::class, 'id_presensi', 'id_presensi');
    }
}