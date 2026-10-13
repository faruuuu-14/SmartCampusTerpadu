<?php

namespace App\Http\Requests\Pimpinan;

use Illuminate\Foundation\Http\FormRequest;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Autentikasi Ulang TTE Pimpinan
// ==========================================
class SetujuiSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && session('PERANAKTIF') === 'PIMPINAN';
    }

    public function rules(): array
    {
        return [
            'PASSWORD' => ['required', 'string', 'max:200'],
        ];
    }
}
