<?php

namespace App\Exports;

use App\Models\AnakTidakSekolah;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AnakTidakSekolahExport implements FromQuery, WithHeadings, WithMapping
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Query data ATS sesuai filter yang dikirimkan.
     */
    public function query()
    {
        return AnakTidakSekolah::filter($this->request)
            ->forAdminContext($this->request)
            ->with('asesmen')
            ->orderBy('id', 'desc');
    }

    /**
     * Header kolom di file Excel.
     */
    public function headings(): array
    {
        return [
            'ID',
            'NIK',
            'NISN',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Provinsi',
            'Kabupaten / Kota',
            'Kecamatan',
            'Desa / Kelurahan',
            'Nama Sekolah Asal',
            'Kategori Sekolah',
            'Status ATS',
            'Alasan',
            'Status Asesmen',
            'Program Intervensi',
            'Tanggal Asesmen',
            'Catatan Asesmen',
        ];
    }

    /**
     * Formatting tiap baris data di file Excel.
     */
    public function map($row): array
    {
        $asesmen = $row->asesmen;
        $alasanAts = ($row->alasan_approval_keterangan && $row->alasan_approval_keterangan !== '-') 
            ? $row->alasan_approval_keterangan 
            : ($row->alasan_lainnya ?? '-');

        return [
            $row->id,
            $row->nik ?? '-',
            $row->nisn ?? '-',
            $row->nama,
            $row->jenis_kelamin,
            $row->provinsi ?? 'Sulawesi Tengah',
            $row->kabupaten,
            $row->kecamatan,
            $row->desa_kelurahan,
            $row->nama_sekolah ?? '-',
            $row->kategori_sekolah ?? 'Non-Sekolah',
            $row->status === 'DO' ? 'Putus Sekolah (DO)' : ($row->status === 'BPB' ? 'Belum Pernah Bersekolah (BPB)' : 'Lulus Tidak Melanjutkan (LTM)'),
            $alasanAts,
            $asesmen ? 'Sudah Diasesmen' : 'Belum Diasesmen',
            $asesmen?->program_intervensi ?? '-',
            $asesmen?->tanggal_asesmen ?? '-',
            $asesmen?->alasan ?? '-',
        ];
    }
}
