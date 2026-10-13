<?php

namespace App\Layanan;

use App\Models\Administrasi;
use App\Models\Pengguna;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

// ==========================================
// PEMBAHASAN / BAGIAN: Layanan Penyimpanan Pengajuan Surat
// ==========================================
class AdministrasiSuratLayanan
{
    /**
     * @param  array{JENISSURAT: string, PERIHAL: string, KEPERLUAN: string, DATAFORMULIR: array<string, mixed>, FILELAMPIRAN?: UploadedFile|null}  $data
     */
    public function ajukan(Pengguna $pemohon, array $data): Administrasi
    {
        $lampiran = $data['FILELAMPIRAN'] ?? null;
        $pathLampiran = null;

        if ($lampiran instanceof UploadedFile) {
            $pathLampiran = $lampiran->store('lampiran/surat', 'local');

            if ($pathLampiran === false) {
                throw new RuntimeException('Lampiran surat gagal disimpan.');
            }
        }

        try {
            return DB::transaction(fn (): Administrasi => Administrasi::query()->create([
                'IDPEMOHON' => $pemohon->IDPENGGUNA,
                'JENISSURAT' => $data['JENISSURAT'],
                'PERIHAL' => $data['PERIHAL'],
                'KEPERLUAN' => $data['KEPERLUAN'],
                'DATAFORMULIR' => array_filter(
                    $data['DATAFORMULIR'],
                    fn (mixed $nilai): bool => $nilai !== null && $nilai !== '',
                ),
                'FILELAMPIRAN' => $pathLampiran,
                'STATUSADMINISTRASI' => 'DIAJUKAN',
            ]));
        } catch (Throwable $exception) {
            if ($pathLampiran !== null) {
                Storage::disk('local')->delete($pathLampiran);
            }

            throw $exception;
        }
    }
}
