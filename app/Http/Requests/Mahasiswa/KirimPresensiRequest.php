<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Kiriman Presensi Mahasiswa
// ==========================================
class KirimPresensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'FOTOPRESENSI' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'LINTANG' => ['required', 'numeric', 'between:-90,90'],
            'BUJUR' => ['required', 'numeric', 'between:-180,180'],
            'AKURASIGPS' => ['required', 'numeric', 'between:0,10000'],
            'TANTANGAN' => ['required', 'string', 'size:48'],
            'LIVENESSLOLOS' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'FOTOPRESENSI.required' => 'Foto langsung dari kamera wajib dikirim.',
            'FOTOPRESENSI.mimes' => 'Foto harus berformat JPG, PNG, atau WebP.',
            'FOTOPRESENSI.max' => 'Ukuran foto tidak boleh melebihi 4 MB.',
            'TANTANGAN.size' => 'Tantangan presensi sudah tidak berlaku. Muat ulang halaman.',
            'LIVENESSLOLOS.accepted' => 'Selesaikan tantangan liveness sebelum mengirim presensi.',
        ];
    }
}
