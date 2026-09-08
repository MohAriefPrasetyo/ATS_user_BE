<?php

namespace Tests\Feature;

use Tests\TestCase;

class KelurahanFilteringTest extends TestCase
{
    /**
     * Uji pemfilteran akun Admin Kelurahan Bungin:
     * - Hanya mengembalikan data dengan kelurahan 'Bungin' (case-insensitive e.g. Bungin & BUNGIN)
     * - Tidak memunculkan Tobungin, Bungintimbe, Bungintende, Bungingkela, Bungin Luean, dll.
     */
    public function test_admin_kelurahan_bungin_only_receives_exact_matching_records(): void
    {
        $response = $this->withHeaders([
            'X-User-Id'         => '2',
            'X-User-Role'       => 'admin',
            'X-User-Assignment' => 'kelurahan',
            'X-User-Kabupaten'  => 'Kab. Banggai',
            'X-User-Kecamatan'  => 'Luwuk',
            'X-User-Kelurahan'  => 'Bungin',
        ])->getJson('/api/ats');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $data = $response->json('data.data');

        // Pastikan hanya tepat 2 data asli Kelurahan Bungin di Kab. Banggai yang kembali
        $this->assertCount(2, $data);

        $names = array_column($data, 'nama');
        $this->assertContains('Fanes albahri mointi', $names);
        $this->assertContains('CHRISTIAN SAMUEL BALAHANTI', $names);

        // Pastikan tidak ada data kontaminasi parsial
        foreach ($data as $item) {
            $desa = strtoupper(trim($item['desa_kelurahan']));
            $this->assertEquals('BUNGIN', $desa);
            $this->assertEquals('Kab. Banggai', $item['kabupaten']);
        }
    }

    /**
     * Uji pemfilteran melalui query parameter kelurahan secara case-insensitive
     */
    public function test_query_filter_kelurahan_case_insensitive(): void
    {
        // Request dengan huruf kecil
        $responseLower = $this->getJson('/api/ats?kelurahan=bungin&kabupaten=Kab.+Banggai');
        $responseLower->assertStatus(200);
        $this->assertEquals(2, $responseLower->json('data.total'));

        // Request dengan huruf kapital
        $responseUpper = $this->getJson('/api/ats?kelurahan=BUNGIN&kabupaten=Kab.+Banggai');
        $responseUpper->assertStatus(200);
        $this->assertEquals(2, $responseUpper->json('data.total'));
    }
}
