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
        $query = AnakTidakSekolah::with('tindakLanjuts');

        // Search NIK, NISN, atau Nama
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Filter Wilayah
        if ($this->request->filled('kecamatan')) {
            $query->where('kecamatan', $this->request->kecamatan);
        }
        if ($this->request->filled('kabupaten')) {
            $query->where('kabupaten', $this->request->kabupaten);
        }

        // Filter Status Baku
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        // Filter Penanganan (Sudah / Belum Ditindaklanjuti)
        if ($this->request->filled('filter_tindak_lanjut')) {
            $filter = $this->request->filter_tindak_lanjut;
            if ($filter === 'sudah_ditindaklanjuti') {
                $query->has('tindakLanjuts');
            } elseif ($filter === 'belum_ditindaklanjuti') {
                $query->doesntHave('tindakLanjuts');
            }
        }

        // Filter spesifik Keterangan Tindak Lanjut
        if ($this->request->filled('keterangan_tindak_lanjut')) {
            $keterangan = $this->request->keterangan_tindak_lanjut;
            $query->whereHas('tindakLanjuts', function ($q) use ($keterangan) {
                $q->where('keterangan', $keterangan);
            });
        }

        return $query->orderBy('created_at', 'desc');
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
            'Kabupaten',
            'Kecamatan',
            'Desa / Kelurahan',
            'Status ATS',
            'Status Penanganan',
            'Program Intervensi',
            'Catatan Tindak Lanjut',
        ];
    }

    /**
     * Formatting tiap baris data di file Excel.
     */
    public function map($row): array
    {
        $tindakLanjutTerakhir = $row->tindakLanjuts->last();

        return [
            $row->id,
            $row->nik ?? '-',
            $row->nisn ?? '-',
            $row->nama,
            $row->jenis_kelamin,
            $row->kabupaten,
            $row->kecamatan,
            $row->desa_kelurahan,
            $row->status ?? 'Belum Sekolah',
            $tindakLanjutTerakhir ? $tindakLanjutTerakhir->keterangan : 'Belum Ditindaklanjuti',
            $tindakLanjutTerakhir ? ($tindakLanjutTerakhir->program_intervensi ?? '-') : '-',
            $tindakLanjutTerakhir ? ($tindakLanjutTerakhir->alasan ?? '-') : '-',
        ];
    }
}
