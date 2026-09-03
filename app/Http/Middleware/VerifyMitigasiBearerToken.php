<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMitigasiBearerToken
{
    /**
     * Memvalidasi Machine-to-Machine Secret Bearer Token untuk Sistem Mitigasi ATS.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = env('MITIGASI_SHARED_KEY', 'secret_bearer_token_ats_2026');
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Header Authorization Bearer diperlukan.',
            ], 401);
        }

        $token = substr($authHeader, 7);

        if (!hash_equals($expectedToken, $token)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Token Bearer tidak valid.',
            ], 401);
        }

        return $next($request);
    }
}
