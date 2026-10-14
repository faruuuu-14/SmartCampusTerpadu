<?php

use App\Http\Controllers\Autentikasi\AutentikasiController;
use App\Http\Controllers\Baak\DasborController as BaakDasborController;
use App\Http\Controllers\Baak\JadwalController as BaakJadwalController;
use App\Http\Controllers\Baak\KrsController as BaakKrsController;
use App\Http\Controllers\Baak\PenggunaController as BaakPenggunaController;
use App\Http\Controllers\Baak\SuratController as BaakSuratController;
use App\Http\Controllers\Baak\VerifikasiWajahController;
use App\Http\Controllers\BotWhatsapp\BotWhatsappController;
use App\Http\Controllers\BotWhatsapp\IkatAkunController;
use App\Http\Controllers\Dosen\DasborController as DosenDasborController;
use App\Http\Controllers\Dosen\PertemuanController;
use App\Http\Controllers\Dosen\RekapPresensiController;
use App\Http\Controllers\Dosen\TugasController;
use App\Http\Controllers\Mahasiswa\DasborController as MahasiswaDasborController;
use App\Http\Controllers\Mahasiswa\OrmawaDasborController;
use App\Http\Controllers\Mahasiswa\PendaftaranWajahController;
use App\Http\Controllers\Mahasiswa\PengajuanSuratController;
use App\Http\Controllers\Mahasiswa\PresensiController;
use App\Http\Controllers\Mahasiswa\TugasController as MahasiswaTugasController;
use App\Http\Controllers\Pimpinan\DasborController as PimpinanDasborController;
use App\Http\Controllers\Pimpinan\PersetujuanSuratController;
use App\Http\Controllers\Publik\VerifikasiController;
use App\Http\Controllers\VerifikasiTteController;
use Illuminate\Support\Facades\Route;

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Publik & Pengalihan Awal
// ==========================================
Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================
// PEMBAHASAN / BAGIAN: Webhook Verifikasi dan Pesan WhatsApp
// ==========================================
Route::get('/webhook/whatsapp', [BotWhatsappController::class, 'verifikasi'])->name('whatsapp.verifikasi');
Route::post('/webhook/whatsapp', [BotWhatsappController::class, 'terima'])->name('whatsapp.terima');

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Pengikatan Akun WhatsApp
// ==========================================
Route::middleware('auth')->get('/whatsapp/ikat', [IkatAkunController::class, 'tampilkan'])->name('whatsapp.ikat');

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Autentikasi Satu Pintu
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AutentikasiController::class, 'tampilkanFormLogin'])->name('login');
    Route::post('/login', [AutentikasiController::class, 'prosesLogin'])->name('login.proses');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AutentikasiController::class, 'logout'])->name('logout');

    // Rute Pemilihan Peran Aktif untuk Akun Multi-Role
    Route::get('/pilih-peran', [AutentikasiController::class, 'tampilkanPilihPeran'])->name('autentikasi.pilihperan');
    Route::post('/pilih-peran', [AutentikasiController::class, 'simpanPilihPeran'])->name('autentikasi.pilihperan.simpan');
});

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Dasbor Peran MAHASISWA
// ==========================================
Route::middleware(['auth', 'peran:MAHASISWA'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dasbor', [MahasiswaDasborController::class, 'indeks'])->name('dasbor');
    Route::get('/wajah', [PendaftaranWajahController::class, 'indeks'])->name('wajah.indeks');
    Route::post('/wajah', [PendaftaranWajahController::class, 'daftarkan'])->name('wajah.daftar');
    Route::post('/wajah/cabut', [PendaftaranWajahController::class, 'cabut'])->name('wajah.cabut');
    Route::get('/pengajuan-surat', [PengajuanSuratController::class, 'indeks'])->name('surat.indeks');
    Route::post('/pengajuan-surat', [PengajuanSuratController::class, 'simpan'])->name('surat.simpan');
    Route::get('/tugas', [MahasiswaTugasController::class, 'indeks'])->name('tugas.indeks');
    Route::post('/tugas/{tugas}/pengumpulan', [MahasiswaTugasController::class, 'simpan'])->name('tugas.simpan');
    Route::delete('/tugas/{tugas}/pengumpulan', [MahasiswaTugasController::class, 'hapus'])->name('tugas.hapus');
});

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Presensi Publik Bertanda Tangan
// ==========================================
Route::get('/presensi/{mahasiswa}/{pertemuan}', [PresensiController::class, 'tampilkan'])
    ->middleware('signed')
    ->name('mahasiswa.presensi.tampilkan');
Route::post('/presensi/{mahasiswa}/{pertemuan}', [PresensiController::class, 'simpan'])
    ->middleware('signed')
    ->name('mahasiswa.presensi.simpan');

// ==========================================
// PEMBAHASAN / BAGIAN: Verifikasi Publik Tanda Tangan Elektronik
// ==========================================
Route::get('/verifikasi/{token}', [VerifikasiController::class, 'tampilkan'])
    ->middleware('throttle:30,1')
    ->name('tte.verifikasi');
Route::post('/verifikasi/{token}/bandingkan', [VerifikasiController::class, 'bandingkan'])
    ->middleware('throttle:10,1')
    ->name('tte.verifikasi.bandingkan');
Route::get('/verifikasi/{token}/berkas', [VerifikasiTteController::class, 'unduh'])->name('tte.unduh');

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Dasbor Peran DOSEN
// ==========================================
Route::middleware(['auth', 'peran:DOSEN'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dasbor', [DosenDasborController::class, 'indeks'])->name('dasbor');
    Route::get('/pertemuan', [PertemuanController::class, 'indeks'])->name('pertemuan.indeks');
    Route::post('/pertemuan/{jadwal}/buka', [PertemuanController::class, 'buka'])->name('pertemuan.buka');
    Route::post('/pertemuan/{pertemuan}/tutup', [PertemuanController::class, 'tutup'])->name('pertemuan.tutup');
    Route::post('/presensi/{presensi}/tinjau', [PertemuanController::class, 'tinjau'])->name('presensi.tinjau');
    Route::get('/rekap-presensi', [RekapPresensiController::class, 'indeks'])->name('rekap.indeks');
    Route::get('/presensi/{presensi}/foto', [RekapPresensiController::class, 'foto'])->name('rekap.foto');
    Route::get('/tugas', [TugasController::class, 'indeks'])->name('tugas.indeks');
    Route::post('/tugas', [TugasController::class, 'simpan'])->name('tugas.simpan');
    Route::put('/tugas/{tugas}', [TugasController::class, 'ubah'])->name('tugas.ubah');
    Route::delete('/tugas/{tugas}', [TugasController::class, 'hapus'])->name('tugas.hapus');
    Route::get('/pengumpulan/{pengumpulan}/unduh', [TugasController::class, 'unduh'])->name('tugas.unduh');
    Route::post('/pengumpulan/{pengumpulan}/nilai', [TugasController::class, 'nilai'])->name('tugas.nilai');
});

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Dasbor Peran PIMPINAN
// ==========================================
Route::middleware(['auth', 'peran:PIMPINAN'])->prefix('pimpinan')->name('pimpinan.')->group(function () {
    Route::get('/dasbor', [PimpinanDasborController::class, 'indeks'])->name('dasbor');
    Route::get('/surat', [PersetujuanSuratController::class, 'indeks'])->name('surat.indeks');
    Route::post('/surat/{persetujuan}/setujui', [PersetujuanSuratController::class, 'setujui'])->name('surat.setujui');
    Route::post('/surat/{persetujuan}/tolak', [PersetujuanSuratController::class, 'tolak'])->name('surat.tolak');
});

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Dasbor Peran BAAK
// ==========================================
Route::middleware(['auth', 'peran:BAAK'])->prefix('baak')->name('baak.')->group(function () {
    Route::get('/dasbor', [BaakDasborController::class, 'indeks'])->name('dasbor');
    Route::resource('/pengguna', BaakPenggunaController::class)->except(['show']);
    Route::resource('/jadwal', BaakJadwalController::class)->except(['show']);
    Route::resource('/krs', BaakKrsController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['krs' => 'krs']);
    Route::get('/wajah', [VerifikasiWajahController::class, 'indeks'])->name('wajah.indeks');
    Route::get('/wajah/{mahasiswa}/foto', [VerifikasiWajahController::class, 'foto'])->name('wajah.foto');
    Route::post('/wajah/{mahasiswa}/verifikasi', [VerifikasiWajahController::class, 'simpan'])->name('wajah.verifikasi');
    Route::get('/surat', [BaakSuratController::class, 'indeks'])->name('surat.indeks');
    Route::get('/surat/{administrasi}/lampiran', [BaakSuratController::class, 'lampiran'])->name('surat.lampiran');
    Route::post('/surat/{administrasi}/tinjau', [BaakSuratController::class, 'tinjau'])->name('surat.tinjau');
});

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Dasbor Peran ORMAWA
// ==========================================
Route::middleware(['auth', 'peran:ORMAWA'])->prefix('ormawa')->name('ormawa.')->group(function () {
    Route::get('/dasbor', [OrmawaDasborController::class, 'indeks'])->name('dasbor');
});
