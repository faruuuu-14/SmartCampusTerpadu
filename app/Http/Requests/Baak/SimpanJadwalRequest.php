<?php

namespace App\Http\Requests\Baak;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Data Jadwal Kuliah
// ==========================================
class SimpanJadwalRequest extends FormRequest
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Otorisasi Pengelolaan Jadwal oleh BAAK
    // ==========================================
    public function authorize(): bool
    {
        return $this->user() !== null && session('PERANAKTIF') === 'BAAK';
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Aturan Data Akademik dan Geofence
    // ==========================================
    public function rules(): array
    {
        return [
            'IDDOSEN' => ['required', 'integer', 'exists:DOSEN,IDDOSEN'],
            'KODEMATAKULIAH' => ['required', 'string', 'max:255'],
            'NAMAMATAKULIAH' => ['required', 'string', 'max:255'],
            'SKS' => ['required', 'integer', 'between:1,30'],
            'KELAS' => ['required', 'string', 'max:255'],
            'HARI' => ['required', Rule::in(['SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'])],
            'JAMMULAI' => ['required', 'date_format:H:i'],
            'JAMSELESAI' => ['required', 'date_format:H:i', 'after:JAMMULAI'],
            'RUANGAN' => ['required', 'string', 'max:255'],
            'LINTANG' => ['required', 'numeric', 'between:-90,90'],
            'BUJUR' => ['required', 'numeric', 'between:-180,180'],
            'RADIUSMETER' => ['required', 'integer', 'between:1,10000'],
            'MODEKEHADIRAN' => ['required', Rule::in(['WAJIBKELAS', 'BEBAS'])],
            'MENITPENGINGAT' => ['required', 'integer', 'between:0,1440'],
        ];
    }
}
