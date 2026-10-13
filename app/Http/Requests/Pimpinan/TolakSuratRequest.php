<?php

namespace App\Http\Requests\Pimpinan;

use Illuminate\Foundation\Http\FormRequest;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Penolakan Surat oleh Pimpinan
// ==========================================
class TolakSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && session('PERANAKTIF') === 'PIMPINAN';
    }

    public function rules(): array
    {
        return [
            'CATATAN' => ['required', 'string', 'max:2000'],
        ];
    }
}
