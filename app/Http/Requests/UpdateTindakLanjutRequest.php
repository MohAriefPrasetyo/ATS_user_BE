<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTindakLanjutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keterangan'            => 'sometimes|required|string|max:255',
            'alasan'                => 'nullable|string',
            'program_intervensi'    => 'nullable|string',
            'tanggal_tindak_lanjut' => 'nullable|date',
            'dokumen_pendukung'     => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'foto_dokumentasi'      => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }
}
