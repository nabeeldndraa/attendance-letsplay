<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LokasiKantor extends Model
{
    protected $table = 'lokasi_kantor';

    protected $primaryKey = 'id_lokasi';

    protected $fillable = [
        'nama_lokasi',
        'latitude',
        'longitude',
        'radius_meter',
    ];

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'id_lokasi', 'id_lokasi');
    }
}