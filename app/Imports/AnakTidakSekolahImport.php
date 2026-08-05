<?php

namespace App\Imports;

use App\Models\AnakTidakSekolah;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AnakTidakSekolahImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    private function transformDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function transformDateTime($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d H:i:s');
            }
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function model(array $row)
    {
        // Skip empty row if essential identifiers are missing
        if (empty($row['nama']) && empty($row['nik']) && empty($row['peserta_didik_id'])) {
            return null;
        }

        return new AnakTidakSekolah([
            'sekolah_id'                 => $row['sekolah_id'] ?? null,
            'tahun'                      => $row['tahun'] ?? null,
            'semester_id'                => $row['semester_id'] ?? null,
            'peserta_didik_id'           => $row['peserta_didik_id'] ?? null,
            'nisn'                       => $row['nisn'] ?? null,
            'nik'                        => $row['nik'] ?? null,
            'no_kk'                      => $row['no_kk'] ?? null,
            'nama'                       => $row['nama'] ?? null,
            'jenis_kelamin'              => $row['jenis_kelamin'] ?? null,
            'tempat_lahir'               => $row['tempat_lahir'] ?? null,
            'tanggal_lahir'              => $this->transformDate($row['tanggal_lahir'] ?? null),
            'nama_ibu_kandung'           => $row['nama_ibu_kandung'] ?? null,
            'kode_provinsi'              => $row['kode_provinsi'] ?? null,
            'provinsi'                   => $row['provinsi'] ?? null,
            'kode_kabupaten'             => $row['kode_kabupaten'] ?? null,
            'kabupaten'                  => $row['kabupaten'] ?? null,
            'kode_kecamatan'             => $row['kode_kecamatan'] ?? null,
            'kecamatan'                  => $row['kecamatan'] ?? null,
            'kode_desa_kelurahan'        => $row['kode_desa_kelurahan'] ?? null,
            'desa_kelurahan'             => $row['desa_kelurahan'] ?? null,
            'kode_wilayah'               => $row['kode_wilayah'] ?? null,
            'kode_dagri'                 => $row['kode_dagri'] ?? null,
            'alamat_jalan'               => $row['alamat_jalan'] ?? null,
            'rt'                         => $row['rt'] ?? null,
            'rw'                         => $row['rw'] ?? null,
            'lintang'                    => isset($row['lintang']) && $row['lintang'] !== '' ? (float)$row['lintang'] : null,
            'bujur'                      => isset($row['bujur']) && $row['bujur'] !== '' ? (float)$row['bujur'] : null,
            'status_approval'            => $row['status_approval'] ?? null,
            'status_approval_keterangan' => $row['status_approval_keterangan'] ?? null,
            'status_validasi'            => $row['status_validasi'] ?? null,
            'status'                     => $row['status'] ?? null,
            'keterangan_approval'        => $row['keterangan_approval'] ?? null,
            'alasan_approval_id'         => $row['alasan_approval_id'] ?? null,
            'alasan_approval_keterangan' => $row['alasan_approval_keterangan'] ?? null,
            'keterangan_tolak'           => $row['keterangan_tolak'] ?? null,
            'alasan_lainnya'             => $row['alasan_lainnya'] ?? null,
            'tingkat_pendidikan'         => $row['tingkat_pendidikan'] ?? null,
            'kebutuhan_khusus_id'        => $row['kebutuhan_khusus_id'] ?? null,
            'aktif'                      => isset($row['aktif']) ? (bool)$row['aktif'] : true,
            'create_date'                => $this->transformDateTime($row['create_date'] ?? null),
            'last_update'                => $this->transformDateTime($row['last_update'] ?? null),
            'soft_delete_ats'            => $this->transformDateTime($row['soft_delete_ats'] ?? null),
            'unnamed_42'                 => $row['unnamed_42'] ?? null,
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
