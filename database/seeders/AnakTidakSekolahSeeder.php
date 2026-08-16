<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnakTidakSekolahSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/ATS_fix.csv');

        if (!file_exists($path)) {
            return;
        }

        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle);

            if (!$header) {
                fclose($handle);
                return;
            }

            // Normalisasi nama kolom header CSV
            $cleanHeader = array_map(function ($key) {
                $trimmed = trim($key);
                if (strtolower($trimmed) === 'unnamed: 42' || str_contains($trimmed, 'Unnamed')) {
                    return 'unnamed_42';
                }
                return strtolower(str_replace([':', ' '], ['_', '_'], $trimmed));
            }, $header);

            $dateFields = ['tanggal_lahir'];
            $dateTimeFields = ['create_date', 'last_update', 'soft_delete_ats'];
            $numberFields = ['nik', 'no_kk', 'nisn'];

            while (($row = fgetcsv($handle)) !== false) {
                if (count($cleanHeader) === count($row)) {
                    $data = array_combine($cleanHeader, $row);

                    $cleanedData = [];
                    foreach ($data as $k => $v) {
                        $vStr = is_string($v) ? trim($v) : $v;

                        // Penanganan khusus NIK, NO_KK, NISN (Konversi Notasi Ilmiah Eksponensial Excel)
                        if (in_array($k, $numberFields, true)) {
                            $cleanedData[$k] = $this->cleanScientificNumber($vStr);
                            continue;
                        }

                        // Penanganan khusus untuk kolom Tanggal
                        if (in_array($k, $dateFields, true)) {
                            $cleanedData[$k] = $this->parseDate($vStr);
                            continue;
                        }

                        // Penanganan khusus untuk kolom DateTime/Timestamp
                        if (in_array($k, $dateTimeFields, true)) {
                            $cleanedData[$k] = $this->parseDateTime($vStr);
                            continue;
                        }

                        // Kolom Aktif (Boolean)
                        if ($k === 'aktif') {
                            $cleanedData[$k] = ($vStr === '1' || $vStr === 'true' || $vStr === 1) ? 1 : 0;
                            continue;
                        }

                        // String biasa / Kosong ke NULL
                        $cleanedData[$k] = ($vStr === '' || $vStr === null) ? null : $vStr;
                    }

                    // Tambahkan timestamp Laravel jika belum ada
                    $cleanedData['created_at'] = now();
                    $cleanedData['updated_at'] = now();

                    DB::table('anak_tidak_sekolah')->insert($cleanedData);
                }
            }
            fclose($handle);
        }
    }

    private function cleanScientificNumber($val): ?string
    {
        if ($val === null) return null;
        $vStr = trim((string)$val);
        if ($vStr === '' || $vStr === '0' || $vStr === '-' || strtolower($vStr) === 'null') {
            return null;
        }
        // Jika format eksponensial ilmiah (seperti 9.10301240106E+015)
        if (stripos($vStr, 'e+') !== false || stripos($vStr, 'e-') !== false) {
            return sprintf('%.0f', (float)$vStr);
        }
        return $vStr;
    }

    private function parseDate($val): ?string
    {
        if ($val === null) return null;
        $vStr = trim((string)$val);
        if ($vStr === '' || $vStr === '0' || $vStr === '0.0' || $vStr === '-' || strtolower($vStr) === 'null') {
            return null;
        }
        try {
            return Carbon::parse($vStr)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseDateTime($val): ?string
    {
        if ($val === null) return null;
        $vStr = trim((string)$val);
        if ($vStr === '' || $vStr === '0' || $vStr === '0.0' || $vStr === '-' || strtolower($vStr) === 'null') {
            return null;
        }
        try {
            return Carbon::parse($vStr)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
