<?php

namespace App\Http\Requests\Mahasiswa;

use App\Models\Pengguna;
use App\Models\Tugas;
use Illuminate\Foundation\Http\FormRequest;

// ==========================================
// PEMBAHASAN / BAGIAN: Otorisasi dan Validasi Berkas Pengumpulan Tugas
// ==========================================
class KumpulkanTugasRequest extends FormRequest
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Memastikan Mahasiswa Terdaftar pada Kelas Tugas
    // ==========================================
    public function authorize(): bool
    {
        $pengguna = $this->user();
        $tugas = $this->route('tugas');

        return session('PERANAKTIF') === 'MAHASISWA'
            && $pengguna instanceof Pengguna
            && $pengguna->mahasiswa()
                ->whereHas('krs', fn ($query) => $query->where('IDJADWAL', $tugas instanceof Tugas ? $tugas->IDJADWAL : null))
                ->exists();
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Validasi Format dan Ukuran Berkas Tugas
    // ==========================================
    public function rules(): array
    {
        return [
            'BERKAS' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }
}
