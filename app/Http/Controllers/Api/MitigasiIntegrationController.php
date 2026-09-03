<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MitigasiAggregatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MitigasiIntegrationController extends Controller
{
    protected MitigasiAggregatorService $aggregatorService;

    public function __construct(MitigasiAggregatorService $aggregatorService)
    {
        $this->aggregatorService = $aggregatorService;
    }

    /**
     * Endpoint PULL (GET /api/v1/mitigasi/ats-summary)
     * Mengembalikan data ringkasan agregat ATS bebas PII untuk dikonsumsi Sistem Mitigasi ATS.
     */
    public function getSummary(Request $request): JsonResponse
    {
        $forceFresh = $request->boolean('fresh', false);
        $summary = $this->aggregatorService->getAggregatedSummary($forceFresh);

        return response()->json($summary, 200, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Endpoint PUSH (POST /api/v1/mitigasi/push-summary)
     * Dipanggil oleh Frontend Admin saat menekan tombol "Terbitkan Ringkasan ke Portal Mitigasi".
     */
    public function pushSummary(Request $request): JsonResponse
    {
        $result = $this->aggregatorService->pushToMitigasi();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data'    => $result,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'error'   => $result['error'] ?? null,
        ], 500);
    }
}
