<?php

namespace App\Http\Requests\Baak;

use App\Models\Krs;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Enrolmen KRS
// ==========================================
class SimpanKrsRequest extends FormRequest
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Otorisasi Pengelolaan KRS oleh BAAK
    // ==========================================
    public function authorize(): bool
    {
        return $this->user() !== null && session('PERANAKTIF') === 'BAAK';
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Aturan Pasangan Mahasiswa dan Jadwal yang Unik
    // ==========================================
    public function rules(): array
    {
        $krs = $this->route('krs');
        $aturanJadwal = Rule::unique('KRS', 'IDJADWAL')
            ->where('IDMAHASISWA', $this->input('IDMAHASISWA'));

        if ($krs instanceof Krs) {
            $aturanJadwal->ignore($krs->IDKRS, 'IDKRS');
        }

        return [
            'IDJADWAL' => ['required', 'integer', 'exists:JADWAL,IDJADWAL', $aturanJadwal],
            'IDMAHASISWA' => ['required', 'integer', 'exists:MAHASISWA,IDMAHASISWA'],
        ];
    }
}
