<?php

namespace Database\Seeders;

use App\Models\RiwayatImport;
use Illuminate\Database\Seeder;

class RiwayatImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RiwayatImport::create([
            'periode_data'  => 'Periode #2',
            'nama_berkas'   => 'ats_sigi_gumbasa_val.csv',
            'data_sukses'   => 1,
            'data_duplikat' => 0,
            'status'        => 'Selesai',
            'created_at'    => '2026-07-20 14:15:00',
        ]);

        RiwayatImport::create([
            'periode_data'  => 'Periode #1',
            'nama_berkas'   => 'ats_provinsi_sulteng_semester_1.xlsx',
            'data_sukses'   => 6,
            'data_duplikat' => 1,
            'status'        => 'Selesai',
            'created_at'    => '2026-06-15 09:30:00',
        ]);
    }
}
