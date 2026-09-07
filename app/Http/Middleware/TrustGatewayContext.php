<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrustGatewayContext
{
    /**
     * Handle an incoming request.
     * Membaca context headers dari Gateway dan menyuntikkan user virtual ke request.
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->header('X-User-Id');

        if ($userId) {
            $user = new GenericUser([
                'id'              => (int) $userId,
                'role'            => $request->header('X-User-Role', 'admin'),
                'name'            => $request->header('X-User-Name', 'Petugas ATS'),
                'email'           => $request->header('X-User-Email', ''),
                'jenis_penugasan' => $request->header('X-User-Assignment', 'provinsi'),
                'kabupaten'       => $request->header('X-User-Kabupaten', ''),
                'kecamatan'       => $request->header('X-User-Kecamatan', ''),
                'kelurahan'       => $request->header('X-User-Kelurahan', ''),
                'sekolah_id'      => $request->header('X-User-Sekolah-Id', ''),
            ]);

            Auth::setUser($user);
            $request->setUserResolver(fn () => $user);
        }

        return $next($request);
    }
}
