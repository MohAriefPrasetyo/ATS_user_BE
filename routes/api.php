<?php

use App\Http\Controllers\Api\AnakTidakSekolahController;
use App\Http\Controllers\Api\RiwayatImportController;
use App\Http\Controllers\Api\TindakLanjutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sistem Informasi Anak Tidak Sekolah (ATS)
|--------------------------------------------------------------------------
*/

// 1. Modul Master Data ATS & Laporan Export
Route::controller(AnakTidakSekolahController::class)->group(function () {
    Route::get('ats/export-pdf', 'exportPdf');
    Route::get('ats/export', 'exportPdf');
    Route::post('ats/import', 'import');
    Route::get('ats', 'index');
    Route::post('ats', 'store');
    Route::get('ats/{id}', 'show');
    Route::put('ats/{id}', 'update');
    Route::delete('ats/{id}', 'destroy');
});

// 2. Modul Form & Riwayat Tindak Lanjut
Route::controller(TindakLanjutController::class)->group(function () {
    Route::get('tindak-lanjut', 'index');
    Route::post('tindak-lanjut', 'store');
    Route::get('tindak-lanjut/{id}', 'show');
    Route::put('tindak-lanjut/{id}', 'update');
    Route::delete('tindak-lanjut/{id}', 'destroy');
});

// 3. Modul Riwayat Import & Log Data ATS
Route::controller(RiwayatImportController::class)->group(function () {
    Route::get('ats/riwayat-import', 'index');
    Route::get('ats/riwayat-import/{id}', 'show');
});
