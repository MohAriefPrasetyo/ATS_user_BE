<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiwayatImport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'riwayat_import';

    protected $fillable = [
        'user_id',
        'periode_data',
        'nama_berkas',
        'data_sukses',
        'data_duplikat',
        'status',
        'catatan',
    ];

    /**
     * Relasi ke User / Admin yang melakukan import
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
