<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTindakLanjutRequest;
use App\Http\Requests\UpdateTindakLanjutRequest;
use App\Models\TindakLanjut;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TindakLanjutController extends Controller
{
    /**
     * Tampilan List Data Tindak Lanjut.
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

        $data = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar data Tindak Lanjut berhasil diambil.',
            'data'    => $data
        ]);
    }

    /**
     * Detail Form Tindak Lanjut.
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
     * Simpan Form Tindak Lanjut Baru (+ Handling Upload File Lampiran).
     */
    public function store(StoreTindakLanjutRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()?->id;

        if ($request->hasFile('dokumen_pendukung')) {
            $data['dokumen_pendukung_path'] = $request->file('dokumen_pendukung')->store('tindak_lanjut/dokumen', 'public');
        }

        if ($request->hasFile('foto_dokumentasi')) {
            $data['foto_dokumentasi_path'] = $request->file('foto_dokumentasi')->store('tindak_lanjut/foto', 'public');
        }

        $tindakLanjut = TindakLanjut::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Data Tindak Lanjut berhasil disimpan.',
            'data'    => $tindakLanjut->load(['anakTidakSekolah', 'user'])
        ], 201);
    }

    /**
     * Edit / Update Form Tindak Lanjut.
     */
    public function update(UpdateTindakLanjutRequest $request, string $id): JsonResponse
    {
        $tindakLanjut = TindakLanjut::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('dokumen_pendukung')) {
            if ($tindakLanjut->dokumen_pendukung_path && Storage::disk('public')->exists($tindakLanjut->dokumen_pendukung_path)) {
                Storage::disk('public')->delete($tindakLanjut->dokumen_pendukung_path);
            }
            $data['dokumen_pendukung_path'] = $request->file('dokumen_pendukung')->store('tindak_lanjut/dokumen', 'public');
        }

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
     * Hapus Data Tindak Lanjut (Permanen).
     */
    public function destroy(string $id): JsonResponse
    {
        $tindakLanjut = TindakLanjut::findOrFail($id);

        if ($tindakLanjut->dokumen_pendukung_path && Storage::disk('public')->exists($tindakLanjut->dokumen_pendukung_path)) {
            Storage::disk('public')->delete($tindakLanjut->dokumen_pendukung_path);
        }
        if ($tindakLanjut->foto_dokumentasi_path && Storage::disk('public')->exists($tindakLanjut->foto_dokumentasi_path)) {
            Storage::disk('public')->delete($tindakLanjut->foto_dokumentasi_path);
        }

        $tindakLanjut->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Data Tindak Lanjut berhasil dihapus secara permanen.'
        ]);
    }
}
