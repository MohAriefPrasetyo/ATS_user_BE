<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnakTidakSekolahRequest;
use App\Imports\AnakTidakSekolahImport;
use App\Models\AnakTidakSekolah;
use App\Models\RiwayatImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AnakTidakSekolahController extends Controller
{
    /**
     * Tampilan Tabel Depan ATS (Mendukung Search, Filter Wilayah, Status ATS, & Filter Tindak Lanjut).
     */
    public function index(Request $request): JsonResponse
    {
        $data = AnakTidakSekolah::filter($request)
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar data Anak Tidak Sekolah berhasil diambil.',
            'data'    => $data
        ]);
    }

    /**
     * Detail Data Anak Tidak Sekolah (43 Kolom Lengkap + Riwayat Tindak Lanjut).
     */
    public function show(string $id): JsonResponse
    {
        $ats = AnakTidakSekolah::with(['tindakLanjuts.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail data Anak Tidak Sekolah.',
            'data'    => $ats
        ]);
    }

    /**
     * Tambah Data ATS Baru.
     */
    public function store(StoreAnakTidakSekolahRequest $request): JsonResponse
    {
        $ats = AnakTidakSekolah::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data Anak Tidak Sekolah berhasil ditambahkan.',
            'data'    => $ats
        ], 201);
    }

    /**
     * Edit / Update Data ATS.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $ats = AnakTidakSekolah::findOrFail($id);
        $ats->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data Anak Tidak Sekolah berhasil diperbarui.',
            'data'    => $ats->fresh()
        ]);
    }

    /**
     * Hapus Data ATS.
     */
    public function destroy(string $id): JsonResponse
    {
        $ats = AnakTidakSekolah::findOrFail($id);
        $ats->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Anak Tidak Sekolah berhasil dihapus.'
        ]);
    }

    /**
     * Impor Data Excel ATS + Otomatis Mencatat Log di Riwayat Import.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            'periode_data' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $namaBerkas = $file->getClientOriginalName();

        try {
            $beforeCount = AnakTidakSekolah::count();
            Excel::import(new AnakTidakSekolahImport, $file);
            $afterCount = AnakTidakSekolah::count();

            $dataSukses = max(1, $afterCount - $beforeCount);
            $periodeNumber = RiwayatImport::count() + 1;
            $periodeData = $request->input('periode_data', 'Periode #' . $periodeNumber);

            // Mencatat Log ke Tabel Riwayat Import
            $riwayat = RiwayatImport::create([
                'user_id'       => $request->user()?->id,
                'periode_data'  => $periodeData,
                'nama_berkas'   => $namaBerkas,
                'data_sukses'   => $dataSukses,
                'data_duplikat' => 0,
                'status'        => 'Selesai',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data Excel Anak Tidak Sekolah berhasil diimpor.',
                'data'    => $riwayat
            ]);
        } catch (\Exception $e) {
            $periodeNumber = RiwayatImport::count() + 1;
            RiwayatImport::create([
                'user_id'       => $request->user()?->id,
                'periode_data'  => $request->input('periode_data', 'Periode #' . $periodeNumber),
                'nama_berkas'   => $namaBerkas,
                'data_sukses'   => 0,
                'data_duplikat' => 0,
                'status'        => 'Gagal',
                'catatan'       => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint Export Data Laporan PDF Terfilter.
     */
    public function exportPdf(Request $request): JsonResponse
    {
        $data = AnakTidakSekolah::filter($request)->get();

        return response()->json([
            'success'    => true,
            'message'    => 'Data laporan terfilter berhasil diproses oleh backend.',
            'total_data' => $data->count(),
            'filters'    => $request->only(['search', 'kabupaten', 'kecamatan', 'status', 'filter_tindak_lanjut', 'keterangan_tindak_lanjut']),
            'data'       => $data
        ]);
    }
}
