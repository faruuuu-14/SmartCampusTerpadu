# Smart Campus Terpadu

Proyek Skripsi: **Sistem Web Portal Multi-Role Terpadu & WhatsApp Bot (FSM) dengan Tanda Tangan Elektronik (TTE) Kriptografi Ed25519 dan Presensi Cerdas Anti-Titip Absen.**

---

## 🛠️ Lingkungan Pengembangan & Prasyarat
- **Sistem Operasi**: Windows 11 / Laragon
- **Framework**: Laravel 13.x (PHP 8.5)
- **Basis Data**: MySQL 8.0 (`SMARTCAMPUS`, `utf8mb4_unicode_ci`)
- **Ekstensi PHP Wajib**: `openssl`, `sodium`, `gd`, `mbstring`, `fileinfo`, `pdo_mysql`, `curl`, `intl`, `zip`

---

## 🔑 Kredensial Akun Demo (Sesi 1)
Semua akun demo menggunakan **satu kata sandi seragam**:
> **Kata Sandi Seragam**: `SmartCampus2026!`

| Peran | Username | Nama Lengkap | Keterangan Relasi Peran |
|---|---|---|---|
| **BAAK** | `BAAK001` | Staf Administrasi BAAK | `ADALAHBAAK = 1` |
| **DOSEN & PIMPINAN** | `DOSEN001` | Prof. Dr. Ir. Budi Santoso, M.T. | Terdaftar di `DOSEN` & memiliki `JABATAN` (Dekan) &rarr; **Peran Ganda** |
| **DOSEN** | `DOSEN002` | Dr. Ratna Sari, S.Kom., M.Kom. | Dosen Pengampu Biasa |
| **DOSEN** | `DOSEN003` | Ahmad Fauzi, S.T., M.T. | Dosen Pengampu Biasa |
| **MAHASISWA & ORMAWA** | `220101001` | Ahmad Maulana | Mahasiswa Informatika & `IDKETUA` BEM aktif &rarr; **Peran Ganda** |
| **MAHASISWA** | `220101002` | Siti Nurhaliza | Mahasiswa Informatika 2022 |
| **MAHASISWA** | `220101003` | Rian Pratama | Mahasiswa Informatika 2022 |
| **MAHASISWA** | `230201004` | Dewi Lestari | Mahasiswa Sistem Informasi 2023 |
| **MAHASISWA** | `230201005` | Fajar Nugraha | Mahasiswa Sistem Informasi 2023 |

---

## 🏛️ Arsitektur "Login Satu Pintu" & Multi-Role Dinamis
1. **Tanpa Kolom Peran Statis**: Tabel `PENGGUNA` sama sekali tidak memiliki kolom `ROLE`.
2. **Penurunan Relasi Otomatis (`daftarPeran()`)**:
   - Jika ada entri di tabel `MAHASISWA` &rarr; Berperan `MAHASISWA`.
   - Jika menjadi `IDKETUA` di tabel `ORGANISASI` (status `AKTIF`) &rarr; Otomatis berperan `ORMAWA`.
   - Jika ada entri di tabel `DOSEN` &rarr; Berperan `DOSEN`.
   - Jika pada entri `DOSEN` terisi kolom `JABATAN` &rarr; Otomatis berperan `PIMPINAN`.
   - Jika kolom `ADALAHBAAK = 1` &rarr; Berperan `BAAK`.
3. **Alur Masuk Akun**:
   - Jika akun hanya memiliki **1 peran**: Langsung diarahkan ke dasbor peran tersebut.
   - Jika akun memiliki **> 1 peran**: Diarahkan ke halaman `/pilih-peran` untuk menentukan konteks kerja aktif. Sesi server menyimpan `PERANAKTIF`.
   - Pengguna dengan peran ganda dapat berpindah konteks kapan saja melalui tombol **⇄ Ganti Peran** di navigasi atas.
4. **Keamanan Server-Side**:
   - Middleware `DeteksiPeran` (alias `peran:DOSEN,PIMPINAN`) memeriksa peran aktif langsung pada sesi server dan menolak akses ilegal dengan status **403 Forbidden**.

---

## 🗄️ Aturan Skema Basis Data `SMARTCAMPUS` (12 Tabel)
Semua nama tabel dan kolom menggunakan **HURUF KAPITAL TANPA SPASI DAN TANPA GARIS BAWAH**:
1. `PENGGUNA`
2. `DOSEN`
3. `MAHASISWA`
4. `ORGANISASI`
5. `JADWAL`
6. `KRS`
7. `PERTEMUAN`
8. `PRESENSI`
9. `TUGAS`
10. `PENGUMPULAN`
11. `ADMINISTRASI`
12. `PERSETUJUANSURAT`

Setiap entitas memiliki stempel waktu `TANGGALBUAT` dan `TANGGALUBAH`.

---

## 🚀 Perintah Dasar Pengujian
```bash
# Migrasi ulang dan isi data demo
php artisan migrate:fresh --seed

# Menjalankan test suite
php artisan test --compact

# Pemeriksaan format kode
vendor/bin/pint --format agent
```
