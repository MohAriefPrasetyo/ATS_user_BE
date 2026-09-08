<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAsesmenRequest;
use App\Http\Requests\UpdateAsesmenRequest;
use App\Models\Asesmen;
use App\Models\AnakTidakSekolah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AsesmenController extends Controller
{
    /**
     * Menampilkan daftar riwayat Asesmen Lapangan ATS.
     * Otomatis disaring berdasarkan konteks hak akses wilayah / sekolah Admin yang bertugas.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Asesmen::with(['anakTidakSekolah', 'user'])
            ->whereHas('anakTidakSekolah', function ($q) use ($request) {
                $q->forAdminContext($request);
            })
            ->latest('tanggal_asesmen')
            ->latest('updated_at');

        // Filter opsional berdasarkan anak_tidak_sekolah_id
        if ($request->filled('anak_tidak_sekolah_id')) {
            $query->where('anak_tidak_sekolah_id', $request->anak_tidak_sekolah_id);
        }

        $perPage = $request->get('per_page', 15);
        $data = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar data Asesmen Lapangan berhasil diambil.',
            'data'    => $data
        ]);
    }

    /**
     * Simpan / Perbarui Form Asesmen Lapangan (1 Siswa ATS = Tepat 1 Data Asesmen Aktif).
     */
    public function store(StoreAsesmenRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Gunakan X-User-Id dari Gateway jika ada, atau fallback ke auth user
        if ($request->header('X-User-Id')) {
            $data['user_id'] = (int) $request->header('X-User-Id');
        } elseif ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        // Sinkronisasi tanggal_asesmen & tanggal_tindak_lanjut
        if (!empty($data['tanggal_asesmen'])) {
            $data['tanggal_tindak_lanjut'] = $data['tanggal_asesmen'];
        } elseif (!empty($data['tanggal_tindak_lanjut'])) {
            $data['tanggal_asesmen'] = $data['tanggal_tindak_lanjut'];
        } else {
            $data['tanggal_asesmen'] = now()->toDateString();
            $data['tanggal_tindak_lanjut'] = now()->toDateString();
        }

        // Upload Berkas Dokumen Pendukung
        if ($request->hasFile('dokumen_pendukung')) {
            $data['dokumen_pendukung_path'] = $request->file('dokumen_pendukung')->store('asesmen/dokumen', 'public');
        }

        // Upload Foto Dokumentasi Kunjungan
        if ($request->hasFile('foto_dokumentasi')) {
            $data['foto_dokumentasi_path'] = $request->file('foto_dokumentasi')->store('asesmen/foto', 'public');
        }

        // Upload Foto Rumah Tinggal
        if ($request->hasFile('foto_rumah')) {
            $data['foto_rumah_path'] = $request->file('foto_rumah')->store('asesmen/foto_rumah', 'public');
        }

        // PENEGAKAN 1 ATS = 1 DATA ASESMEN
        // Cek apakah siswa sudah memiliki data asesmen sebelumnya
        $existing = Asesmen::where('anak_tidak_sekolah_id', $data['anak_tidak_sekolah_id'])->first();

        if ($existing) {
            // Hapus file lama jika ada file baru diupload
            if ($request->hasFile('dokumen_pendukung') && $existing->dokumen_pendukung_path) {
                Storage::disk('public')->delete($existing->dokumen_pendukung_path);
            }
            if ($request->hasFile('foto_dokumentasi') && $existing->foto_dokumentasi_path) {
                Storage::disk('public')->delete($existing->foto_dokumentasi_path);
            }
            if ($request->hasFile('foto_rumah') && $existing->foto_rumah_path) {
                Storage::disk('public')->delete($existing->foto_rumah_path);
            }

            $existing->update($data);
            $asesmen = $existing;
        } else {
            $asesmen = Asesmen::create($data);
        }

        $asesmen->anakTidakSekolah?->touch();

        return response()->json([
            'success' => true,
            'message' => 'Data Asesmen berhasil disimpan.',
            'data'    => $asesmen->load(['anakTidakSekolah', 'user'])
        ], 201);
    }

    /**
     * Tampilkan detail data Asesmen.
     */
    public function show(string $id): JsonResponse
    {
        $asesmen = Asesmen::with(['anakTidakSekolah', 'user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail data Asesmen.',
            'data'    => $asesmen
        ]);
    }

    /**
     * Perbarui data Asesmen yang sudah ada.
     */
    public function update(UpdateAsesmenRequest $request, string $id): JsonResponse
    {
        $asesmen = Asesmen::findOrFail($id);
        $data = $request->validated();

        if (!empty($data['tanggal_asesmen'])) {
            $data['tanggal_tindak_lanjut'] = $data['tanggal_asesmen'];
        } elseif (!empty($data['tanggal_tindak_lanjut'])) {
            $data['tanggal_asesmen'] = $data['tanggal_tindak_lanjut'];
        }

        if ($request->hasFile('dokumen_pendukung')) {
            if ($asesmen->dokumen_pendukung_path && Storage::disk('public')->exists($asesmen->dokumen_pendukung_path)) {
                Storage::disk('public')->delete($asesmen->dokumen_pendukung_path);
            }
            $data['dokumen_pendukung_path'] = $request->file('dokumen_pendukung')->store('asesmen/dokumen', 'public');
        }

        if ($request->hasFile('foto_dokumentasi')) {
            if ($asesmen->foto_dokumentasi_path && Storage::disk('public')->exists($asesmen->foto_dokumentasi_path)) {
                Storage::disk('public')->delete($asesmen->foto_dokumentasi_path);
            }
            $data['foto_dokumentasi_path'] = $request->file('foto_dokumentasi')->store('asesmen/foto', 'public');
        }

        if ($request->hasFile('foto_rumah')) {
            if ($asesmen->foto_rumah_path && Storage::disk('public')->exists($asesmen->foto_rumah_path)) {
                Storage::disk('public')->delete($asesmen->foto_rumah_path);
            }
            $data['foto_rumah_path'] = $request->file('foto_rumah')->store('asesmen/foto_rumah', 'public');
        }

        $asesmen->update($data);
        $asesmen->anakTidakSekolah?->touch();

        return response()->json([
            'success' => true,
            'message' => 'Data Asesmen berhasil diperbarui.',
            'data'    => $asesmen->fresh()->load(['anakTidakSekolah', 'user'])
        ]);
    }

    /**
     * Hapus data Asesmen (Permanen).
     */
    public function destroy(string $id): JsonResponse
    {
        $asesmen = Asesmen::findOrFail($id);
        $ats = $asesmen->anakTidakSekolah;

        if ($asesmen->dokumen_pendukung_path && Storage::disk('public')->exists($asesmen->dokumen_pendukung_path)) {
            Storage::disk('public')->delete($asesmen->dokumen_pendukung_path);
        }
        if ($asesmen->foto_dokumentasi_path && Storage::disk('public')->exists($asesmen->foto_dokumentasi_path)) {
            Storage::disk('public')->delete($asesmen->foto_dokumentasi_path);
        }
        if ($asesmen->foto_rumah_path && Storage::disk('public')->exists($asesmen->foto_rumah_path)) {
            Storage::disk('public')->delete($asesmen->foto_rumah_path);
        }

        $asesmen->forceDelete();
        $ats?->touch();

        return response()->json([
            'success' => true,
            'message' => 'Data Asesmen berhasil dihapus.'
        ]);
    }
}
