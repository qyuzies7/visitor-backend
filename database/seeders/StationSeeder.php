<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stations')->insert([
            [
                'station_name' => 'Stasiun Lempuyangan',
                'station_code' => 'LPN',
                'address' => 'Jl. Lempuyangan, Yogyakarta',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'station_name' => 'Stasiun Yogyakarta',
                'station_code' => 'YK',
                'address' => 'Jl. Margo Utomo, Yogyakarta',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'station_name' => 'Stasiun Klaten',
                'station_code' => 'KT',
                'address' => 'Jl. Stasiun, Klaten',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'station_name' => 'Stasiun Purwosari',
                'station_code' => 'PWS',
                'address' => 'Jl. Slamet Riyadi, Surakarta',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'station_name' => 'Stasiun Solo Balapan',
                'station_code' => 'SLO',
                'address' => 'Jl. Wolter Monginsidi, Surakarta',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'station_name' => 'Stasiun Solo Jebres',
                'station_code' => 'SK',
                'address' => 'Jl. Ledoksari, Surakarta',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
