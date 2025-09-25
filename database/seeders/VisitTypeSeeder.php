<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('visit_types')->insert([
            [
                'type_name' => 'Reguler',
                'max_duration_days' => 1,
                'description' => 'Kunjungan reguler, maksimal 1 hari',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type_name' => 'VIP',
                'max_duration_days' => 3,
                'description' => 'Visitor VIP, 1-3 hari',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type_name' => 'Vendor/Kontraktor',
                'max_duration_days' => 365,
                'description' => 'Vendor/Kontraktor, 1 bulan - 1 tahun',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type_name' => 'Pelajar/Magang',
                'max_duration_days' => 356,
                'description' => 'Pelajar/Magang, sesuai kegiatan',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'type_name' => 'Darurat/Inspeksi',
                'max_duration_days' => 3,
                'description' => 'Darurat/Inspeksi, 1-3 hari',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
