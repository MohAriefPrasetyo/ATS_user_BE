<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnakTidakSekolahSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/ATS_fix.csv'); // Sesuaikan letak file CSV Anda

        if (($handle = fopen($path, 'r')) !== FALSE) {
            $header = fgetcsv($handle); // Ambil baris pertama sebagai nama kolom

            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($header) === count($row)) {
                    $data = array_combine($header, $row);

                    // Masukkan data ke tabel yang sudah ada
                    DB::table('anak_tidak_sekolah')->insert($data);
                }
            }
            fclose($handle);
        }
    }
}

