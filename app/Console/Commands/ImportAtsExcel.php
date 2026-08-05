<?php

namespace App\Console\Commands;

use App\Imports\AnakTidakSekolahImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportAtsExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ats:import {file : Path ke file Excel/CSV (.xlsx, .xls, .csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengimpor data Excel Anak Tidak Sekolah (43 Kolom) langsung dari terminal CLI';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan di path: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Memulai proses impor data ATS dari file: {$filePath}...");

        try {
            Excel::import(new AnakTidakSekolahImport, $filePath);
            $count = \App\Models\AnakTidakSekolah::count();
            $this->info("Berhasil! Total {$count} data Anak Tidak Sekolah saat ini tersimpan di database.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Gagal mengimpor file: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
