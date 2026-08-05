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
        Schema::create('anak_tidak_sekolah', function (Blueprint $table) {
            $table->id();

            // 43 Kolom Murni Presisi 1-to-1 dengan File Excel
            $table->string('sekolah_id')->nullable();
            $table->string('tahun', 20)->nullable();
            $table->string('semester_id', 20)->nullable();
            $table->string('peserta_didik_id')->nullable();
            $table->string('nisn', 20)->nullable()->index();
            $table->string('nik', 16)->nullable()->index();
            $table->string('no_kk', 16)->nullable();
            $table->string('nama')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('nama_ibu_kandung')->nullable();
            
            // Wilayah Administrasi (By Address)
            $table->string('kode_provinsi', 20)->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_kabupaten', 20)->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('kode_kecamatan', 20)->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kode_desa_kelurahan', 20)->nullable();
            $table->string('desa_kelurahan')->nullable();
            $table->string('kode_wilayah', 30)->nullable();
            $table->string('kode_dagri', 30)->nullable();
            
            // Alamat & Koordinat Presisi
            $table->text('alamat_jalan')->nullable();
            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->decimal('lintang', 10, 7)->nullable();
            $table->decimal('bujur', 10, 7)->nullable();

            // Status Approval & Validasi System
            $table->string('status_approval')->nullable();
            $table->text('status_approval_keterangan')->nullable();
            $table->string('status_validasi')->nullable();
            $table->string('status')->nullable();
            $table->text('keterangan_approval')->nullable();
            $table->string('alasan_approval_id')->nullable();
            $table->text('alasan_approval_keterangan')->nullable();
            $table->text('keterangan_tolak')->nullable();
            $table->text('alasan_lainnya')->nullable();
            $table->string('tingkat_pendidikan')->nullable();
            $table->string('kebutuhan_khusus_id')->nullable();
            $table->boolean('aktif')->default(true);

            // Audit Timestamps Asli Excel
            $table->timestamp('create_date')->nullable();
            $table->timestamp('last_update')->nullable();
            $table->timestamp('soft_delete_ats')->nullable();
            $table->text('unnamed_42')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anak_tidak_sekolah');
    }
};
