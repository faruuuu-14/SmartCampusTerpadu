<?php

namespace App\Http\Requests\Dosen;

use App\Models\Pengguna;
use Illuminate\Foundation\Http\FormRequest;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Tugas Baru pada Jadwal Milik Dosen
// ==========================================
class BuatTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pengguna = $this->user();

        return session('PERANAKTIF') === 'DOSEN'
            && $pengguna instanceof Pengguna
            && $pengguna->dosen()->whereHas('jadwal', fn ($query) => $query->whereKey($this->input('IDJADWAL')))->exists();
    }

    public function rules(): array
    {
        return [
            'IDJADWAL' => ['required', 'integer', 'exists:JADWAL,IDJADWAL'],
            'JUDULTUGAS' => ['required', 'string', 'max:255'],
            'DESKRIPSI' => ['nullable', 'string', 'max:10000'],
            'TANGGALTENGGAT' => ['required', 'date', 'after:now'],
            'MODEPENGERJAAN' => ['required', 'in:BEBAS,WAJIBKELAS'],
        ];
    }
}
