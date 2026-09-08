<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
     * Relasi 1-to-1 ke Data Asesmen Lapangan
     */
    public function asesmen(): HasOne
    {
        return $this->hasOne(Asesmen::class, 'anak_tidak_sekolah_id');
    }

    /**
     * Relasi ke Asesmen Lapangan (Collection)
     */
    public function asesmens(): HasMany
    {
        return $this->hasMany(Asesmen::class, 'anak_tidak_sekolah_id');
    }

    /**
     * Relasi ke Tindak Lanjut (Kompatibilitas Legacy)
     */
    public function tindakLanjuts(): HasMany
    {
        return $this->hasMany(Asesmen::class, 'anak_tidak_sekolah_id');
    }

    public function tindakLanjut(): HasOne
    {
        return $this->hasOne(Asesmen::class, 'anak_tidak_sekolah_id');
    }

    /**
     * Scope query filter terpusat untuk Data ATS
     */
    public function scopeFilter(Builder $query, Request $request): Builder
    {
        $query->with(['asesmen.user', 'tindakLanjuts.user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kecamatan')) {
            $query->whereKecamatan($request->kecamatan);
        }

        if ($request->filled('kabupaten')) {
            $query->whereKabupaten($request->kabupaten);
        }

        if ($request->filled('desa_kelurahan')) {
            $query->whereDesaKelurahan($request->desa_kelurahan);
        } elseif ($request->filled('kelurahan')) {
            $query->whereDesaKelurahan($request->kelurahan);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter status Asesmen / Tindak Lanjut (Mendukung istilah baru & legacy)
        $filterAsesmen = $request->get('filter_asesmen', $request->get('filter_tindak_lanjut'));
        if ($filterAsesmen) {
            if (in_array($filterAsesmen, ['sudah_diasesmen', 'sudah_ditindaklanjuti', 'sudah'])) {
                $query->has('asesmens');
            } elseif (in_array($filterAsesmen, ['belum_diasesmen', 'belum_ditindaklanjuti', 'belum'])) {
                $query->doesntHave('asesmens');
            }
        }

        if ($request->filled('keterangan_tindak_lanjut')) {
            $keterangan = $request->keterangan_tindak_lanjut;
            $query->whereHas('asesmens', function ($q) use ($keterangan) {
                $q->where('keterangan', $keterangan);
            });
        }

        return $query;
    }

    /**
     * Scope filter toleran untuk Kabupaten / Kota
     */
    public function scopeWhereKabupaten(Builder $query, ?string $kabupaten): Builder
    {
        if (empty($kabupaten) || str_contains(strtolower($kabupaten), 'provinsi')) {
            return $query;
        }

        $clean = trim(preg_replace('/^(kabupaten|kab\.|kab|kota)\s*/i', '', trim($kabupaten)));
        if ($clean === '') {
            return $query;
        }

        $escaped = preg_quote($clean, '/');
        $patternPart = preg_replace('/(\\\\\-|\s+|,)+/', '[-–—,[:space:]]+', $escaped);
        $regex = '(?i)^((kabupaten|kab\.|kab|kota)[.][[:space:]]*|(kabupaten|kab\.|kab|kota)[[:space:]]+)?' . $patternPart . '$';

        return $query->where(function ($q) use ($clean, $regex) {
            $q->whereRaw('LOWER(TRIM(kabupaten)) = ?', [mb_strtolower($clean)])
              ->orWhereRaw('TRIM(kabupaten) REGEXP ?', [$regex]);
        });
    }

    /**
     * Scope filter toleran untuk Kecamatan
     */
    public function scopeWhereKecamatan(Builder $query, ?string $kecamatan): Builder
    {
        if (empty($kecamatan)) {
            return $query;
        }

        $clean = trim(preg_replace('/^(kecamatan|kec\.|kec)\s*/i', '', trim($kecamatan)));
        if ($clean === '') {
            return $query;
        }

        $escaped = preg_quote($clean, '/');
        $patternPart = preg_replace('/(\\\\\-|\s+|,)+/', '[-–—,[:space:]]+', $escaped);
        $regex = '(?i)^((kecamatan|kec\.|kec)[.][[:space:]]*|(kecamatan|kec\.|kec)[[:space:]]+)?' . $patternPart . '$';

        return $query->where(function ($q) use ($clean, $regex) {
            $q->whereRaw('LOWER(TRIM(kecamatan)) = ?', [mb_strtolower($clean)])
              ->orWhereRaw('TRIM(kecamatan) REGEXP ?', [$regex]);
        });
    }

    /**
     * Scope filter toleran & presisi untuk Desa / Kelurahan.
     * Mengabaikan perbedaan huruf kapital/kecil (case-insensitive),
     * mentolerir awalan administratif (Desa, DESA., Kel., Kelurahan, Ds., dll.),
     * namun HANYA mencocokkan kelurahan yang sama persis (bukan substring parsial seperti Tobungin untuk Bungin).
     */
    public function scopeWhereDesaKelurahan(Builder $query, ?string $kelurahan): Builder
    {
        if (empty($kelurahan)) {
            return $query;
        }

        $clean = trim(preg_replace('/^(kelurahan|keluarahan|desa\.|desa|kel\.|ds\.)\s*/i', '', trim($kelurahan)));
        if ($clean === '') {
            return $query;
        }

        $escaped = preg_quote($clean, '/');
        $patternPart = preg_replace('/(\\\\\-|\s+|,)+/', '[-–—,[:space:]]+', $escaped);
        $regex = '(?i)^((desa|kelurahan|keluarahan|kel|ds)[.][[:space:]]*|(desa|kelurahan|keluarahan|kel|ds)[[:space:]]+)?' . $patternPart . '$';

        return $query->where(function ($q) use ($clean, $regex) {
            $q->whereRaw('LOWER(TRIM(desa_kelurahan)) = ?', [mb_strtolower($clean)])
              ->orWhereRaw('TRIM(desa_kelurahan) REGEXP ?', [$regex]);
        });
    }

    /**
     * Scope otomatis berdasarkan hak akses dan penugasan wilayah Admin.
     */
    public function scopeForAdminContext(Builder $query, Request $request): Builder
    {
        $user = $request->user() ?? \Illuminate\Support\Facades\Auth::user();
        $userRole = $user?->role ?? $request->header('X-User-Role');

        if ($userRole === 'admin') {
            $assignment = $user?->jenis_penugasan ?? $request->header('X-User-Assignment');
            $sekolahId = $user?->sekolah_id ?? $request->header('X-User-Sekolah-Id');
            $kelurahan = $user?->kelurahan ?? $request->header('X-User-Kelurahan');
            $kecamatan = $user?->kecamatan ?? $request->header('X-User-Kecamatan');
            $kabupaten = $user?->kabupaten ?? $request->header('X-User-Kabupaten');

            if ($assignment === 'sekolah' && !empty($sekolahId)) {
                $query->where('sekolah_id', $sekolahId);
            } elseif ($assignment === 'kelurahan' && !empty($kelurahan)) {
                if (!empty($kabupaten) && $kabupaten !== 'Provinsi Sulawesi Tengah') {
                    $query->whereKabupaten($kabupaten);
                }
                if (!empty($kecamatan)) {
                    $query->whereKecamatan($kecamatan);
                }
                $query->whereDesaKelurahan($kelurahan);
            } elseif ($assignment === 'kecamatan' && !empty($kecamatan)) {
                if (!empty($kabupaten) && $kabupaten !== 'Provinsi Sulawesi Tengah') {
                    $query->whereKabupaten($kabupaten);
                }
                $query->whereKecamatan($kecamatan);
            } elseif (!empty($kabupaten) && $kabupaten !== 'Provinsi Sulawesi Tengah') {
                $query->whereKabupaten($kabupaten);
            }
        }

        return $query;
    }
}
