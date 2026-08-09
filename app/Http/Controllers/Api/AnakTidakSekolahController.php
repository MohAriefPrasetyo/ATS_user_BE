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
     * Display a listing of the resource (Tampilan Tabel Depan ATS).
     * Mode Admin: Menampilkan Biodata Lengkap
     * Mode User Biasa: Menampilkan Data Ringkas (Sembunyikan NIK, No KK, Ibu Kandung, dll)
     */
    public function index(Request $request): JsonResponse
    {
        $query = AnakTidakSekolah::with('tindakLanjuts');

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

        // Filter Status Baku ATS
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter Penanganan (Sudah / Belum Ditindaklanjuti)
        if ($request->filled('filter_tindak_lanjut')) {
            $filter = $request->filter_tindak_lanjut;
            if ($filter === 'sudah_ditindaklanjuti') {
                $query->has('tindakLanjuts');
            } elseif ($filter === 'belum_ditindaklanjuti') {
                $query->doesntHave('tindakLanjuts');
            }
        }

        // Filter spesifik berdasarkan hasil Keterangan Tindak Lanjut
        if ($request->filled('keterangan_tindak_lanjut')) {
            $keterangan = $request->keterangan_tindak_lanjut;
            $query->whereHas('tindakLanjuts', function ($q) use ($keterangan) {
                $q->where('keterangan', $keterangan);
            });
        }

        $paginatedData = $query->orderBy('created_at', 'desc')
                               ->paginate($request->get('per_page', 15));

        // Format proteksi data berdasarkan role user (Admin vs User Biasa)
        $data = $this->formatAtsResponse($paginatedData, $request);

        return response()->json([
            'success' => true,
            'message' => 'Daftar data Anak Tidak Sekolah berhasil diambil.',
            'is_admin' => $request->user()?->role === 'admin',
            'data'    => $data
        ]);
    }

    /**
     * Display the specified resource (Detail View ATS).
     * Mode Admin: Full 43 Kolom Biodata Lengkap
     * Mode User Biasa: Data Publik Ringkas
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $ats = AnakTidakSekolah::with(['tindakLanjuts.user'])->findOrFail($id);

        $data = $this->formatAtsResponse($ats, $request);

        return response()->json([
            'success' => true,
            'message' => 'Detail data Anak Tidak Sekolah.',
            'is_admin' => $request->user()?->role === 'admin',
            'data'    => $data
        ]);
    }

    /**
     * Store a newly created resource in storage (Khusus Admin).
     */
    public function store(Request $request): JsonResponse
    {
        // Proteksi Hak Akses Admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Penambahan data ATS hanya dapat dilakukan oleh Admin.'
            ], 403);
        }

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
     * Update the specified resource in storage (Khusus Admin).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Proteksi Hak Akses Admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengubahan data ATS hanya dapat dilakukan oleh Admin.'
            ], 403);
        }

        $ats = AnakTidakSekolah::findOrFail($id);
        $ats->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data Anak Tidak Sekolah berhasil diperbarui.',
            'data'    => $ats->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage (Khusus Admin).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        // Proteksi Hak Akses Admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Penghapusan data ATS hanya dapat dilakukan oleh Admin.'
            ], 403);
        }

        $ats = AnakTidakSekolah::findOrFail($id);
        $ats->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Anak Tidak Sekolah berhasil dihapus.'
        ]);
    }

    /**
     * Import data Excel ATS (Khusus Admin).
     */
    public function import(Request $request): JsonResponse
    {
        // Proteksi Hak Akses Admin
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Impor data Excel hanya dapat dilakukan oleh Admin.'
            ], 403);
        }

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
     * Helper internal untuk memfilter kolom sensitif jika user BUKAN Admin.
     */
    private function formatAtsResponse($ats, Request $request)
    {
        $user = $request->user();
        $isAdmin = $user && $user->role === 'admin';

        if ($isAdmin) {
            // Mode Admin: Kembalikan biodata lengkap (43 Kolom)
            return $ats;
        }

        // Mode User Biasa: Sembunyikan kolom sensitif (NIK, No KK, Ibu Kandung, Tanggal Lahir, Alamat Jalan, dll)
        $sensitiveFields = [
            'nik',
            'no_kk',
            'nama_ibu_kandung',
            'tanggal_lahir',
            'alamat_jalan',
            'rt',
            'rw',
            'peserta_didik_id',
            'sekolah_id',
            'kode_dagri',
            'status_approval_keterangan',
            'alasan_approval_keterangan',
            'keterangan_tolak',
        ];

        if ($ats instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $ats->getCollection()->transform(function ($item) use ($sensitiveFields) {
                return $item->makeHidden($sensitiveFields);
            });
            return $ats;
        }

        return $ats->makeHidden($sensitiveFields);
    }
}
