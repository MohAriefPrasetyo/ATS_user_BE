<?php

namespace App\Services;

class SchoolLookupService
{
    private static ?array $schoolMap = null;

    /**
     * Memuat dan meng-cache master nama sekolah ke memory (O(1) lookup).
     */
    public static function getSchoolMap(): array
    {
        if (self::$schoolMap !== null) {
            return self::$schoolMap;
        }

        self::$schoolMap = [];

        // 1. Muat dari hasil_nama_sekolah.csv
        $file1 = storage_path('app/hasil_nama_sekolah.csv');
        if (file_exists($file1)) {
            if (($handle = fopen($file1, 'r')) !== false) {
                fgetcsv($handle, 0, ',', '"', '\\'); // skip header
                while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                    if (isset($data[0], $data[1])) {
                        $id = strtolower(trim($data[0]));
                        $name = trim($data[1], " \t\n\r\0\x0B\"'");
                        if ($id !== '' && $name !== '') {
                            self::$schoolMap[$id] = $name;
                        }
                    }
                }
                fclose($handle);
            }
        }

        // 2. Muat dari backbone_sekolah_filtered.csv (11.644 sekolah)
        $file2 = storage_path('app/backbone_sekolah_filtered.csv');
        if (file_exists($file2)) {
            if (($handle = fopen($file2, 'r')) !== false) {
                fgetcsv($handle, 0, ',', '"', '\\'); // skip header
                while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                    // format: sekolah_id, desa_kelurahan, kecamatan, kabupaten, nama
                    if (isset($data[0], $data[4])) {
                        $id = strtolower(trim($data[0]));
                        $name = trim($data[4], " \t\n\r\0\x0B\"'");
                        if ($id !== '' && $name !== '') {
                            // prioritaskan jika belum ada atau update dengan nama resmi
                            self::$schoolMap[$id] = $name;
                        }
                    }
                }
                fclose($handle);
            }
        }

        return self::$schoolMap;
    }

    /**
     * Deteksi kategori sekolah tunggal berdasarkan teks nama sekolah.
     */
    public static function detectKategori(?string $schoolName): string
    {
        if (empty($schoolName) || trim($schoolName) === '-' || trim($schoolName) === '') {
            return 'Non-Sekolah';
        }

        $upper = strtoupper(trim($schoolName));

        // 1. SMK
        if (preg_match('/\b(SMK|SMKN|KEJURUAN)\b/', $upper)) {
            return 'SMK';
        }

        // 2. MA (Madrasah Aliyah)
        if (preg_match('/\b(MAN|MAS|MA|MADRASAH ALIYAH)\b/', $upper)) {
            return 'MA';
        }

        // 3. SMA
        if (preg_match('/\b(SMA|SMAN)\b/', $upper)) {
            return 'SMA';
        }

        // 4. MTs (Madrasah Tsanawiyah)
        if (preg_match('/\b(MTS|MTSN|MADRASAH TSANAWIYAH)\b/', $upper)) {
            return 'MTs';
        }

        // 5. SMP
        if (preg_match('/\b(SMP|SMPN)\b/', $upper)) {
            return 'SMP';
        }

        // 6. MI (Madrasah Ibtidaiyah)
        if (preg_match('/\b(MIN|MIS|MI|MADRASAH IBTIDAIYAH)\b/', $upper)) {
            return 'MI';
        }

        // 7. SD
        if (preg_match('/\b(SD|SDN|INPRES)\b/', $upper)) {
            return 'SD';
        }

        // 8. SLB
        if (preg_match('/\b(SLB|SDLB|SMPLB|SMALB|LUAR BIASA)\b/', $upper)) {
            return 'SLB';
        }

        // 9. PKBM
        if (preg_match('/\b(PKBM|PAKET)\b/', $upper)) {
            return 'PKBM';
        }

        // 10. PAUD / TK
        if (preg_match('/\b(PAUD|TK|TKN|RA|KB|KELOMPOK BERMAIN)\b/', $upper)) {
            return 'PAUD/TK';
        }

        return 'Lainnya';
    }

    /**
     * Resolve nama_sekolah dan kategori_sekolah dari sekolah_id atau nama input.
     *
     * @return array{nama_sekolah: ?string, kategori_sekolah: string}
     */
    public static function resolve(?string $sekolahId, ?string $manualNamaSekolah = null): array
    {
        $cleanId = $sekolahId ? strtolower(trim($sekolahId)) : null;
        $name = null;

        if ($cleanId && $cleanId !== 'null' && $cleanId !== '-') {
            $map = self::getSchoolMap();
            if (isset($map[$cleanId])) {
                $name = $map[$cleanId];
            }
        }

        if (empty($name) && !empty($manualNamaSekolah) && trim($manualNamaSekolah) !== '-') {
            $name = trim($manualNamaSekolah);
        }

        if (empty($name)) {
            return [
                'nama_sekolah'     => null,
                'kategori_sekolah' => 'Non-Sekolah',
            ];
        }

        return [
            'nama_sekolah'     => $name,
            'kategori_sekolah' => self::detectKategori($name),
        ];
    }
}
