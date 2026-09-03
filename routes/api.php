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

// 1. Modul Riwayat Import & Log Data ATS
Route::controller(RiwayatImportController::class)->group(function () {
    Route::get('ats/riwayat-import', 'index');
    Route::get('ats/riwayat-import/{id}', 'show');
});

// 2. Modul Master Data ATS & Laporan Export
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

// 3. Modul Form & Riwayat Tindak Lanjut
Route::controller(TindakLanjutController::class)->group(function () {
    Route::get('tindak-lanjut', 'index');
    Route::post('tindak-lanjut', 'store');
    Route::get('tindak-lanjut/{id}', 'show');
    Route::put('tindak-lanjut/{id}', 'update');
    Route::delete('tindak-lanjut/{id}', 'destroy');
});

// 4. Modul Integrasi Sistem Mitigasi ATS (Zero-PII Data Sharing)
Route::controller(\App\Http\Controllers\Api\MitigasiIntegrationController::class)->group(function () {
    // Endpoint PULL: Menyuplai ringkasan agregat ke Sistem Mitigasi (dijaga Bearer Token)
    Route::get('v1/mitigasi/ats-summary', 'getSummary')
        ->middleware(\App\Http\Middleware\VerifyMitigasiBearerToken::class);

    // Endpoint PUSH: Trigger penerbitan ringkasan secara instan via Webhook
    Route::post('v1/mitigasi/push-summary', 'pushSummary');
    Route::post('ats/mitigasi/push-summary', 'pushSummary');
});


