<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiwayatImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiwayatImportController extends Controller
{
    /**
     * Menampilkan daftar Riwayat Import & Log untuk Tabel Frontend.
     */
    public function index(Request $request): JsonResponse
    {
        $data = RiwayatImport::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar riwayat import berhasil diambil.',
            'data'    => $data
        ]);
    }

    /**
     * Menampilkan detail riwayat import spesifik.
     */
    public function show(string $id): JsonResponse
    {
        $riwayat = RiwayatImport::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail riwayat import.',
            'data'    => $riwayat
        ]);
    }
}
