<?php

namespace App\Http\Requests\Autentikasi;

use Illuminate\Foundation\Http\FormRequest;

// ==========================================
// PEMBAHASAN / BAGIAN: Form Request Validasi Login
// ==========================================
class LoginRequest extends FormRequest
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Otorisasi Permintaan
    // ==========================================
    public function authorize(): bool
    {
        return true;
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Aturan Validasi Input
    // ==========================================
    public function rules(): array
    {
        return [
            'USERNAME' => ['required', 'string', 'max:50'],
            'PASSWORD' => ['required', 'string'],
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Pesan Kesalahan Bahasa Indonesia
    // ==========================================
    public function messages(): array
    {
        return [
            'USERNAME.required' => 'NIM / NIDN / Username wajib diisi.',
            'USERNAME.max' => 'Panjang username maksimal 50 karakter.',
            'PASSWORD.required' => 'Kata sandi wajib diisi.',
        ];
    }
}
