<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnakTidakSekolah extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'anak_tidak_sekolah';

    protected $fillable = [
        'sekolah_id',
        'tahun',
        'semester_id',
        'peserta_didik_id',
        'nisn',
        'nik',
        'no_kk',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'nama_ibu_kandung',
        'kode_provinsi',
        'provinsi',
        'kode_kabupaten',
        'kabupaten',
        'kode_kecamatan',
        'kecamatan',
        'kode_desa_kelurahan',
        'desa_kelurahan',
        'kode_wilayah',
        'kode_dagri',
        'alamat_jalan',
        'rt',
        'rw',
        'lintang',
        'bujur',
        'status_approval',
        'status_approval_keterangan',
        'status_validasi',
        'status',
        'keterangan_approval',
        'alasan_approval_id',
        'alasan_approval_keterangan',
        'keterangan_tolak',
        'alasan_lainnya',
        'tingkat_pendidikan',
        'kebutuhan_khusus_id',
        'aktif',
        'create_date',
        'last_update',
        'soft_delete_ats',
        'unnamed_42',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'aktif' => 'boolean',
        'create_date' => 'datetime',
        'last_update' => 'datetime',
        'soft_delete_ats' => 'datetime',
    ];

    /**
     * Relasi ke Tindak Lanjut
     */
    public function tindakLanjuts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'anak_tidak_sekolah_id');
    }
}

