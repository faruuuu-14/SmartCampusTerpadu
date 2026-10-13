# Smart Campus Terpadu

Proyek skripsi **Sistem Web Portal Multi-Role Terpadu dan Bot WhatsApp** dengan alur FSM, presensi cerdas, pengajuan surat, persetujuan berantai, serta Tanda Tangan Elektronik (TTE) internal berbasis Ed25519.

## Prasyarat

- Windows 11 dan Laragon (atau sistem lain yang mendukung PHP).
- PHP 8.5 dengan ekstensi `openssl`, `sodium`, `gd`, `mbstring`, `fileinfo`, `pdo_mysql`, `curl`, `intl`, dan `zip`.
- Composer 2.
- Node.js dan npm.
- MySQL 8; konfigurasi pengembangan proyek memakai basis data `SMARTCAMPUS`.
- Browser dengan kamera dan GPS untuk demonstrasi presensi langsung.

## Instalasi

Jalankan dari direktori proyek:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm install
```

Atur koneksi MySQL di `.env` (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`), lalu siapkan skema dan data demo:

```powershell
php artisan migrate:fresh --seed
npm run build
```

> **Peringatan:** `migrate:fresh` menghapus seluruh tabel dan data pada basis data yang dikonfigurasi. Pastikan `DB_DATABASE` menunjuk ke basis data pengembangan yang memang boleh direset, bukan basis data produksi.

Jalankan aplikasi:

```powershell
php artisan serve
```

Untuk menerima akses kamera dan lokasi dari browser, gunakan HTTPS atau tunnel HTTPS. `localhost` dapat dianggap konteks aman oleh browser, tetapi perangkat lain yang membuka alamat HTTP biasa tidak dapat menggunakan kamera/GPS.

## Akun Demo

Semua akun di bawah menggunakan kata sandi demo `SmartCampus2026!`.

| Peran | Username | Nama | Catatan |
|---|---|---|---|
| BAAK | `BAAK001` | Staf Administrasi BAAK | Pemeriksaan dan persetujuan awal surat |
| Dosen dan Pimpinan | `DOSEN001` | Prof. Dr. Ir. Budi Santoso, M.T. | Dekan; penyetuju urutan kedua |
| Dosen dan Pimpinan | `DOSEN004` | Dr. Maya Prameswari, M.Si. | Wakil Dekan; penyetuju urutan pertama |
| Dosen | `DOSEN002` | Dr. Ratna Sari, S.Kom., M.Kom. | Dosen pengampu |
| Dosen | `DOSEN003` | Ahmad Fauzi, S.T., M.T. | Dosen pengampu |
| Mahasiswa dan ORMAWA | `220101001` | Ahmad Maulana | Mahasiswa dan ketua BEM |
| Mahasiswa | `220101002` | Siti Nurhaliza | Mahasiswa Teknik Informatika |
| Mahasiswa | `220101003` | Rian Pratama | Mahasiswa Teknik Informatika |
| Mahasiswa | `230201004` | Dewi Lestari | Mahasiswa Sistem Informasi |
| Mahasiswa | `230201005` | Fajar Nugraha | Mahasiswa Sistem Informasi |

## Integrasi Bot WhatsApp

Pengembangan lokal memakai pengirim mode log, sehingga balasan tersimpan pada `storage/logs/laravel.log` tanpa akun Meta:

```dotenv
WHATSAPP_MODE=log
WHATSAPP_VERIFY_TOKEN=
WHATSAPP_APP_SECRET=
```

Untuk WhatsApp Cloud API, atur `WHATSAPP_MODE=cloud`, `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID`, serta `WHATSAPP_APP_SECRET` dan `WHATSAPP_VERIFY_TOKEN`. Nilai rahasia hanya disimpan di `.env` dan tidak di-commit. URL API dan waktu tunggu dapat diatur lewat `WHATSAPP_API_BASE_URL`, `WHATSAPP_API_VERSION`, dan `WHATSAPP_API_TIMEOUT`.

Setelah login, buka **Ikat akun WhatsApp** atau `/whatsapp/ikat`, lalu kirim `BIND <KODE>` dari nomor yang akan dihubungkan. Kode sekali pakai berlaku 10 menit. Webhook Meta harus mengarah ke HTTPS `/webhook/whatsapp` dan menggunakan token verifikasi yang sama. Webhook POST memeriksa `X-Hub-Signature-256`, mengabaikan ID pesan yang telah diproses, serta membatasi pesan per nomor.

Perintah mahasiswa: `jadwal`, `absen`, `ajukan surat`, `tugas`, dan `status surat`. Perintah dosen: `batalkan kelas` dan `tugas kilat`. Bot mengirim tautan presensi bertanda tangan yang berlaku lima menit, meminta konfirmasi pembatalan, serta mendukung tombol status tugas dan unggahan PDF/Word melalui Cloud API.

## Pengingat Kuliah

Pengingat menggunakan antrean tabel `ANTRIANPENGINGAT`; satu jadwal dan mahasiswa hanya dibuatkan satu pengingat per waktu kuliah. Tiap eksekusi memproses paling banyak 30 pesan agar pengiriman tersebar:

```powershell
php artisan jadwal:pengingat
php artisan schedule:work
```

`jadwal:pengingat` terdaftar untuk berjalan setiap menit. Mode `log` menulis pengingat ke log; mode `cloud` mengirimnya melalui WhatsApp Cloud API. Nomor yang belum terikat ditandai gagal dengan catatan agar dapat ditindaklanjuti.

## Rute Utama

| Metode | URL | Akses / fungsi |
|---|---|---|
| GET | `/login` | Form login |
| POST | `/login` | Autentikasi |
| GET, POST | `/pilih-peran` | Memilih konteks akun multi-peran (terautentikasi) |
| POST | `/logout` | Keluar |
| GET | `/mahasiswa/dasbor` | Dasbor mahasiswa |
| GET, POST | `/mahasiswa/pengajuan-surat` | Riwayat dan pengajuan surat |
| GET | `/dosen/dasbor` | Dasbor dosen |
| GET | `/dosen/pertemuan` | Sesi dan tautan presensi |
| POST | `/dosen/pertemuan/{jadwal}/buka` | Membuka sesi presensi |
| POST | `/dosen/pertemuan/{pertemuan}/tutup` | Menutup sesi presensi |
| GET | `/dosen/rekap-presensi` | Rekap dan peninjauan presensi |
| GET, POST | `/dosen/tugas` | Daftar dan pembuatan tugas |
| GET | `/baak/surat` | Daftar surat untuk BAAK |
| POST | `/baak/surat/{administrasi}/tinjau` | Menyetujui lanjut atau meminta revisi |
| GET | `/pimpinan/surat` | Antrean persetujuan pimpinan |
| POST | `/pimpinan/surat/{persetujuan}/setujui` | Persetujuan TTE dengan kata sandi ulang |
| POST | `/pimpinan/surat/{persetujuan}/tolak` | Menolak surat dengan catatan |
| GET | `/verifikasi/{token}` | Verifikasi publik seluruh TTE dan hash isi; 30 permintaan/menit |
| POST | `/verifikasi/{token}/bandingkan` | Membandingkan SHA-256 PDF unggahan; 10 permintaan/menit |
| GET | `/presensi/{mahasiswa}/{pertemuan}` | Halaman presensi dengan URL bertanda tangan |
| GET, POST | `/webhook/whatsapp` | Verifikasi webhook dan penerimaan pesan WhatsApp |
| GET | `/whatsapp/ikat` | Kode pengikatan WhatsApp (terautentikasi) |

Seluruh rute peran dilindungi autentikasi dan middleware `DeteksiPeran`. Gunakan `php artisan route:list` untuk melihat daftar lengkap beserta nama rute dan middleware.

## TTE dan Pemeriksaan Integritas

Saat BAAK meloloskan surat, isi kanonik dibekukan dan `HASHISI` dihitung. Setiap pimpinan menandatangani muatan JSON dengan kunci privat Ed25519 terenkripsi; tanda tangan, sidik jari kunci, dan token verifikasi disimpan per penyetuju. Setelah semua persetujuan selesai, PDF ber-QR diterbitkan dan `HASHBERKAS` dihitung atas PDF final.

Halaman `/verifikasi/{token}` memeriksa tanda tangan setiap penyetuju, sidik jari kunci, isi kanonik terhadap `HASHISI`, serta dapat membandingkan PDF unggahan dengan `HASHBERKAS`. **TTE ini tidak tersertifikasi dan hanya untuk kebutuhan internal; bukan pengganti validasi penyelenggara sertifikasi elektronik.**

## Pengujian dan Pemeriksaan Kode

```powershell
php artisan test
php artisan route:list
vendor/bin/pint --dirty --format agent
```

`tests/Feature/TandaTanganElektronikTest.php` memeriksa TTE sah, perubahan isi surat, muatan, dan tanda tangan, token acak, perbandingan PDF asli/ubah, autentikasi ulang, serta akses akun yang tidak berhak. Tes fitur bot dan pengingat memeriksa kode pengikatan sekali pakai, unggah tugas, deduplikasi pesan, serta antrean pengingat.

## Naskah Demo Sidang (±10 Menit)

1. **Menit 0–1 — Login multi-role:** masuk sebagai `DOSEN001`, tunjukkan pemilihan konteks Pimpinan/Dosen; lalu masuk sebagai mahasiswa.
2. **Menit 1–2 — Bot WhatsApp:** buka `/whatsapp/ikat`, ikat nomor demo, kirim `jadwal`/`menu`, dan perlihatkan balasan mode log. Jika Cloud API tidak tersedia, gunakan log sebagai rencana cadangan.
3. **Menit 2–4 — Presensi live:** sebagai dosen buka sesi; sebagai mahasiswa buka tautan presensi melalui HTTPS, izinkan kamera depan dan GPS, lakukan instruksi liveness, lalu kirim. Tunjukkan foto dan posisi pada rekap dosen.
4. **Menit 4–5 — Uji risiko anti-joki:** lakukan percobaan dari lokasi di luar geofence atau kondisi yang memicu pemeriksaan tambahan. Tunjukkan hasil `PERLUREVIEW` untuk ditinjau dosen atau `DITOLAK` bila risikonya melewati ambang. Penilai wajah bawaan adalah **stub demo**; jangan menyebutnya sebagai deteksi biometrik produksi.
5. **Menit 5–6 — Pengajuan surat:** ajukan surat sebagai mahasiswa, tunjukkan status `DIAJUKAN`, lalu periksa lampiran dan checklist sebagai BAAK. Pilih lanjut agar nomor dan isi kanonik surat diterbitkan.
6. **Menit 6–8 — Persetujuan TTE:** login sebagai `DOSEN004`, ACC dengan kata sandi ulang; lanjutkan sebagai `DOSEN001` dan ACC. Tunjukkan PDF final dengan blok tanda tangan serta QR untuk masing-masing penyetuju.
7. **Menit 8–9 — Verifikasi publik:** pindai QR dari PDF dengan ponsel/pemindai QR, buka halaman publik, tunjukkan hasil tiap tanda tangan `VALID`, sidik jari, hash isi, dan pemeriksaan SHA-256 PDF.
8. **Menit 9–10 — Bukti anti-perubahan:** unggah salinan PDF yang diubah dan tunjukkan hasil `BERBEDA`. Untuk uji isi surat, gunakan skenario pengujian otomatis agar rekaman produksi tidak dimutasi.

## Checklist Persiapan Sidang

- [ ] Siapkan tunnel HTTPS/domain dengan sertifikat aktif; set `APP_URL` ke alamat yang benar.
- [ ] Pastikan kamera dan GPS browser diizinkan; uji dari perangkat yang akan dipakai.
- [ ] Siapkan akun demo serta kata sandi dan sesi browser terpisah untuk mahasiswa, BAAK, dan dua pimpinan.
- [ ] Jika mendemonstrasikan Cloud API, siapkan nomor demo, webhook HTTPS, access token aktif, `PHONE_NUMBER_ID`, app secret, dan verify token; jangan tampilkan rahasia di layar.
- [ ] Pastikan mode WhatsApp `log` siap sebagai cadangan jika jaringan/Cloud API bermasalah.
- [ ] Jalankan `php artisan migrate:fresh --seed` sebelum sidang hanya pada basis data demo yang boleh dihapus.
- [ ] Jalankan `php artisan test` dan `php artisan route:list`; pastikan pengingat berjalan melalui `php artisan schedule:work`.
- [ ] Siapkan PDF surat demo yang telah terbit, QR yang dapat dipindai, dan salinan PDF yang dimodifikasi untuk pembandingan.
- [ ] Siapkan rencana cadangan: screenshot/log webhook, PDF/QR demo tersimpan, dan video singkat alur kamera/GPS jika perangkat atau HTTPS gagal.
