<?php

use App\Http\Controllers\Autentikasi\AutentikasiController;
use App\Http\Controllers\Baak\DasborController as BaakDasborController;
use App\Http\Controllers\Dosen\DasborController as DosenDasborController;
use App\Http\Controllers\Mahasiswa\DasborController as MahasiswaDasborController;
use App\Http\Controllers\Mahasiswa\OrmawaDasborController;
use App\Http\Controllers\Pimpinan\DasborController as PimpinanDasborController;
use Illuminate\Support\Facades\Route;

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Publik & Pengalihan Awal
// ==========================================
Route::get('/', function () {
    return redirect()->route('login');
});

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
});

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Dasbor Peran DOSEN
// ==========================================
Route::middleware(['auth', 'peran:DOSEN'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dasbor', [DosenDasborController::class, 'indeks'])->name('dasbor');
});

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Dasbor Peran PIMPINAN
// ==========================================
Route::middleware(['auth', 'peran:PIMPINAN'])->prefix('pimpinan')->name('pimpinan.')->group(function () {
    Route::get('/dasbor', [PimpinanDasborController::class, 'indeks'])->name('dasbor');
});

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Dasbor Peran BAAK
// ==========================================
Route::middleware(['auth', 'peran:BAAK'])->prefix('baak')->name('baak.')->group(function () {
    Route::get('/dasbor', [BaakDasborController::class, 'indeks'])->name('dasbor');
});

// ==========================================
// PEMBAHASAN / BAGIAN: Rute Dasbor Peran ORMAWA
// ==========================================
Route::middleware(['auth', 'peran:ORMAWA'])->prefix('ormawa')->name('ormawa.')->group(function () {
    Route::get('/dasbor', [OrmawaDasborController::class, 'indeks'])->name('dasbor');
});
