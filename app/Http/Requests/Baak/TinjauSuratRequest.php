<?php

namespace App\Http\Requests\Baak;

use Illuminate\Foundation\Http\FormRequest;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Keputusan Verifikasi Surat BAAK
// ==========================================
class TinjauSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && session('PERANAKTIF') === 'BAAK';
    }

    public function rules(): array
    {
        return [
            'KEPUTUSAN' => ['required', 'in:LANJUT,REVISI'],
            'CEKLIST' => ['required_if:KEPUTUSAN,LANJUT', 'array'],
            'CEKLIST.*' => ['in:IDENTITAS,KEPERLUAN,LAMPIRAN', 'distinct'],
            'CATATAN' => ['required_if:KEPUTUSAN,REVISI', 'nullable', 'string', 'max:2000'],
        ];
    }
}
