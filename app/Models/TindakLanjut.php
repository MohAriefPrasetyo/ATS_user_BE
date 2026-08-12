<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TindakLanjut extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tindak_lanjut';

    protected $fillable = [
        'anak_tidak_sekolah_id',
        'user_id',
        'keterangan',
        'alasan',
        'program_intervensi',
        'dokumen_pendukung_path',
        'foto_dokumentasi_path',
    ];

    /**
     * Relasi ke Anak Tidak Sekolah (ATS)
     */
    public function anakTidakSekolah(): BelongsTo
    {
        return $this->belongsTo(AnakTidakSekolah::class, 'anak_tidak_sekolah_id');
    }

    /**
     * Relasi ke User / Petugas Penginput
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
