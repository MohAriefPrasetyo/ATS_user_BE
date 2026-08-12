<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\AnakTidakSekolahImport;
use App\Models\AnakTidakSekolah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AnakTidakSekolahController extends Controller
{
    /**
     * Display a listing of the resource (Tampilan Tabel Depan ATS - Biodata Lengkap).
     * Mendukung filter status penanganan tindak lanjut:
     * - filter_tindak_lanjut=sudah_ditindaklanjuti
     * - filter_tindak_lanjut=belum_ditindaklanjuti
     * - keterangan_tindak_lanjut=Kembali Sekolah
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->buildFilteredQuery($request);

        $data = $query->orderBy('created_at', 'desc')
                       ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar data Anak Tidak Sekolah berhasil diambil.',
            'data'    => $data
        ]);
    }

    /**
     * Display the specified resource (Detail View ATS - Biodata Lengkap 43 Kolom).
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $ats = AnakTidakSekolah::with(['tindakLanjuts.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail data Anak Tidak Sekolah.',
            'data'    => $ats
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nik'  => 'nullable|string|size:16|unique:anak_tidak_sekolah,nik',
        ]);

        $ats = AnakTidakSekolah::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data Anak Tidak Sekolah berhasil ditambahkan.',
            'data'    => $ats
        ], 201);
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $ats = AnakTidakSekolah::findOrFail($id);
        $ats->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Anak Tidak Sekolah berhasil dihapus.'
        ]);
    }

    /**
     * Import data Excel ATS.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            Excel::import(new AnakTidakSekolahImport, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Data Excel Anak Tidak Sekolah berhasil diimpor.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * EXPORT PDF DATA TERFILTER (Pure Backend API Endpoint):
     * Mengambil data terfilter dari database dan mengembalikan data JSON / Stream Laporan.
     */
    public function exportPdf(Request $request): JsonResponse
    {
        $data = $this->buildFilteredQuery($request)->get();

        return response()->json([
            'success'    => true,
            'message'    => 'Data laporan terfilter berhasil diproses oleh backend.',
            'total_data' => $data->count(),
            'filters'    => $request->only(['search', 'kabupaten', 'kecamatan', 'status', 'filter_tindak_lanjut', 'keterangan_tindak_lanjut']),
            'data'       => $data
        ]);
    }

    /**
     * Internal Query Builder untuk memfilter data ATS secara terpusat.
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = AnakTidakSekolah::with('tindakLanjuts');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', $request->kecamatan);
        }
        if ($request->filled('kabupaten')) {
            $query->where('kabupaten', $request->kabupaten);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('filter_tindak_lanjut')) {
            $filter = $request->filter_tindak_lanjut;
            if ($filter === 'sudah_ditindaklanjuti') {
                $query->has('tindakLanjuts');
            } elseif ($filter === 'belum_ditindaklanjuti') {
                $query->doesntHave('tindakLanjuts');
            }
        }

        if ($request->filled('keterangan_tindak_lanjut')) {
            $keterangan = $request->keterangan_tindak_lanjut;
            $query->whereHas('tindakLanjuts', function ($q) use ($keterangan) {
                $q->where('keterangan', $keterangan);
            });
        }

        return $query;
    }
}
