<?php

namespace App\Imports;

use App\Models\AnakTidakSekolah;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class AnakTidakSekolahImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    /**
     * Ambil nilai cell secara fleksibel: coba berdasarkan nama header (string key),
     * jika null/tidak ada, coba berdasarkan indeks kolom (0..42).
     */
    private function getValue(array $row, string $key, int $index)
    {
        if (array_key_exists($key, $row) && $row[$key] !== null && trim((string)$row[$key]) !== '') {
            return $row[$key];
        }
        if (array_key_exists($index, $row)) {
            return $row[$index];
        }
        return null;
    }

    private function cleanString($value)
    {
        if ($value === null) {
            return null;
        }
        $str = trim((string)$value);
        return $str === '' ? null : $str;
    }

    private function transformDate($value)
    {
        $clean = $this->cleanString($value);
        if ($clean === null || $clean === '0' || $clean === '-' || $clean === '0.0') {
            return null;
        }

        try {
            if (is_numeric($clean)) {
                $num = (float)$clean;
                if ($num <= 0) {
                    return null;
                }
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($num)->format('Y-m-d');
            }
            return Carbon::parse($clean)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function transformDateTime($value)
    {
        $clean = $this->cleanString($value);
        if ($clean === null || $clean === '0' || $clean === '-' || $clean === '0.0') {
            return null;
        }

        try {
            if (is_numeric($clean)) {
                $num = (float)$clean;
                if ($num <= 0) {
                    return null;
                }
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($num)->format('Y-m-d H:i:s');
            }
            return Carbon::parse($clean)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function model(array $row)
    {
        // Filter nilai yang tidak kosong
        $nonEmptyValues = array_filter($row, function ($v) {
            return $v !== null && trim((string)$v) !== '';
        });

        // Lewati jika seluruh baris kosong
        if (empty($nonEmptyValues)) {
            return null;
        }

        // Ambil nilai setiap kolom dengan dual-lookup (Header Name OR Index 0..42)
        $sekolah_id                 = $this->cleanString($this->getValue($row, 'sekolah_id', 0));
        $tahun                      = $this->cleanString($this->getValue($row, 'tahun', 1));
        $semester_id                = $this->cleanString($this->getValue($row, 'semester_id', 2));
        $peserta_didik_id           = $this->cleanString($this->getValue($row, 'peserta_didik_id', 3));
        $nisn                       = $this->cleanString($this->getValue($row, 'nisn', 4));
        $nik                        = $this->cleanString($this->getValue($row, 'nik', 5));
        $no_kk                      = $this->cleanString($this->getValue($row, 'no_kk', 6));
        $nama                       = $this->cleanString($this->getValue($row, 'nama', 7));
        $jenis_kelamin              = $this->cleanString($this->getValue($row, 'jenis_kelamin', 8));
        $tempat_lahir               = $this->cleanString($this->getValue($row, 'tempat_lahir', 9));
        $tanggal_lahir              = $this->transformDate($this->getValue($row, 'tanggal_lahir', 10));
        $nama_ibu_kandung           = $this->cleanString($this->getValue($row, 'nama_ibu_kandung', 11));
        $kode_provinsi              = $this->cleanString($this->getValue($row, 'kode_provinsi', 12));
        $provinsi                   = $this->cleanString($this->getValue($row, 'provinsi', 13));
        $kode_kabupaten             = $this->cleanString($this->getValue($row, 'kode_kabupaten', 14));
        $kabupaten                  = $this->cleanString($this->getValue($row, 'kabupaten', 15));
        $kode_kecamatan             = $this->cleanString($this->getValue($row, 'kode_kecamatan', 16));
        $kecamatan                  = $this->cleanString($this->getValue($row, 'kecamatan', 17));
        $kode_desa_kelurahan        = $this->cleanString($this->getValue($row, 'kode_desa_kelurahan', 18));
        $desa_kelurahan             = $this->cleanString($this->getValue($row, 'desa_kelurahan', 19));
        $kode_wilayah               = $this->cleanString($this->getValue($row, 'kode_wilayah', 20));
        $kode_dagri                 = $this->cleanString($this->getValue($row, 'kode_dagri', 21));
        $alamat_jalan               = $this->cleanString($this->getValue($row, 'alamat_jalan', 22));
        $rt                         = $this->cleanString($this->getValue($row, 'rt', 23));
        $rw                         = $this->cleanString($this->getValue($row, 'rw', 24));
        $lintang                    = $this->cleanString($this->getValue($row, 'lintang', 25));
        $bujur                      = $this->cleanString($this->getValue($row, 'bujur', 26));
        $status_approval            = $this->cleanString($this->getValue($row, 'status_approval', 27));
        $status_approval_keterangan = $this->cleanString($this->getValue($row, 'status_approval_keterangan', 28));
        $status_validasi            = $this->cleanString($this->getValue($row, 'status_validasi', 29));
        $status                     = $this->cleanString($this->getValue($row, 'status', 30));
        $keterangan_approval        = $this->cleanString($this->getValue($row, 'keterangan_approval', 31));
        $alasan_approval_id         = $this->cleanString($this->getValue($row, 'alasan_approval_id', 32));
        $alasan_approval_keterangan = $this->cleanString($this->getValue($row, 'alasan_approval_keterangan', 33));
        $keterangan_tolak           = $this->cleanString($this->getValue($row, 'keterangan_tolak', 34));
        $alasan_lainnya             = $this->cleanString($this->getValue($row, 'alasan_lainnya', 35));
        $tingkat_pendidikan         = $this->cleanString($this->getValue($row, 'tingkat_pendidikan', 36));
        $kebutuhan_khusus_id        = $this->cleanString($this->getValue($row, 'kebutuhan_khusus_id', 37));
        $aktif_val                  = $this->getValue($row, 'aktif', 38);
        $create_date                = $this->transformDateTime($this->getValue($row, 'create_date', 39));
        $last_update                = $this->transformDateTime($this->getValue($row, 'last_update', 40));
        $soft_delete_ats            = $this->transformDateTime($this->getValue($row, 'soft_delete_ats', 41));
        $unnamed_42                 = $this->cleanString($this->getValue($row, 'unnamed_42', 42));

        // Jika baris ini adalah baris header yang tak sengaja terbaca, abaikan
        if ($nama === 'nama' || $nik === 'nik' || $sekolah_id === 'sekolah_id') {
            return null;
        }

        $now = now();

        return new AnakTidakSekolah([
            'sekolah_id'                 => $sekolah_id,
            'tahun'                      => $tahun,
            'semester_id'                => $semester_id,
            'peserta_didik_id'           => $peserta_didik_id,
            'nisn'                       => $nisn,
            'nik'                        => $nik,
            'no_kk'                      => $no_kk,
            'nama'                       => $nama,
            'jenis_kelamin'              => $jenis_kelamin,
            'tempat_lahir'               => $tempat_lahir,
            'tanggal_lahir'              => $tanggal_lahir,
            'nama_ibu_kandung'           => $nama_ibu_kandung,
            'kode_provinsi'              => $kode_provinsi,
            'provinsi'                   => $provinsi,
            'kode_kabupaten'             => $kode_kabupaten,
            'kabupaten'                  => $kabupaten,
            'kode_kecamatan'             => $kode_kecamatan,
            'kecamatan'                  => $kecamatan,
            'kode_desa_kelurahan'        => $kode_desa_kelurahan,
            'desa_kelurahan'             => $desa_kelurahan,
            'kode_wilayah'               => $kode_wilayah,
            'kode_dagri'                 => $kode_dagri,
            'alamat_jalan'               => $alamat_jalan,
            'rt'                         => $rt,
            'rw'                         => $rw,
            'lintang'                    => $lintang,
            'bujur'                      => $bujur,
            'status_approval'            => $status_approval,
            'status_approval_keterangan' => $status_approval_keterangan,
            'status_validasi'            => $status_validasi,
            'status'                     => $status,
            'keterangan_approval'        => $keterangan_approval,
            'alasan_approval_id'         => $alasan_approval_id,
            'alasan_approval_keterangan' => $alasan_approval_keterangan,
            'keterangan_tolak'           => $keterangan_tolak,
            'alasan_lainnya'             => $alasan_lainnya,
            'tingkat_pendidikan'         => $tingkat_pendidikan,
            'kebutuhan_khusus_id'        => $kebutuhan_khusus_id,
            'aktif'                      => ($aktif_val === '1' || $aktif_val === 1 || $aktif_val === true || $aktif_val === 'true'),
            'create_date'                => $create_date,
            'last_update'                => $last_update,
            'soft_delete_ats'            => $soft_delete_ats,
            'unnamed_42'                 => $unnamed_42,
            'created_at'                 => $now,
            'updated_at'                 => $now,
        ]);
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
