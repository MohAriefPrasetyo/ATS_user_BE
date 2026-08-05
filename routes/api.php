<?php

use App\Http\Controllers\Api\AnakTidakSekolahController;
use Illuminate\Support\Facades\Route;

// Rute REST API Anak Tidak Sekolah (43 Kolom)
Route::apiResource('ats', AnakTidakSekolahController::class);
