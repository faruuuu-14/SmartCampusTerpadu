<?php

namespace App\BotWhatsapp;

use App\Models\Administrasi;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Pengguna;
use App\Models\Pengumpulan;
use App\Models\Pertemuan;
use App\Models\Tugas;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

// ==========================================
// PEMBAHASAN / BAGIAN: Layanan Alur Layanan WhatsApp Multi-Peran
// ==========================================
class LayananBotWhatsapp
{
    private const TTL_ALUR = 600;

    public function __construct(
        private readonly MesinFsm $mesin,
        private readonly KlienWhatsAppCloudApi $klien,
    ) {}

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengikat Nomor dan Mengarahkan Perintah Berdasarkan Peran
    // ==========================================
    /**
     * @return array{pesan: string, tombol?: array<int, array{id: string, title: string}>}
     */
    public function tangani(string $nomor, string $pesan, ?string $idMedia = null): array
    {
        $pesan = trim($pesan);
        if (preg_match('/\ABIND\s+([A-Z0-9]{8})\z/i', $pesan, $cocok) === 1) {
            return ['pesan' => $this->ikat($nomor, Str::upper($cocok[1]))];
        }

        $pengguna = Pengguna::query()
            ->where('NOMORTELEPON', $nomor)
            ->where('STATUSAKUN', 'AKTIF')
            ->first();

        if ($pengguna === null) {
            return ['pesan' => $this->mesin->tangani($nomor, $pesan)];
        }

        $aliran = $this->kunciAliran($nomor);
        $langkah = Cache::get($aliran);
        $perintah = Str::lower(Str::squish($pesan));

        if ($perintah === 'batal' || $perintah === 'menu') {
            Cache::forget($aliran);

            return ['pesan' => $this->menu($pengguna)];
        }

        if (is_array($langkah)) {
            return $this->lanjutkanAliran($pengguna, $nomor, $pesan, $idMedia, $langkah);
        }

        if (Str::startsWith($perintah, 'jadwal')) {
            return ['pesan' => $this->jadwalMahasiswa($pengguna)];
        }
        if (Str::startsWith($perintah, 'absen')) {
            return ['pesan' => $this->tautanAbsen($pengguna)];
        }
        if (Str::startsWith($perintah, 'ajukan surat')) {
            Cache::put($aliran, ['langkah' => 'JENIS_SURAT'], now()->addSeconds(self::TTL_ALUR));

            return ['pesan' => 'Sebutkan jenis surat: SURATAKTIF, TRANSKRIP, MAGANG, DISPENSASI, atau IZINSAKIT.'];
        }
        if (Str::startsWith($perintah, 'status surat')) {
            return ['pesan' => $this->statusSurat($pengguna)];
        }
        if (Str::startsWith($perintah, 'batalkan kelas')) {
            return ['pesan' => $this->mulaiPembatalan($pengguna, $aliran)];
        }
        if (Str::startsWith($perintah, 'tugas kilat')) {
            Cache::put($aliran, ['langkah' => 'TUGAS_JADWAL'], now()->addSeconds(self::TTL_ALUR));

            return ['pesan' => 'Kirim ID jadwal kuliah yang akan diberi tugas kilat.'];
        }
        if (Str::startsWith($perintah, 'tugas')) {
            return $this->daftarTugas($pengguna, $nomor);
        }

        return ['pesan' => $this->menu($pengguna)];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menukarkan Kode Sekali Pakai dengan Ikatan Nomor Pengguna
    // ==========================================
    private function ikat(string $nomor, string $kode): string
    {
        $idPengguna = Cache::pull('whatsapp:binding:'.hash('sha256', $kode));
        if (! is_numeric($idPengguna)) {
            return 'Kode pengikatan tidak valid atau sudah kedaluwarsa. Buat kode baru setelah login.';
        }

        $pengguna = Pengguna::query()->find($idPengguna);
        if ($pengguna === null || Pengguna::query()
            ->where('NOMORTELEPON', $nomor)
            ->where('IDPENGGUNA', '!=', $pengguna->IDPENGGUNA)
            ->exists()) {
            return 'Nomor WhatsApp sudah terikat ke akun lain atau akun tidak tersedia.';
        }

        $pengguna->forceFill([
            'NOMORTELEPON' => $nomor,
            'TANGGALBINDING' => now(),
        ])->save();

        return 'Akun berhasil diikat ke WhatsApp. Ketik MENU untuk melihat layanan yang tersedia.';
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Menu Berdasarkan Peran Pengguna
    // ==========================================
    private function menu(Pengguna $pengguna): string
    {
        $peran = $pengguna->daftarPeran();
        $menu = ['Layanan Smart Campus Terpadu:'];
        if (in_array('MAHASISWA', $peran, true)) {
            $menu[] = 'jadwal - jadwal kuliah hari ini';
            $menu[] = 'absen - tautan presensi yang sedang dibuka';
            $menu[] = 'ajukan surat - pengajuan surat bertahap';
            $menu[] = 'tugas - daftar dan kumpulkan tugas';
            $menu[] = 'status surat - status pengajuan';
        }
        if (in_array('DOSEN', $peran, true)) {
            $menu[] = 'batalkan kelas - pembatalan dengan konfirmasi';
            $menu[] = 'tugas kilat - siarkan tugas pada mahasiswa';
        }

        return implode("\n", $menu)."\n\nKetik BATAL untuk menghentikan alur.";
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Jadwal Mahasiswa Hari Ini
    // ==========================================
    private function jadwalMahasiswa(Pengguna $pengguna): string
    {
        $mahasiswa = $pengguna->mahasiswa;
        if (! $mahasiswa instanceof Mahasiswa) {
            return 'Perintah jadwal hanya tersedia untuk akun mahasiswa.';
        }

        $hari = [1 => 'SENIN', 2 => 'SELASA', 3 => 'RABU', 4 => 'KAMIS', 5 => 'JUMAT', 6 => 'SABTU', 7 => 'MINGGU'][now()->dayOfWeekIso];
        $jadwal = Krs::query()
            ->with('jadwal')
            ->where('IDMAHASISWA', $mahasiswa->IDMAHASISWA)
            ->whereHas('jadwal', fn ($query) => $query->where('HARI', $hari))
            ->get()
            ->map(fn (Krs $krs): string => sprintf(
                '%s %s-%s, %s (%s)',
                $krs->jadwal->NAMAMATAKULIAH,
                $krs->jadwal->JAMMULAI,
                $krs->jadwal->JAMSELESAI,
                $krs->jadwal->RUANGAN,
                $krs->jadwal->KELAS,
            ));

        return $jadwal->isEmpty() ? 'Tidak ada jadwal kuliah untuk hari ini.' : "Jadwal kuliah hari ini:\n".implode("\n", $jadwal->all());
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membuat Tautan Presensi Bertanda Tangan
    // ==========================================
    private function tautanAbsen(Pengguna $pengguna): string
    {
        $mahasiswa = $pengguna->mahasiswa;
        if (! $mahasiswa instanceof Mahasiswa) {
            return 'Perintah absen hanya tersedia untuk akun mahasiswa.';
        }

        $pertemuan = Pertemuan::query()
            ->with('jadwal')
            ->where('STATUSPERTEMUAN', 'BERLANGSUNG')
            ->whereNotNull('BUKAPRESENSI')
            ->where('TUTUPPRESENSI', '>', now())
            ->whereHas('jadwal.krs', fn ($query) => $query->where('IDMAHASISWA', $mahasiswa->IDMAHASISWA))
            ->get()
            ->filter(fn (Pertemuan $item): bool => ! $item->presensi()
                ->where('IDMAHASISWA', $mahasiswa->IDMAHASISWA)
                ->exists());

        if ($pertemuan->isEmpty()) {
            return 'Tidak ada sesi presensi terbuka untuk jadwal Anda.';
        }

        $tautan = $pertemuan->map(fn (Pertemuan $item): string => $item->jadwal->NAMAMATAKULIAH.': '.URL::temporarySignedRoute(
            'mahasiswa.presensi.tampilkan',
            now()->addMinutes(5),
            ['mahasiswa' => $mahasiswa, 'pertemuan' => $item],
        ));

        return "Tautan presensi berlaku 5 menit:\n".implode("\n", $tautan->all());
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Status Surat dan Tahap Persetujuan
    // ==========================================
    private function statusSurat(Pengguna $pengguna): string
    {
        $surat = $pengguna->administrasi()
            ->with(['persetujuanSurat.penyetuju'])
            ->orderByDesc('TANGGALBUAT')
            ->limit(5)
            ->get();

        if ($surat->isEmpty()) {
            return 'Belum ada pengajuan surat.';
        }

        return $surat->map(function (Administrasi $item): string {
            $tahap = $item->persetujuanSurat
                ->sortBy('URUTAN')
                ->map(fn ($persetujuan): string => ($persetujuan->penyetuju?->NAMALENGKAP ?? 'Penyetuju')
                    .'='.$persetujuan->STATUSPERSETUJUAN)
                ->implode(', ');

            return $item->JENISSURAT.' - '.$item->STATUSADMINISTRASI
                .($item->NOMORSURAT ? ' ('.$item->NOMORSURAT.')' : '')
                .($tahap !== '' ? "\n  Tahap: ".$tahap : '');
        })->implode("\n");
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Tugas dan Tombol Sudah atau Belum
    // ==========================================
    /**
     * @return array{pesan: string, tombol?: array<int, array{id: string, title: string}>}
     */
    private function daftarTugas(Pengguna $pengguna, string $nomor): array
    {
        $mahasiswa = $pengguna->mahasiswa;
        if (! $mahasiswa instanceof Mahasiswa) {
            return ['pesan' => 'Perintah tugas hanya tersedia untuk akun mahasiswa.'];
        }

        $jadwalIds = $mahasiswa->krs()->pluck('IDJADWAL');
        $tugas = Tugas::query()
            ->with('jadwal')
            ->whereIn('IDJADWAL', $jadwalIds)
            ->where('TANGGALTENGGAT', '>=', now())
            ->orderBy('TANGGALTENGGAT')
            ->limit(10)
            ->get();

        if ($tugas->isEmpty()) {
            return ['pesan' => 'Tidak ada tugas yang belum jatuh tempo.'];
        }

        $pesan = "Tugas aktif:\n".$tugas->map(fn (Tugas $item): string => sprintf(
            '#%s %s (tenggat %s)',
            $item->IDTUGAS,
            $item->JUDULTUGAS,
            $item->TANGGALTENGGAT->format('d/m H:i'),
        ))->implode("\n")."\n\nPilih status tugas pertama:";
        $tugasPertama = $tugas->first();
        Cache::put($this->kunciAliran($nomor), ['langkah' => 'STATUS_TUGAS'], now()->addSeconds(self::TTL_ALUR));

        return [
            'pesan' => $pesan,
            'tombol' => [
                ['id' => 'SUDAH:'.$tugasPertama->IDTUGAS, 'title' => 'Sudah'],
                ['id' => 'BELUM:'.$tugasPertama->IDTUGAS, 'title' => 'Belum'],
            ],
        ];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengelola Percakapan Bertahap dan Status Tugas
    // ==========================================
    /**
     * @param  array<string, string|int>  $langkah
     * @return array{pesan: string, tombol?: array<int, array{id: string, title: string}>}
     */
    private function lanjutkanAliran(Pengguna $pengguna, string $nomor, string $pesan, ?string $idMedia, array $langkah): array
    {
        $kunci = $this->kunciAliran($nomor);
        $langkahAktif = $langkah['langkah'] ?? '';

        if (in_array($langkahAktif, ['JENIS_SURAT', 'PERIHAL_SURAT', 'KEPERLUAN_SURAT'], true)) {
            return $this->aliranSurat($pengguna, $kunci, $pesan, $langkah);
        }
        if ($langkahAktif === 'UNGGAH_TUGAS') {
            return $this->simpanBerkasTugas($pengguna, $kunci, $idMedia, $langkah);
        }
        if ($langkahAktif === 'PEMBATALAN_PILIH') {
            return $this->pilihPembatalan($pengguna, $kunci, $pesan);
        }
        if ($langkahAktif === 'PEMBATALAN_KONFIRMASI') {
            return $this->konfirmasiPembatalan($pengguna, $kunci, $pesan, $langkah);
        }
        if (Str::startsWith($langkahAktif, 'TUGAS_')) {
            return $this->aturTugasKilat($pengguna, $kunci, $pesan, $langkah);
        }

        return $this->aksiStatusTugas($pengguna, $kunci, $pesan);
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengumpulkan Data Surat Secara Bertahap
    // ==========================================
    /**
     * @param  array<string, string|int>  $langkah
     * @return array{pesan: string}
     */
    private function aliranSurat(Pengguna $pengguna, string $kunci, string $pesan, array $langkah): array
    {
        $jenisDiizinkan = ['SURATAKTIF', 'TRANSKRIP', 'MAGANG', 'DISPENSASI', 'IZINSAKIT'];
        if (($langkah['langkah'] ?? '') === 'JENIS_SURAT') {
            $jenis = Str::upper(trim($pesan));
            if (! in_array($jenis, $jenisDiizinkan, true)) {
                return ['pesan' => 'Jenis tidak tersedia. Pilih SURATAKTIF, TRANSKRIP, MAGANG, DISPENSASI, atau IZINSAKIT.'];
            }

            Cache::put($kunci, ['langkah' => 'PERIHAL_SURAT', 'jenis' => $jenis], now()->addSeconds(self::TTL_ALUR));

            return ['pesan' => 'Tuliskan perihal singkat surat yang diajukan.'];
        }

        if (($langkah['langkah'] ?? '') === 'PERIHAL_SURAT') {
            Cache::put($kunci, ['langkah' => 'KEPERLUAN_SURAT', 'jenis' => $langkah['jenis'], 'perihal' => Str::limit(trim($pesan), 255, '')], now()->addSeconds(self::TTL_ALUR));

            return ['pesan' => 'Jelaskan keperluan surat secara singkat.'];
        }

        Administrasi::query()->create([
            'IDPEMOHON' => $pengguna->IDPENGGUNA,
            'JENISSURAT' => $langkah['jenis'],
            'PERIHAL' => $langkah['perihal'],
            'KEPERLUAN' => Str::limit(trim($pesan), 3000, ''),
            'DATAFORMULIR' => ['SUMBER' => 'WHATSAPP'],
            'STATUSADMINISTRASI' => 'DIAJUKAN',
        ]);
        Cache::forget($kunci);

        return ['pesan' => 'Pengajuan surat berhasil diterima dengan status DIAJUKAN.'];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memulai Alur Pengumpulan Tugas Mahasiswa
    // ==========================================
    private function aksiStatusTugas(Pengguna $pengguna, string $kunci, string $pesan): array
    {
        if (! preg_match('/\A(SUDAH|BELUM)[:\s]+(\d+)\z/i', trim($pesan), $cocok)) {
            Cache::forget($kunci);

            return ['pesan' => 'Gunakan MENU untuk melihat layanan atau pilih Sudah/Belum pada tugas yang tampil.'];
        }

        $mahasiswa = $pengguna->mahasiswa;
        $tugas = Tugas::query()
            ->whereKey((int) $cocok[2])
            ->whereIn('IDJADWAL', $mahasiswa?->krs()->pluck('IDJADWAL') ?? [])
            ->first();
        if (! $tugas instanceof Tugas) {
            Cache::forget($kunci);

            return ['pesan' => 'Tugas tidak ditemukan pada jadwal Anda.'];
        }

        if (Str::upper($cocok[1]) === 'BELUM') {
            Pengumpulan::query()->updateOrCreate(
                ['IDTUGAS' => $tugas->IDTUGAS, 'IDMAHASISWA' => $mahasiswa->IDMAHASISWA],
                ['STATUSPENGUMPULAN' => 'BELUM'],
            );
            Cache::forget($kunci);

            return ['pesan' => 'Status tugas dicatat BELUM.'];
        }

        Cache::put($kunci, ['langkah' => 'UNGGAH_TUGAS', 'id_tugas' => $tugas->IDTUGAS], now()->addSeconds(self::TTL_ALUR));

        return ['pesan' => 'Kirim dokumen tugas sebagai berkas PDF atau Word pada percakapan ini.'];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Mengunduh dan Menyimpan Berkas PENGUMPULAN
    // ==========================================
    /**
     * @param  array<string, string|int>  $langkah
     * @return array{pesan: string}
     */
    private function simpanBerkasTugas(Pengguna $pengguna, string $kunci, ?string $idMedia, array $langkah): array
    {
        $mahasiswa = $pengguna->mahasiswa;
        if ($idMedia === null || ! $mahasiswa instanceof Mahasiswa) {
            return ['pesan' => 'Berkas tugas belum diterima. Kirim lampiran PDF atau Word, bukan teks.'];
        }

        $berkas = $this->klien->unduhMedia($idMedia);
        $ekstensi = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ][$berkas['tipe']] ?? null;

        if ($ekstensi === null || strlen($berkas['isi']) > 10 * 1024 * 1024) {
            return ['pesan' => 'Berkas harus berupa PDF/Word dan berukuran paling banyak 10 MB.'];
        }

        $jalur = 'pengumpulan/whatsapp/'.Str::uuid().'.'.$ekstensi;
        Storage::disk('local')->put($jalur, $berkas['isi']);
        $waktuKumpul = now();
        Pengumpulan::query()->updateOrCreate(
            ['IDTUGAS' => $langkah['id_tugas'], 'IDMAHASISWA' => $mahasiswa->IDMAHASISWA],
            [
                'STATUSPENGUMPULAN' => 'SUDAH',
                'FILEKUMPUL' => $jalur,
                'HASHFILE' => hash('sha256', $berkas['isi']),
                'WAKTUKUMPUL' => $waktuKumpul,
            ],
        );
        Cache::forget($kunci);

        return ['pesan' => 'Berkas tugas tersimpan dengan status SUDAH.'];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Menampilkan Sesi Berlangsung Milik Dosen
    // ==========================================
    private function mulaiPembatalan(Pengguna $pengguna, string $kunci): string
    {
        $dosen = $pengguna->dosen;
        if (! $dosen instanceof Dosen) {
            return 'Pembatalan kelas hanya tersedia untuk akun dosen.';
        }

        $pertemuan = Pertemuan::query()
            ->with('jadwal')
            ->whereHas('jadwal', fn ($query) => $query->where('IDDOSEN', $dosen->IDDOSEN))
            ->where('STATUSPERTEMUAN', 'BERLANGSUNG')
            ->get();
        if ($pertemuan->isEmpty()) {
            return 'Tidak ada pertemuan berlangsung yang dapat dibatalkan.';
        }

        Cache::put($kunci, ['langkah' => 'PEMBATALAN_PILIH'], now()->addSeconds(self::TTL_ALUR));

        return "Pilih ID pertemuan yang akan dibatalkan:\n".$pertemuan
            ->map(fn (Pertemuan $item): string => '#'.$item->IDPERTEMUAN.' '.$item->jadwal->NAMAMATAKULIAH)
            ->implode("\n");
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Memilih dan Meminta Konfirmasi Pembatalan
    // ==========================================
    private function pilihPembatalan(Pengguna $pengguna, string $kunci, string $pesan): array
    {
        $dosen = $pengguna->dosen;
        $pertemuan = ctype_digit(trim($pesan))
            ? Pertemuan::query()->whereKey((int) $pesan)->where('STATUSPERTEMUAN', 'BERLANGSUNG')
                ->whereHas('jadwal', fn ($query) => $query->where('IDDOSEN', $dosen?->IDDOSEN))->first()
            : null;
        if (! $pertemuan instanceof Pertemuan) {
            return ['pesan' => 'ID pertemuan tidak valid atau bukan kelas Anda. Kirim BATAL untuk menghentikan.'];
        }

        Cache::put($kunci, ['langkah' => 'PEMBATALAN_KONFIRMASI', 'id_pertemuan' => $pertemuan->IDPERTEMUAN], now()->addSeconds(self::TTL_ALUR));

        return ['pesan' => 'Konfirmasi pembatalan kelas #'.$pertemuan->IDPERTEMUAN.'? Balas YA atau TIDAK.'];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membatalkan Pertemuan dan Memberi Tahu Mahasiswa KRS
    // ==========================================
    /**
     * @param  array<string, string|int>  $langkah
     */
    private function konfirmasiPembatalan(Pengguna $pengguna, string $kunci, string $pesan, array $langkah): array
    {
        if (Str::upper(trim($pesan)) !== 'YA') {
            Cache::forget($kunci);

            return ['pesan' => 'Pembatalan kelas tidak dilanjutkan.'];
        }

        $dosen = $pengguna->dosen;
        $pertemuan = Pertemuan::query()
            ->with('jadwal.krs.mahasiswa.pengguna')
            ->whereKey($langkah['id_pertemuan'])
            ->where('STATUSPERTEMUAN', 'BERLANGSUNG')
            ->whereHas('jadwal', fn ($query) => $query->where('IDDOSEN', $dosen?->IDDOSEN))
            ->first();

        if (! $pertemuan instanceof Pertemuan) {
            Cache::forget($kunci);

            return ['pesan' => 'Pertemuan tidak lagi tersedia untuk dibatalkan.'];
        }

        $pertemuan->forceFill([
            'STATUSPERTEMUAN' => 'DIBATALKAN',
            'DIBATALKANVIA' => 'BOT',
            'ALASANBATAL' => 'Dibatalkan melalui Bot WhatsApp oleh '.$pengguna->NAMALENGKAP,
        ])->save();
        foreach ($pertemuan->jadwal->krs as $krs) {
            $nomorMahasiswa = $krs->mahasiswa?->pengguna?->NOMORTELEPON;
            if ($nomorMahasiswa !== null) {
                app(PengirimPesan::class)->kirim($nomorMahasiswa, 'Kelas '.$pertemuan->jadwal->NAMAMATAKULIAH.' dibatalkan oleh dosen.');
            }
        }
        Cache::forget($kunci);

        return ['pesan' => 'Pertemuan berhasil dibatalkan dan mahasiswa terdaftar sudah diberi tahu.'];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Alur Pembuatan Tugas Kilat dan Siaran ke Kelas
    // ==========================================
    /**
     * @param  array<string, string|int>  $langkah
     * @return array{pesan: string}
     */
    private function aturTugasKilat(Pengguna $pengguna, string $kunci, string $pesan, array $langkah): array
    {
        $dosen = $pengguna->dosen;
        if (! $dosen instanceof Dosen) {
            Cache::forget($kunci);

            return ['pesan' => 'Pembuatan tugas kilat hanya tersedia untuk akun dosen.'];
        }

        $tahap = $langkah['langkah'] ?? '';
        if ($tahap === 'TUGAS_JADWAL') {
            $jadwal = ctype_digit(trim($pesan))
                ? $dosen->jadwal()->whereKey((int) $pesan)->first()
                : null;
            if (! $jadwal instanceof Jadwal) {
                return ['pesan' => 'ID jadwal tidak valid atau bukan jadwal Anda.'];
            }

            Cache::put($kunci, ['langkah' => 'TUGAS_JUDUL', 'id_jadwal' => $jadwal->IDJADWAL], now()->addSeconds(self::TTL_ALUR));

            return ['pesan' => 'Tuliskan judul tugas kilat.'];
        }
        if ($tahap === 'TUGAS_JUDUL') {
            Cache::put($kunci, ['langkah' => 'TUGAS_TENGGAT', 'id_jadwal' => $langkah['id_jadwal'], 'judul' => Str::limit(trim($pesan), 255, '')], now()->addSeconds(self::TTL_ALUR));

            return ['pesan' => 'Kirim tenggat dengan format YYYY-MM-DD HH:MM.'];
        }

        $tenggat = null;
        try {
            $tanggal = Carbon::createFromFormat('Y-m-d H:i', trim($pesan));
            if ($tanggal instanceof Carbon) {
                $tenggat = $tanggal;
            }
        } catch (\InvalidArgumentException|\ValueError) {
            $tenggat = null;
        }
        if ($tenggat === null || $tenggat->isPast()) {
            return ['pesan' => 'Format tenggat tidak valid atau sudah lewat. Gunakan YYYY-MM-DD HH:MM.'];
        }

        $jadwal = $dosen->jadwal()->whereKey($langkah['id_jadwal'])->firstOrFail();
        $tugas = Tugas::query()->create([
            'IDJADWAL' => $jadwal->IDJADWAL,
            'IDDOSEN' => $dosen->IDDOSEN,
            'JUDULTUGAS' => $langkah['judul'],
            'DESKRIPSI' => 'Tugas kilat yang disiarkan melalui Bot WhatsApp.',
            'TANGGALTENGGAT' => $tenggat,
            'MODEPENGERJAAN' => 'BEBAS',
            'DIBUATVIA' => 'BOT',
            'TANGGALSIARAN' => now(),
        ]);
        foreach ($jadwal->krs()->with('mahasiswa.pengguna')->get() as $krs) {
            $nomorMahasiswa = $krs->mahasiswa?->pengguna?->NOMORTELEPON;
            if ($nomorMahasiswa !== null) {
                app(PengirimPesan::class)->kirim($nomorMahasiswa, 'Tugas baru: '.$tugas->JUDULTUGAS.'; tenggat '.$tenggat->format('d/m/Y H:i').'.');
            }
        }
        Cache::forget($kunci);

        return ['pesan' => 'Tugas kilat tersimpan dan disiarkan kepada mahasiswa kelas.'];
    }

    // ==========================================
    // PEMBAHASAN / BAGIAN: Membentuk Kunci Cache Dialog per Nomor WhatsApp
    // ==========================================
    private function kunciAliran(string $nomor): string
    {
        return 'whatsapp:flow:'.hash('sha256', $nomor);
    }
}
