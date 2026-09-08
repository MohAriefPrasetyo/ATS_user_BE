<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tindak_lanjut') && !Schema::hasTable('asesmen')) {
            Schema::rename('tindak_lanjut', 'asesmen');
        } elseif (!Schema::hasTable('asesmen')) {
            Schema::create('asesmen', function (Blueprint $table) {
                $table->id();
                $table->foreignId('anak_tidak_sekolah_id')
                      ->constrained('anak_tidak_sekolah')
                      ->onDelete('cascade');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('keterangan')->nullable();
                $table->text('alasan')->nullable();
                $table->text('program_intervensi')->nullable();
                $table->date('tanggal_asesmen')->nullable();
                $table->date('tanggal_tindak_lanjut')->nullable();
                $table->string('dokumen_pendukung_path')->nullable();
                $table->string('foto_dokumentasi_path')->nullable();
                $table->string('foto_rumah_path')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Pastikan kolom keterangan nullable dan kolom tanggal_asesmen tersedia
        Schema::table('asesmen', function (Blueprint $table) {
            if (Schema::hasColumn('asesmen', 'keterangan')) {
                $table->string('keterangan')->nullable()->change();
            }
            if (!Schema::hasColumn('asesmen', 'tanggal_asesmen')) {
                $table->date('tanggal_asesmen')->nullable()->after('program_intervensi');
            }
        });

        // Salin data tanggal lama ke tanggal_asesmen jika ada
        if (Schema::hasColumn('asesmen', 'tanggal_tindak_lanjut') && Schema::hasColumn('asesmen', 'tanggal_asesmen')) {
            DB::statement('UPDATE asesmen SET tanggal_asesmen = tanggal_tindak_lanjut WHERE tanggal_asesmen IS NULL AND tanggal_tindak_lanjut IS NOT NULL');
        }

        // Buat VIEW tindak_lanjut agar query legacy tetap kompatibel 100%
        try {
            DB::statement('CREATE OR REPLACE VIEW tindak_lanjut AS SELECT * FROM asesmen');
        } catch (\Throwable $e) {
            // Abaikan jika view creation tidak didukung
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('DROP VIEW IF EXISTS tindak_lanjut');
        } catch (\Throwable $e) {}

        if (Schema::hasTable('asesmen') && !Schema::hasTable('tindak_lanjut')) {
            Schema::rename('asesmen', 'tindak_lanjut');
        }
    }
};
