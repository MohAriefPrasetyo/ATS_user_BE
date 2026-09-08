<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Dalam arsitektur microservices terdistribusi, tabel users dikelola oleh ats_gateway.
     * Foreign key constraint lokal pada tindak_lanjut.user_id dihilangkan agar ID pengguna
     * dari gateway dapat disimpan secara fleksibel tanpa pelanggaran referensial lokal.
     */
    public function up(): void
    {
        $foreignKeys = \Illuminate\Support\Facades\DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE CONSTRAINT_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'tindak_lanjut' 
              AND CONSTRAINT_NAME = 'tindak_lanjut_user_id_foreign'
        ");

        if (!empty($foreignKeys)) {
            Schema::table('tindak_lanjut', function (Blueprint $table) {
                $table->dropForeign('tindak_lanjut_user_id_foreign');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tindak_lanjut', function (Blueprint $table) {
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
