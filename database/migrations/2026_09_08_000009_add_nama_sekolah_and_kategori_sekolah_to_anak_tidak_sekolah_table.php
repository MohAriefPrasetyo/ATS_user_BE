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
        Schema::table('anak_tidak_sekolah', function (Blueprint $table) {
            $table->string('nama_sekolah', 255)->nullable()->after('sekolah_id');
            $table->string('kategori_sekolah', 50)->nullable()->index()->after('nama_sekolah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anak_tidak_sekolah', function (Blueprint $table) {
            $table->dropIndex(['kategori_sekolah']);
            $table->dropColumn(['nama_sekolah', 'kategori_sekolah']);
        });
    }
};
