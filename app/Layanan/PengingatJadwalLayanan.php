<?php

namespace App\Layanan;

use App\BotWhatsapp\PengirimPesan;
use App\Models\Krs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// ==========================================
// PEMBAHASAN / BAGIAN: Antrean dan Pengiriman Pengingat Kuliah Terbatas
// ==========================================
class PengingatJadwalLayanan
{
    private const BATAS_PER_MENIT = 30;

    public function __construct(private readonly PengirimPesan $pengirim) {}

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menjadwalkan Pengingat untuk Kelas yang Akan Dimulai
    // ==========================================
    public function jalankan(): int
    {
        $kunci = Cache::lock('whatsapp:pengingat:worker', 900);
        if (! $kunci->get()) {
            return 0;
        }

        try {
            $this->antrikanKelasMendatang();
            $antrean = DB::table('ANTRIANPENGINGAT')
                ->where('STATUSPENGINGAT', 'MENUNGGU')
                ->where('WAKTUKIRIM', '<=', now())
                ->orderBy('WAKTUKIRIM')
                ->orderBy('IDANTRIANPENGINGAT')
                ->limit(self::BATAS_PER_MENIT)
                ->get();
            $jumlahDiproses = 0;

            foreach ($antrean as $baris) {
                $krs = Krs::query()
                    ->with(['jadwal', 'mahasiswa.pengguna'])
                    ->where('IDJADWAL', $baris->IDJADWAL)
                    ->where('IDMAHASISWA', $baris->IDMAHASISWA)
                    ->first();
                $nomor = $krs?->mahasiswa?->pengguna?->NOMORTELEPON;

                if ($nomor === null) {
                    DB::table('ANTRIANPENGINGAT')
                        ->where('IDANTRIANPENGINGAT', $baris->IDANTRIANPENGINGAT)
                        ->update([
                            'STATUSPENGINGAT' => 'GAGAL',
                            'CATATAN' => 'Nomor WhatsApp belum diikat.',
                            'TANGGALUBAH' => now(),
                        ]);

                    continue;
                }

                $pesan = sprintf(
                    'Pengingat: kuliah %s dimulai pukul %s di %s.',
                    $krs->jadwal->NAMAMATAKULIAH,
                    Carbon::parse($baris->WAKTUKULIAH)->format('H:i'),
                    $krs->jadwal->RUANGAN,
                );
                $this->pengirim->kirim($nomor, $pesan);
                DB::table('ANTRIANPENGINGAT')
                    ->where('IDANTRIANPENGINGAT', $baris->IDANTRIANPENGINGAT)
                    ->update([
                        'STATUSPENGINGAT' => 'TERKIRIM',
                        'TANGGALKIRIM' => now(),
                        'CATATAN' => null,
                        'TANGGALUBAH' => now(),
                    ]);
                $jumlahDiproses++;
            }

            return $jumlahDiproses;
        } finally {
            $kunci->release();
        }
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengantre Satu Pengingat per Mahasiswa dan Waktu Kuliah
    // ==========================================
    private function antrikanKelasMendatang(): void
    {
        $hariIni = [
            1 => 'SENIN',
            2 => 'SELASA',
            3 => 'RABU',
            4 => 'KAMIS',
            5 => 'JUMAT',
            6 => 'SABTU',
            7 => 'MINGGU',
        ][now()->dayOfWeekIso];

        $jadwal = DB::table('JADWAL')
            ->where('HARI', $hariIni)
            ->where('MENITPENGINGAT', '>', 0)
            ->get();

        foreach ($jadwal as $kelas) {
            $waktuKuliah = Carbon::parse(now()->toDateString().' '.$kelas->JAMMULAI);
            $waktuKirim = $waktuKuliah->copy()->subMinutes((int) $kelas->MENITPENGINGAT);
            if ($waktuKirim->gt(now()) || $waktuKuliah->lte(now())) {
                continue;
            }

            $pendaftar = Krs::query()
                ->where('IDJADWAL', $kelas->IDJADWAL)
                ->get(['IDMAHASISWA']);
            $baris = $pendaftar->map(fn (Krs $krs): array => [
                'IDJADWAL' => $kelas->IDJADWAL,
                'IDMAHASISWA' => $krs->IDMAHASISWA,
                'WAKTUKULIAH' => $waktuKuliah->toDateTimeString(),
                'WAKTUKIRIM' => $waktuKirim->toDateTimeString(),
                'STATUSPENGINGAT' => 'MENUNGGU',
                'TANGGALBUAT' => now(),
                'TANGGALUBAH' => now(),
            ])->all();

            if ($baris !== []) {
                DB::table('ANTRIANPENGINGAT')->insertOrIgnore($baris);
            }
        }
    }
}
