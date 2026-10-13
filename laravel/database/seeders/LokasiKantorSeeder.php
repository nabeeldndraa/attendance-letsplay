<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LokasiKantor;

class LokasiKantorSeeder extends Seeder
{
    public function run(): void
    {
        LokasiKantor::create([
            'nama_lokasi' => 'Kolektive Space',
            'latitude' => -7.94858616,
            'longitude' => 112.64314358,
            'radius_meter' => 100,
        ]);

        LokasiKantor::create([
            'nama_lokasi' => 'Malang Creative Center',
            'latitude' => -7.94082471,
            'longitude' => 112.64241174,
            'radius_meter' => 100,
        ]);
    }
}