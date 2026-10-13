<?php

namespace App\Http\Requests\Baak;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Pembuatan Akun dan Profil Kampus
// ==========================================
class SimpanPenggunaRequest extends FormRequest
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Otorisasi Pengelolaan Akun oleh BAAK
    // ==========================================
    public function authorize(): bool
    {
        return $this->user() !== null && session('PERANAKTIF') === 'BAAK';
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Aturan Validasi Akun Baru
    // ==========================================
    public function rules(): array
    {
        return [
            'JENISPERAN' => ['required', Rule::in(['MAHASISWA', 'DOSEN', 'PIMPINAN', 'BAAK'])],
            'USERNAME' => ['required', 'string', 'max:255', 'unique:PENGGUNA,USERNAME'],
            'PASSWORD' => ['required', 'string', 'min:12', 'confirmed'],
            'NAMALENGKAP' => ['required', 'string', 'max:255'],
            'EMAIL' => ['nullable', 'email', 'max:255', 'unique:PENGGUNA,EMAIL'],
            'NOMORTELEPON' => ['nullable', 'string', 'max:255', 'unique:PENGGUNA,NOMORTELEPON'],
            'STATUSAKUN' => ['required', Rule::in(['AKTIF', 'NONAKTIF'])],
            'NIM' => ['required_if:JENISPERAN,MAHASISWA', 'prohibited_unless:JENISPERAN,MAHASISWA', 'nullable', 'string', 'max:255', 'unique:MAHASISWA,NIM'],
            'ANGKATAN' => ['required_if:JENISPERAN,MAHASISWA', 'prohibited_unless:JENISPERAN,MAHASISWA', 'nullable', 'digits:4'],
            'NIDN' => ['required_if:JENISPERAN,DOSEN,PIMPINAN', 'prohibited_unless:JENISPERAN,DOSEN,PIMPINAN', 'nullable', 'string', 'max:255', 'unique:DOSEN,NIDN'],
            'NIP' => ['prohibited_unless:JENISPERAN,DOSEN,PIMPINAN', 'nullable', 'string', 'max:255'],
            'PROGRAMSTUDI' => ['required_if:JENISPERAN,MAHASISWA,DOSEN,PIMPINAN', 'prohibited_unless:JENISPERAN,MAHASISWA,DOSEN,PIMPINAN', 'nullable', 'string', 'max:255'],
            'JABATAN' => ['required_if:JENISPERAN,PIMPINAN', 'prohibited_unless:JENISPERAN,PIMPINAN', 'nullable', 'string', 'max:255'],
            'LEVELPERSETUJUAN' => ['required_if:JENISPERAN,PIMPINAN', 'prohibited_unless:JENISPERAN,PIMPINAN', 'nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}
