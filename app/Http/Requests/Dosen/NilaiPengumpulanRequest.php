<?php

namespace App\Http\Requests\Dosen;

use App\Models\Pengguna;
use App\Models\Pengumpulan;
use Illuminate\Foundation\Http\FormRequest;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Nilai dan Umpan Balik Dosen
// ==========================================
class NilaiPengumpulanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pengguna = $this->user();
        $pengumpulan = $this->route('pengumpulan');

        return session('PERANAKTIF') === 'DOSEN'
            && $pengguna instanceof Pengguna
            && $pengumpulan instanceof Pengumpulan
            && $pengumpulan->tugas()->where('IDDOSEN', $pengguna->dosen?->IDDOSEN)->exists();
    }

    public function rules(): array
    {
        return [
            'NILAI' => ['required', 'numeric', 'between:0,100'],
            'UMPANBALIK' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
