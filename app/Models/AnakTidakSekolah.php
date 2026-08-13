<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

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
        'tanggal_lahir'   => 'date',
        'aktif'           => 'boolean',
        'create_date'     => 'datetime',
        'last_update'     => 'datetime',
        'soft_delete_ats' => 'datetime',
    ];

    /**
     * Relasi ke Tindak Lanjut
     */
    public function tindakLanjuts(): HasMany
    {
        return $this->hasMany(TindakLanjut::class, 'anak_tidak_sekolah_id');
    }

    /**
     * Scope query filter terpusat untuk Data ATS
     */
    public function scopeFilter(Builder $query, Request $request): Builder
    {
        $query->with('tindakLanjuts');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kecamatan')) {
            $query->where('kecamatan', $request->kecamatan);
        }

        if ($request->filled('kabupaten')) {
            $query->where('kabupaten', $request->kabupaten);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('filter_tindak_lanjut')) {
            $filter = $request->filter_tindak_lanjut;
            if ($filter === 'sudah_ditindaklanjuti') {
                $query->has('tindakLanjuts');
            } elseif ($filter === 'belum_ditindaklanjuti') {
                $query->doesntHave('tindakLanjuts');
            }
        }

        if ($request->filled('keterangan_tindak_lanjut')) {
            $keterangan = $request->keterangan_tindak_lanjut;
            $query->whereHas('tindakLanjuts', function ($q) use ($keterangan) {
                $q->where('keterangan', $keterangan);
            });
        }

        return $query;
    }
}
