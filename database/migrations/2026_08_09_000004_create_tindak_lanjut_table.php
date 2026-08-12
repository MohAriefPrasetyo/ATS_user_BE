<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tindak_lanjut', function (Blueprint $table) {
            $table->id();

            // Relasi ke Data Anak Tidak Sekolah
            $table->foreignId('anak_tidak_sekolah_id')
                  ->constrained('anak_tidak_sekolah')
                  ->onDelete('cascade');

            // Relasi ke User / Petugas Penginput
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            // Form Fields Sesuai UI (Gambar)
            $table->string('keterangan'); // Dropdown select keterangan/status tindak lanjut
            $table->text('alasan')->nullable(); // Textarea alasan / rincian tindak lanjut
            $table->string('dokumen_pendukung_path')->nullable(); // Path file dokumen/surat pendukung (max 10MB)
            $table->string('foto_dokumentasi_path')->nullable(); // Path foto dokumentasi kunjungan (max 10MB)

            // Kolom Tambahan Pendukung Operasional ATS
            $table->text('program_intervensi')->nullable(); // Program intervensi yang disarankan (misal: Beasiswa, Paket B, PKH, dll)
            $table->date('tanggal_tindak_lanjut')->nullable(); // Tanggal pelaksanaan kunjungan/tindak lanjut





            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut');
    }
};
