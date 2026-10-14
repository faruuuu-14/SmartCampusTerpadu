<?php

// ==========================================
// PEMBAHASAN / BAGIAN: Persetujuan dan Pendaftaran Embedding Wajah
// ==========================================

namespace App\Layanan\Presensi;

use App\Models\Mahasiswa;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PendaftaranWajahLayanan
{
    public function __construct(private readonly KlienWajah $klien) {}

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function daftarkan(Mahasiswa $mahasiswa, UploadedFile $foto, bool $setuju): void
    {
        if (! $setuju) {
            throw ValidationException::withMessages([
                'persetujuan' => 'Persetujuan biometrik wajib diberikan sebelum pendaftaran wajah.',
            ]);
        }

        $model = (string) config('wajah.model');
        $respons = $this->klien->buatEmbedding($foto, $model);
        $dimensiModel = [
            'ArcFace' => 512,
            'FaceNet' => 512,
            'SFace' => 128,
        ][$model] ?? null;

        if (
            $dimensiModel === null
            ||
            ($respons['face_found'] ?? false) !== true
            || ($respons['face_count'] ?? null) !== 1
            || ($respons['model'] ?? null) !== $model
            || ! is_string($respons['embedding_base64'] ?? null)
            || ($respons['embedding_dimension'] ?? null) !== $dimensiModel
        ) {
            throw ValidationException::withMessages([
                'foto' => 'Pendaftaran memerlukan tepat satu wajah yang dapat diproses. Silakan coba kembali.',
            ]);
        }

        $embedding = base64_decode($respons['embedding_base64'], true);

        if (
            $embedding === false
            || strlen($embedding) !== (int) $respons['embedding_dimension'] * 4
            || ! in_array($model, ['ArcFace', 'FaceNet', 'SFace'], true)
        ) {
            throw new RuntimeException('Layanan wajah mengembalikan embedding yang tidak sesuai.');
        }

        $jalurFotoLama = $mahasiswa->FOTOREFERENSI;
        $jalurFoto = $foto->storeAs(
            'referensi-wajah',
            Str::uuid().'.jpg',
            'local',
        );

        if ($jalurFoto === false) {
            throw new RuntimeException('Foto referensi gagal disimpan pada penyimpanan privat.');
        }

        try {
            DB::transaction(function () use ($mahasiswa, $jalurFoto, $respons, $model): void {
                $mahasiswa->forceFill([
                    'TANGGALKONSENBIOMETRIK' => now(),
                    'EMBEDDINGWAJAH' => Crypt::encryptString($respons['embedding_base64']),
                    'FOTOREFERENSI' => $jalurFoto,
                    'MODELWAJAH' => $model,
                    'STATUSWAJAH' => 'MENUNGGU_VERIFIKASI',
                    'IDVERIFIKASIBIOMETRIK' => null,
                    'WAKTUTINJAUBIOMETRIK' => null,
                ])->save();
            });
        } catch (Throwable $kesalahan) {
            Storage::disk('local')->delete($jalurFoto);

            throw $kesalahan;
        }

        if (is_string($jalurFotoLama) && ! Storage::disk('local')->delete($jalurFotoLama)) {
            Log::error('Foto referensi lama tidak dapat dihapus setelah pendaftaran wajah diganti.');
            throw new RuntimeException('Pendaftaran berhasil, tetapi foto referensi sebelumnya gagal dihapus.');
        }
    }

    public function cabut(Mahasiswa $mahasiswa): void
    {
        $jalurFoto = $mahasiswa->FOTOREFERENSI;
        $mahasiswa->forceFill([
            'TANGGALKONSENBIOMETRIK' => null,
            'EMBEDDINGWAJAH' => null,
            'FOTOREFERENSI' => null,
            'MODELWAJAH' => null,
            'STATUSWAJAH' => 'DICABUT',
            'IDVERIFIKASIBIOMETRIK' => null,
            'WAKTUTINJAUBIOMETRIK' => null,
        ])->save();

        if (is_string($jalurFoto) && ! Storage::disk('local')->delete($jalurFoto)) {
            Log::error('Foto referensi gagal dihapus setelah persetujuan biometrik dicabut.');
            throw new RuntimeException('Persetujuan sudah dicabut, tetapi foto referensi gagal dihapus.');
        }
    }
}
