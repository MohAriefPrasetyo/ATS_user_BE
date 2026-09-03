<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MitigasiAggregatorService
{
    const CACHE_KEY = 'mitigasi_ats_summary_v1';
    const CACHE_TTL = 3600; // 1 Jam (3600 detik)

    /**
     * Menghasilkan struktur JSON ringkasan data ATS teragregasi (Zero-PII).
     * Mendukung Cache memory agar pemanggilan berkala tidak membebani database utama.
     *
     * @param bool $forceFresh Jika true, akan bypass cache dan melakukan query langsung.
     * @return array
     */
    public function getAggregatedSummary(bool $forceFresh = false): array
    {
        if ($forceFresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->computeAggregation();
        });
    }

    /**
     * Eksekusi query agregasi murni di level database engine.
     */
    private function computeAggregation(): array
    {
        $baseQuery = DB::table('anak_tidak_sekolah')->whereNull('deleted_at');

        // 1. Overview counts
        $totalAts = (clone $baseQuery)->count();

        $genderCounts = (clone $baseQuery)
            ->select('jenis_kelamin', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        $statusCounts = (clone $baseQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $kelasCounts = (clone $baseQuery)
            ->select('tingkat_pendidikan', DB::raw('COUNT(*) as total'))
            ->groupBy('tingkat_pendidikan')
            ->pluck('total', 'tingkat_pendidikan');

        $puncakSd = (int) ($kelasCounts['6'] ?? 0);
        $puncakSmp = (int) ($kelasCounts['9'] ?? 0);

        // 2. By Kabupaten (13 Kabupaten/Kota se-Sulteng)
        $rawKabupaten = (clone $baseQuery)
            ->select(
                'kabupaten as nama',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN CAST(tingkat_pendidikan AS UNSIGNED) BETWEEN 1 AND 6 THEN 1 ELSE 0 END) as sd'),
                DB::raw('SUM(CASE WHEN CAST(tingkat_pendidikan AS UNSIGNED) BETWEEN 7 AND 9 THEN 1 ELSE 0 END) as smp'),
                DB::raw('SUM(CASE WHEN CAST(tingkat_pendidikan AS UNSIGNED) BETWEEN 10 AND 12 THEN 1 ELSE 0 END) as sma')
            )
            ->groupBy('kabupaten')
            ->orderByDesc('total')
            ->get();

        $byKabupaten = $rawKabupaten->map(function ($item) {
            return [
                'nama'  => (string) $item->nama,
                'total' => (int) $item->total,
                'sd'    => (int) $item->sd,
                'smp'   => (int) $item->smp,
                'sma'   => (int) $item->sma,
            ];
        })->values()->toArray();

        // 3. By Jenjang
        $totalSd = 0;
        for ($i = 1; $i <= 6; $i++) {
            $totalSd += (int) ($kelasCounts[(string)$i] ?? 0);
        }

        $totalSmp = 0;
        for ($i = 7; $i <= 9; $i++) {
            $totalSmp += (int) ($kelasCounts[(string)$i] ?? 0);
        }

        $totalSma = 0;
        for ($i = 10; $i <= 12; $i++) {
            $totalSma += (int) ($kelasCounts[(string)$i] ?? 0);
        }

        // 4. By Kelas (1 s/d 12)
        $byKelas = [];
        for ($i = 1; $i <= 12; $i++) {
            $byKelas['kelas_' . $i] = (int) ($kelasCounts[(string)$i] ?? 0);
        }

        // 5. By Gender & Reasons (Distribusi Alasan / Faktor Penyebab)
        $reasonRows = (clone $baseQuery)
            ->select('jenis_kelamin', 'alasan_approval_keterangan', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_kelamin', 'alasan_approval_keterangan')
            ->get();

        $byGenderAndReasons = [
            'laki_laki' => [
                'total'                     => (int) ($genderCounts['L'] ?? 0),
                'faktor_bekerja'            => 0,
                'faktor_pernikahan_dini'    => 0,
                'faktor_ekonomi'            => 0,
                'faktor_motivasi_minat'     => 0,
                'faktor_disabilitas'        => 0,
                'faktor_akses_transportasi' => 0,
                'lainnya'                   => 0,
            ],
            'perempuan' => [
                'total'                     => (int) ($genderCounts['P'] ?? 0),
                'faktor_bekerja'            => 0,
                'faktor_pernikahan_dini'    => 0,
                'faktor_ekonomi'            => 0,
                'faktor_motivasi_minat'     => 0,
                'faktor_disabilitas'        => 0,
                'faktor_akses_transportasi' => 0,
                'lainnya'                   => 0,
            ],
        ];

        foreach ($reasonRows as $row) {
            $genderKey = ($row->jenis_kelamin === 'P') ? 'perempuan' : 'laki_laki';
            $reason = trim((string) $row->alasan_approval_keterangan);
            $count = (int) $row->total;

            if ($reason === 'Bekerja' || str_contains(strtolower($reason), 'bekerja')) {
                $byGenderAndReasons[$genderKey]['faktor_bekerja'] += $count;
            } elseif ($reason === 'Menikah / mengurus rumah tangga' || str_contains(strtolower($reason), 'menikah')) {
                $byGenderAndReasons[$genderKey]['faktor_pernikahan_dini'] += $count;
            } elseif ($reason === 'Tidak ada biaya' || str_contains(strtolower($reason), 'biaya') || str_contains(strtolower($reason), 'ekonomi')) {
                $byGenderAndReasons[$genderKey]['faktor_ekonomi'] += $count;
            } elseif ($reason === 'Tidak mau bersekolah' || str_contains(strtolower($reason), 'tidak mau')) {
                $byGenderAndReasons[$genderKey]['faktor_motivasi_minat'] += $count;
            } elseif ($reason === 'Masalah Kesehatan / Penyandang Disabilitas' || str_contains(strtolower($reason), 'disabilitas') || str_contains(strtolower($reason), 'kesehatan')) {
                $byGenderAndReasons[$genderKey]['faktor_disabilitas'] += $count;
            } elseif ($reason === 'Sekolah jauh dari rumah' || str_contains(strtolower($reason), 'jauh') || str_contains(strtolower($reason), 'transportasi')) {
                $byGenderAndReasons[$genderKey]['faktor_akses_transportasi'] += $count;
            }
        }

        // Hitung kategori 'lainnya' sebagai sisa total dikurangi faktor terklasifikasi
        foreach (['laki_laki', 'perempuan'] as $gk) {
            $subtotalCategorized = $byGenderAndReasons[$gk]['faktor_bekerja']
                + $byGenderAndReasons[$gk]['faktor_pernikahan_dini']
                + $byGenderAndReasons[$gk]['faktor_ekonomi']
                + $byGenderAndReasons[$gk]['faktor_motivasi_minat']
                + $byGenderAndReasons[$gk]['faktor_disabilitas']
                + $byGenderAndReasons[$gk]['faktor_akses_transportasi'];

            $byGenderAndReasons[$gk]['lainnya'] = max(0, $byGenderAndReasons[$gk]['total'] - $subtotalCategorized);
        }

        // 6. Agregasi Tindak Lanjut (Hasil Penindaklanjutan: Sudah Lanjut vs Tidak Lanjut)
        $statusCol = \Illuminate\Support\Facades\Schema::hasColumn('tindak_lanjut', 'status') ? 'tl.status' : 'tl.keterangan';
        $statusColDirect = \Illuminate\Support\Facades\Schema::hasColumn('tindak_lanjut', 'status') ? 'status' : 'keterangan';

        $totalDitindaklanjuti = (int) DB::table('tindak_lanjut')->whereNull('deleted_at')->count();
        $totalSudahLanjut = (int) DB::table('tindak_lanjut')
            ->whereNull('deleted_at')
            ->whereRaw("LOWER({$statusColDirect}) LIKE '%sudah%'")
            ->count();
        $totalTidakLanjut = (int) DB::table('tindak_lanjut')
            ->whereNull('deleted_at')
            ->whereRaw("LOWER({$statusColDirect}) LIKE '%tidak%'")
            ->count();

        $persentaseSudahLanjut = $totalDitindaklanjuti > 0
            ? round(($totalSudahLanjut / $totalDitindaklanjuti) * 100, 1)
            : 0.0;

        $tlStatsByKab = DB::table('tindak_lanjut as tl')
            ->join('anak_tidak_sekolah as ats', 'tl.anak_tidak_sekolah_id', '=', 'ats.id')
            ->select(
                'ats.kabupaten',
                DB::raw('COUNT(*) as ditindaklanjuti'),
                DB::raw("SUM(CASE WHEN LOWER({$statusCol}) LIKE '%sudah%' THEN 1 ELSE 0 END) as sudah_lanjut"),
                DB::raw("SUM(CASE WHEN LOWER({$statusCol}) LIKE '%tidak%' THEN 1 ELSE 0 END) as tidak_lanjut")
            )
            ->whereNull('tl.deleted_at')
            ->whereNull('ats.deleted_at')
            ->groupBy('ats.kabupaten')
            ->get()
            ->keyBy('kabupaten');

        $byKabupatenTindakLanjut = [];
        foreach ($byKabupaten as $kabItem) {
            $kabName = $kabItem['nama'];
            $stat = $tlStatsByKab->get($kabName);
            $byKabupatenTindakLanjut[] = [
                'nama'            => $kabName,
                'ditindaklanjuti' => (int) ($stat->ditindaklanjuti ?? 0),
                'sudah_lanjut'    => (int) ($stat->sudah_lanjut ?? 0),
                'tidak_lanjut'    => (int) ($stat->tidak_lanjut ?? 0),
            ];
        }

        return [
            'status' => 'success',
            'meta'   => [
                'generated_at'            => now()->toIso8601String(),
                'total_records_processed' => $totalAts,
                'version'                 => '1.0',
            ],
            'data'   => [
                'overview' => [
                    'total_ats'                    => $totalAts,
                    'total_kabupaten'              => count($byKabupaten),
                    'total_laki_laki'              => (int) ($genderCounts['L'] ?? 0),
                    'total_perempuan'              => (int) ($genderCounts['P'] ?? 0),
                    'total_drop_out'               => (int) ($statusCounts['DO'] ?? 0),
                    'total_lulus_tidak_melanjutkan' => (int) ($statusCounts['LTM'] ?? 0),
                    'puncak_transisi_sd_kelas_6'   => $puncakSd,
                    'puncak_transisi_smp_kelas_9'  => $puncakSmp,
                ],
                'by_kabupaten' => $byKabupaten,
                'by_jenjang'   => [
                    'sd'      => $totalSd,
                    'smp'     => $totalSmp,
                    'sma_smk' => $totalSma,
                ],
                'by_status'    => [
                    'drop_out'               => (int) ($statusCounts['DO'] ?? 0),
                    'lulus_tidak_melanjutkan' => (int) ($statusCounts['LTM'] ?? 0),
                ],
                'by_kelas'     => $byKelas,
                'by_gender_and_reasons' => $byGenderAndReasons,
                'tindak_lanjut' => [
                    'total_ditindaklanjuti'   => $totalDitindaklanjuti,
                    'total_sudah_lanjut'       => $totalSudahLanjut,
                    'total_tidak_lanjut'       => $totalTidakLanjut,
                    'persentase_sudah_lanjut' => $persentaseSudahLanjut,
                    'by_kabupaten'            => $byKabupatenTindakLanjut,
                ],
            ],
        ];
    }

    /**
     * Generate tanda tangan HMAC-SHA256 dari JSON string payload.
     *
     * @param array $payload
     * @param string|null $secret
     * @return string
     */
    public function generateSignature(array $payload, ?string $secret = null): string
    {
        $secretKey = $secret ?: env('MITIGASI_WEBHOOK_SECRET', 'secret_hmac_webhook_ats_2026');
        $rawPayloadJson = json_encode($payload);
        return hash_hmac('sha256', $rawPayloadJson, $secretKey);
    }

    /**
     * Menerbitkan data ringkasan secara instan (PUSH) ke endpoint webhook Sistem Mitigasi ATS.
     * Mendukung fallback otomatis host.docker.internal <-> localhost.
     *
     * @return array Response detail proses push
     */
    public function pushToMitigasi(): array
    {
        // 1. Hitung agregasi terbaru secara fresh
        $payload = $this->getAggregatedSummary(true);

        // 2. Siapkan parameter keamanan
        $secret = env('MITIGASI_WEBHOOK_SECRET', 'secret_hmac_webhook_ats_2026');
        $bearerToken = env('MITIGASI_SHARED_KEY', 'secret_bearer_token_ats_2026');
        $signature = $this->generateSignature($payload, $secret);
        $timestamp = now()->toIso8601String();

        // 3. Tentukan URL webhook target
        $targetUrl = env('MITIGASI_WEBHOOK_URL', 'http://host.docker.internal:8004/api/v1/sync/ats-summary');

        // Daftar URL kandidat untuk resiliensi koneksi Docker & Localhost
        $urlsToTry = [$targetUrl];
        if (str_contains($targetUrl, 'localhost')) {
            $urlsToTry[] = str_replace('localhost', 'host.docker.internal', $targetUrl);
        } elseif (str_contains($targetUrl, 'host.docker.internal')) {
            $urlsToTry[] = str_replace('host.docker.internal', 'localhost', $targetUrl);
            $urlsToTry[] = str_replace('host.docker.internal', '172.20.0.1', $targetUrl);
        }

        $headers = [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $bearerToken,
            'X-Signature'   => $signature,
            'X-Timestamp'   => $timestamp,
        ];

        $lastError = null;
        $response = null;

        foreach ($urlsToTry as $url) {
            try {
                $res = Http::timeout(10)
                    ->withHeaders($headers)
                    ->post($url, $payload);

                if ($res->successful()) {
                    Log::info("[MitigasiSync] Sukses mengirim data ringkasan ke: {$url}", [
                        'status' => $res->status(),
                        'records' => $payload['meta']['total_records_processed'],
                    ]);

                    return [
                        'success'  => true,
                        'message'  => 'Data ringkasan ATS berhasil diterbitkan ke Portal Mitigasi.',
                        'url_used' => $url,
                        'status'   => $res->status(),
                        'response' => $res->json(),
                        'summary'  => [
                            'total_records' => $payload['meta']['total_records_processed'],
                            'timestamp'     => $timestamp,
                            'signature'     => substr($signature, 0, 16) . '...',
                        ],
                    ];
                }

                $lastError = "HTTP {$res->status()}: " . $res->body();
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        Log::error("[MitigasiSync] Gagal menerbitkan ringkasan ke Sistem Mitigasi.", [
            'error' => $lastError,
            'urls'  => $urlsToTry,
        ]);

        return [
            'success' => false,
            'message' => 'Gagal menerbitkan ke Sistem Mitigasi: ' . $lastError,
            'error'   => $lastError,
        ];
    }
}
