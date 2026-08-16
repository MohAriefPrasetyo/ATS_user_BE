<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Menggunakan firstOrCreate agar tidak error duplicate entry saat dikirim ulang
        User::firstOrCreate(
            ['email' => 'admin@ats.go.id'],
            [
                'name'     => 'Admin ATS',
                'password' => bcrypt('password'),
                'role'     => 'admin',
            ]
        );

        if (class_exists(AnakTidakSekolahSeeder::class)) {
            $this->call(AnakTidakSekolahSeeder::class);
        }

        $this->call(RiwayatImportSeeder::class);
    }
}
