<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TindakLanjut;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TindakLanjutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TindakLanjut::with(['anakTidakSekolah', 'user']);

        if ($request->filled('anak_tidak_sekolah_id')) {
            $query->where('anak_tidak_sekolah_id', $request->anak_tidak_sekolah_id);
        }

        if ($request->filled('keterangan')) {
            $query->where('keterangan', $request->keterangan);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar data Tindak Lanjut berhasil diambil.',
            'data'    => $data
        ]);
    }

    /**
     * Store a newly created resource in storage (Form Tindak Lanjut - Khusus Admin).
     */
    public function store(Request $request): JsonResponse
    {
        // Proteksi Hak Akses Admin (Dikommentari sementara)
        // if (!$request->user() || $request->user()->role !== 'admin') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Akses ditolak. Pengisian form Tindak Lanjut hanya dapat dilakukan oleh Admin.'
        //     ], 403);
        // }

        $validated = $request->validate([
            'anak_tidak_sekolah_id' => 'required|exists:anak_tidak_sekolah,id',
            'keterangan'            => 'required|string|max:255',
            'alasan'                => 'nullable|string',
            'tanggal_tindak_lanjut' => 'nullable|date',
            
            // Validasi file upload (Maksimal 10 MB = 10240 KB)
            'dokumen_pendukung'     => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'foto_dokumentasi'      => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $data = $validated;
        $data['user_id'] = $request->user()?->id;

        // Handling File Upload Dokumen Pendukung (Max 10MB)
        if ($request->hasFile('dokumen_pendukung')) {
            $dokumenPath = $request->file('dokumen_pendukung')->store('tindak_lanjut/dokumen', 'public');
            $data['dokumen_pendukung_path'] = $dokumenPath;
        }

        // Handling File Upload Foto Dokumentasi Kunjungan (Max 10MB)
        if ($request->hasFile('foto_dokumentasi')) {
            $fotoPath = $request->file('foto_dokumentasi')->store('tindak_lanjut/foto', 'public');
            $data['foto_dokumentasi_path'] = $fotoPath;
        }

        $tindakLanjut = TindakLanjut::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Data Tindak Lanjut berhasil disimpan.',
            'data'    => $tindakLanjut->load(['anakTidakSekolah', 'user'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $tindakLanjut = TindakLanjut::with(['anakTidakSekolah', 'user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail data Tindak Lanjut berhasil diambil.',
            'data'    => $tindakLanjut
        ]);
    }

    /**
     * Update the specified resource in storage (Khusus Admin).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Proteksi Hak Akses Admin (Dikommentari sementara)
        // if (!$request->user() || $request->user()->role !== 'admin') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Akses ditolak. Pengubahan data Tindak Lanjut hanya dapat dilakukan oleh Admin.'
        //     ], 403);
        // }

        $tindakLanjut = TindakLanjut::findOrFail($id);

        $validated = $request->validate([
            'keterangan'            => 'sometimes|required|string|max:255',
            'alasan'                => 'nullable|string',
            'tanggal_tindak_lanjut' => 'nullable|date',
            'status'                => 'nullable|string|max:50',
            
            // Validasi file upload (Maksimal 10 MB = 10240 KB)
            'dokumen_pendukung'     => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'foto_dokumentasi'      => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        $data = $validated;

        // Handling Update Dokumen Pendukung
        if ($request->hasFile('dokumen_pendukung')) {
            if ($tindakLanjut->dokumen_pendukung_path && Storage::disk('public')->exists($tindakLanjut->dokumen_pendukung_path)) {
                Storage::disk('public')->delete($tindakLanjut->dokumen_pendukung_path);
            }
            $data['dokumen_pendukung_path'] = $request->file('dokumen_pendukung')->store('tindak_lanjut/dokumen', 'public');
        }

        // Handling Update Foto Dokumentasi Kunjungan
        if ($request->hasFile('foto_dokumentasi')) {
            if ($tindakLanjut->foto_dokumentasi_path && Storage::disk('public')->exists($tindakLanjut->foto_dokumentasi_path)) {
                Storage::disk('public')->delete($tindakLanjut->foto_dokumentasi_path);
            }
            $data['foto_dokumentasi_path'] = $request->file('foto_dokumentasi')->store('tindak_lanjut/foto', 'public');
        }

        $tindakLanjut->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data Tindak Lanjut berhasil diperbarui.',
            'data'    => $tindakLanjut->fresh()->load(['anakTidakSekolah', 'user'])
        ]);
    }

    /**
     * Remove the specified resource from storage (Khusus Admin).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        // Proteksi Hak Akses Admin (Dikommentari sementara)
        // if (!$request->user() || $request->user()->role !== 'admin') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Akses ditolak. Penghapusan data Tindak Lanjut hanya dapat dilakukan oleh Admin.'
        //     ], 403);
        // }

        $tindakLanjut = TindakLanjut::findOrFail($id);

        // Hapus berkas fisik dokumen & foto jika ada di storage
        if ($tindakLanjut->dokumen_pendukung_path && Storage::disk('public')->exists($tindakLanjut->dokumen_pendukung_path)) {
            Storage::disk('public')->delete($tindakLanjut->dokumen_pendukung_path);
        }
        if ($tindakLanjut->foto_dokumentasi_path && Storage::disk('public')->exists($tindakLanjut->foto_dokumentasi_path)) {
            Storage::disk('public')->delete($tindakLanjut->foto_dokumentasi_path);
        }

        // Hapus permanen (Hard Delete) langsung dari baris tabel MySQL
        $tindakLanjut->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Data Tindak Lanjut berhasil dihapus secara permanen.'
        ]);
    }
}
