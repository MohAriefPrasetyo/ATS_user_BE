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
     * Display a listing of the resource (Tampilan Tabel Depan).
     */
    public function index(Request $request): JsonResponse
    {
        $query = AnakTidakSekolah::query();

        // Search NIK, NISN, atau Nama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Filter Kecamatan & Kabupaten
        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', $request->kecamatan);
        }
        if ($request->filled('kabupaten')) {
            $query->where('kabupaten', $request->kabupaten);
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar data Anak Tidak Sekolah berhasil diambil.',
            'data'    => $data
        ]);
    }

    /**
     * Display the specified resource (Detail View 43 Kolom).
     */
    public function show(string $id): JsonResponse
    {
        $ats = AnakTidakSekolah::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail data Anak Tidak Sekolah (43 Kolom).',
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
     * Import data Excel ATS (43 Kolom).
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
}
