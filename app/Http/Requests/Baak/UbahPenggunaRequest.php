<?php

namespace App\Http\Requests\Baak;

use App\Models\Pengguna;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Perubahan Akun dan Profil
// ==========================================
class UbahPenggunaRequest extends FormRequest
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Otorisasi Perubahan Data Pengguna
    // ==========================================
    public function authorize(): bool
    {
        return $this->user() !== null && session('PERANAKTIF') === 'BAAK';
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Aturan Validasi Berbasis Profil yang Terdaftar
    // ==========================================
    public function rules(): array
    {
        $pengguna = $this->route('pengguna');
        abort_unless($pengguna instanceof Pengguna, 404);

        return [
            'USERNAME' => ['required', 'string', 'max:255', Rule::unique('PENGGUNA', 'USERNAME')->ignore($pengguna->IDPENGGUNA, 'IDPENGGUNA')],
            'PASSWORD' => ['nullable', 'string', 'min:12', 'confirmed'],
            'NAMALENGKAP' => ['required', 'string', 'max:255'],
            'EMAIL' => ['nullable', 'email', 'max:255', Rule::unique('PENGGUNA', 'EMAIL')->ignore($pengguna->IDPENGGUNA, 'IDPENGGUNA')],
            'NOMORTELEPON' => ['nullable', 'string', 'max:255', Rule::unique('PENGGUNA', 'NOMORTELEPON')->ignore($pengguna->IDPENGGUNA, 'IDPENGGUNA')],
            'STATUSAKUN' => ['required', Rule::in(['AKTIF', 'NONAKTIF'])],
            'NIM' => [$pengguna->mahasiswa ? 'required' : 'prohibited', 'nullable', 'string', 'max:255', Rule::unique('MAHASISWA', 'NIM')->ignore($pengguna->mahasiswa?->IDMAHASISWA, 'IDMAHASISWA')],
            'ANGKATAN' => [$pengguna->mahasiswa ? 'required' : 'prohibited', 'nullable', 'digits:4'],
            'NIDN' => [$pengguna->dosen ? 'required' : 'prohibited', 'nullable', 'string', 'max:255', Rule::unique('DOSEN', 'NIDN')->ignore($pengguna->dosen?->IDDOSEN, 'IDDOSEN')],
            'NIP' => [$pengguna->dosen ? 'nullable' : 'prohibited', 'string', 'max:255'],
            'PROGRAMSTUDI' => [$pengguna->mahasiswa || $pengguna->dosen ? 'required' : 'prohibited', 'nullable', 'string', 'max:255'],
            'JABATAN' => [$pengguna->dosen?->JABATAN ? 'nullable' : 'prohibited', 'string', 'max:255'],
            'LEVELPERSETUJUAN' => [$pengguna->dosen?->JABATAN ? 'required' : 'prohibited', 'nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}
