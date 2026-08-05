<?php

namespace Database\Seeders;

use App\Imports\AnakTidakSekolahImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class AnakTidakSekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('ATS_fix.xlsx');

        if (!File::exists($filePath)) {
            $this->command->error("File ATS_fix.xlsx tidak ditemukan di root project!");
            return;
        }

        $this->command->info("Mengimpor data dari ATS_fix.xlsx ke database...");

        try {
            Excel::import(new AnakTidakSekolahImport, $filePath);
            $this->command->info("Berhasil mengimpor data ATS_fix.xlsx ke tabel anak_tidak_sekolah!");
        } catch (\Exception $e) {
            $this->command->error("Gagal mengimpor data: " . $e->getMessage());
        }
    }
}
