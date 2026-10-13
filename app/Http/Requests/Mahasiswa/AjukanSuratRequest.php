<?php

namespace App\Http\Requests\Mahasiswa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// ==========================================
// PEMBAHASAN / BAGIAN: Validasi Pengajuan Surat Mahasiswa
// ==========================================
class AjukanSuratRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->request->has('DATAFORMULIR')) {
            $this->merge(['DATAFORMULIR' => []]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->mahasiswa()->exists()
            && session('PERANAKTIF') === 'MAHASISWA';
    }

    public function rules(): array
    {
        return [
            'JENISSURAT' => ['required', Rule::in([
                'SURATAKTIF',
                'TRANSKRIP',
                'MAGANG',
                'DISPENSASI',
                'IZINSAKIT',
                'PROPOSALORMAWA',
                'LPJORMAWA',
            ])],
            'PERIHAL' => ['required', 'string', 'max:255'],
            'KEPERLUAN' => ['required', 'string', 'max:3000'],
            'DATAFORMULIR' => ['present', 'array:NAMAPERUSAHAAN,ALAMATPERUSAHAAN,TANGGALMULAI,TANGGALSELESAI,NAMAKEGIATAN,ALASAN'],
            'DATAFORMULIR.NAMAPERUSAHAAN' => ['nullable', 'string', 'max:200', 'required_if:JENISSURAT,MAGANG'],
            'DATAFORMULIR.ALAMATPERUSAHAAN' => ['nullable', 'string', 'max:500', 'required_if:JENISSURAT,MAGANG'],
            'DATAFORMULIR.TANGGALMULAI' => ['nullable', 'date', 'required_if:JENISSURAT,MAGANG,DISPENSASI,IZINSAKIT'],
            'DATAFORMULIR.TANGGALSELESAI' => ['nullable', 'date', 'after_or_equal:DATAFORMULIR.TANGGALMULAI', 'required_if:JENISSURAT,MAGANG,DISPENSASI,IZINSAKIT'],
            'DATAFORMULIR.NAMAKEGIATAN' => ['nullable', 'string', 'max:200', 'required_if:JENISSURAT,DISPENSASI,PROPOSALORMAWA,LPJORMAWA'],
            'DATAFORMULIR.ALASAN' => ['nullable', 'string', 'max:2000', 'required_if:JENISSURAT,DISPENSASI,IZINSAKIT,PROPOSALORMAWA,LPJORMAWA'],
            'FILELAMPIRAN' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'JENISSURAT.in' => 'Jenis surat yang dipilih tidak tersedia.',
            'DATAFORMULIR.NAMAPERUSAHAAN.required_if' => 'Nama perusahaan wajib diisi untuk surat magang.',
            'DATAFORMULIR.TANGGALMULAI.required_if' => 'Tanggal mulai wajib diisi untuk jenis surat ini.',
            'DATAFORMULIR.TANGGALSELESAI.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'FILELAMPIRAN.mimes' => 'Lampiran harus berformat PDF, JPG, PNG, atau WebP.',
            'FILELAMPIRAN.max' => 'Ukuran lampiran tidak boleh melebihi 5 MB.',
        ];
    }
}
