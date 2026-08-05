<?php

use App\Http\Controllers\Api\AnakTidakSekolahController;
use Illuminate\Support\Facades\Route;

// Rute REST API Anak Tidak Sekolah (43 Kolom)
Route::post('ats/import', [AnakTidakSekolahController::class, 'import']);
Route::apiResource('ats', AnakTidakSekolahController::class);
