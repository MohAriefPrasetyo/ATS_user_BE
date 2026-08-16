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
        Schema::create('riwayat_import', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('periode_data')->nullable(); // Contoh: "Periode #1", "Periode #2", "2025/2026"
            $table->string('nama_berkas'); // Contoh: "ats_sigi_gumbasa_val.csv"
            $table->integer('data_sukses')->default(0); // Jumlah baris yang berhasil diimpor
            $table->integer('data_duplikat')->default(0); // Jumlah baris yang dilewati / duplikat
            $table->string('status')->default('Selesai'); // Status: "Selesai", "Gagal", "Proses"
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_import');
    }
};
