<?php

use App\Http\Controllers\Api\AnakTidakSekolahController;
use App\Http\Controllers\Api\TindakLanjutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sistem ATS (Anak Tidak Sekolah & Tindak Lanjut)
|--------------------------------------------------------------------------
|
| - RUTE PUBLIK (User Biasa) : Tampilan Data Ringkas & Read-Only
| - RUTE ADMIN                : Biodata Lengkap (43 Kolom), Impor Excel, 
|                                & Pengisian / Pengubahan Form Tindak Lanjut
|
*/

// =========================================================================
// 1. RUTE DATA ATS (Anak Tidak Sekolah) & EXPORT LAPORAN PDF
// =========================================================================
Route::get('ats/export-pdf', [AnakTidakSekolahController::class, 'exportPdf']);      // Download Laporan PDF Resmi Terfilter
Route::get('ats/export', [AnakTidakSekolahController::class, 'exportPdf']);          // Alias Endpoint Export PDF
Route::get('ats', [AnakTidakSekolahController::class, 'index']);                     // List Data ATS (Filter Penanganan)
Route::get('ats/{id}', [AnakTidakSekolahController::class, 'show']);                 // Detail Data ATS (Biodata Lengkap)
Route::post('ats/import', [AnakTidakSekolahController::class, 'import']);            // Impor Data Excel ATS (Admin)
Route::post('ats', [AnakTidakSekolahController::class, 'store']);                    // Tambah Data ATS Baru (Admin)
Route::put('ats/{id}', [AnakTidakSekolahController::class, 'update']);               // Edit Data ATS (Admin)
Route::delete('ats/{id}', [AnakTidakSekolahController::class, 'destroy']);           // Hapus Data ATS (Admin)

// =========================================================================
// 2. RUTE TINDAK LANJUT
// =========================================================================
Route::get('tindak-lanjut', [TindakLanjutController::class, 'index']);          // List Riwayat Tindak Lanjut
Route::get('tindak-lanjut/{id}', [TindakLanjutController::class, 'show']);      // Detail Form Tindak Lanjut
Route::post('tindak-lanjut', [TindakLanjutController::class, 'store']);        // Simpan Form Tindak Lanjut (Admin)
Route::put('tindak-lanjut/{id}', [TindakLanjutController::class, 'update']);   // Edit Form Tindak Lanjut (Admin)
Route::delete('tindak-lanjut/{id}', [TindakLanjutController::class, 'destroy']);// Hapus Form Tindak Lanjut (Admin)
