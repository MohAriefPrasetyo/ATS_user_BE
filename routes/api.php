<?php

use App\Http\Controllers\Api\AnakTidakSekolahController;
use App\Http\Controllers\Api\TindakLanjutController;
use Illuminate\Support\Facades\Route;

// Rute REST API Anak Tidak Sekolah (43 Kolom)
Route::post('ats/import', [AnakTidakSekolahController::class, 'import']);
Route::apiResource('ats', AnakTidakSekolahController::class);

// Rute REST API Tindak Lanjut
Route::apiResource('tindak-lanjut', TindakLanjutController::class);



