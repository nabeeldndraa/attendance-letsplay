<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'id_user',
        'tanggal',
        'jenis',
        'keterangan',
        'status',
        'id_admin_konfirmasi',
        'catatan_admin',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function adminKonfirmasi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin_konfirmasi');
    }
}
