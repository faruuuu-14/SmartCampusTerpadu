<?php

namespace App\Http\Requests\Dosen;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Keputusan Peninjauan Presensi
// ==========================================
class TinjauPresensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'KEPUTUSAN' => ['required', Rule::in(['HADIR', 'DITOLAK'])],
        ];
    }
}
