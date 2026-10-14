# Protokol Pengumpulan Data Wajah

## A. Persiapan dan etik

1. Dapatkan izin tertulis institusi dan persetujuan etik yang berlaku sebelum merekrut peserta atau mengambil gambar.
2. Gunakan lembar persetujuan biometrik, jelaskan tujuan, risiko, akses, retensi, hak menarik diri, dan batas penghapusan sebelum persetujuan.
3. Rekrut peserta sukarela; jangan menjadikan presensi akademik atau nilai sebagai tekanan untuk berpartisipasi.
4. Tetapkan satu petugas, area pengambilan, perangkat, posisi kamera, dan pencahayaan; dokumentasikan penyimpangan.
5. Buat kode anonim P01, P02, dan seterusnya. Simpan daftar penghubung identitas (bila perlu) terpisah, terenkripsi, dan dengan akses minimum. Jangan gunakan nama di jalur atau nama berkas.
6. Atur kamera, resolusi, orientasi, jarak, dan fokus otomatis secara konsisten. Catat model perangkat, versi OS/kamera, resolusi, dan tanggal relatif/sesi tanpa memasukkannya ke nama berkas.
7. Jangan menggunakan filter kecantikan, mode potret, penghalusan kulit, perubahan warna, atau pengeditan foto. Simpan berkas asli pada media terenkripsi; akses dataset hanya untuk tim yang disetujui.

## B. Kondisi dan jumlah pengambilan

Ambil sekurangnya **5 foto referensi** dan **3 foto untuk setiap kondisi uji**. Jika kondisi tidak dapat dilakukan, catat sebagai data hilang dan jangan mengganti labelnya. Gunakan satu foto wajah utama per bingkai dan periksa fokus sebelum melanjutkan.

| Kondisi | Prosedur terkendali | Variabel yang dicatat |
|---|---|---|
| `referensi` | Wajah frontal, ekspresi netral, tanpa aksesori penutup; jarak tetap 0,5 m. | Perangkat, jarak, iluminansi, sesi |
| `terang` | Frontal pada kondisi terang yang diukur; jangan mengubah setelan kamera. | Iluminansi terukur (lux), arah cahaya |
| `redup` | Frontal pada tingkat cahaya rendah yang masih aman dan telah ditentukan; tidak memakai lampu kilat jika referensi tidak memakainya. | Lux, lampu kilat hidup/mati |
| `sudut` | Ambil sudut yaw kiri dan kanan bertahap (mis. 15°, 30°, 45°); kamera dan jarak tetap. Catat sudut aktual/target. | Arah dan besar sudut |
| `aksesori` | Ambil kondisi aksesori yang disetujui (mis. kacamata bening dan/atau masker); jangan menutupi peserta tanpa izin. | Jenis aksesori, urutan |
| `jarak` | Frontal pada jarak 1 m dan 2 m (ukur dari kamera ke posisi wajah); jangan mengubah zoom digital. | Jarak terukur, ukuran wajah dalam piksel |

Urutan kondisi diacak per peserta menggunakan seed yang dicatat dalam catatan sesi, agar urutan tidak selalu sama. Beri jeda dan hentikan jika peserta tidak nyaman.

## C. Serangan presentasi

1. Hanya dilakukan setelah mendapat izin etik/institusi dan persetujuan terpisah yang menjelaskan risiko tambahan.
2. Gunakan foto peserta yang memang disetujui untuk pembuatan replika. Jangan unggah/teruskan ke layanan pihak ketiga.
3. **Cetak:** cetak foto pada satu ukuran/kertas yang dicatat; letakkan pada jarak dan orientasi yang ditetapkan. Ambil sekurangnya 3 pengulangan per peserta dengan kondisi kamera terkendali.
4. **Layar:** tampilkan foto pada perangkat layar yang model, ukuran, kecerahan, dan jarak pandangnya dicatat; ambil sekurangnya 3 pengulangan per peserta.
5. Jangan menambahkan gerakan, manipulasi, atau teknik bypass lain. Dataset serangan hanya digunakan di laboratorium lokal yang disetujui.
6. Simpan berkas di `_serangan/cetak/P01/` atau `_serangan/layar/P01/`. Jangan membuat tangkapan layar yang memuat nama, chat, notifikasi, atau akun pribadi.

## D. Penamaan dan struktur

Simpan JPG asli tanpa informasi nama pada nama file:

```text
evaluasi_wajah/data_wajah/
  P01/
    referensi/P01_ref_01.jpg
    terang/P01_terang_01.jpg
    redup/P01_redup_01.jpg
    sudut/P01_sudut_15_kiri_01.jpg
    aksesori/P01_aksesori_kacamata_01.jpg
    jarak/P01_jarak_1m_01.jpg
  _serangan/
    cetak/P01/P01_cetak_01.jpg
    layar/P01/P01_layar_01.jpg
```

Nama berkas hanya memuat kode anonim, kondisi, dan nomor urut. Jangan menyimpan nama, NIM, nomor telepon, embedding, atau metadata identitas dalam berkas/direktori data. Sebelum analisis, jalankan `python -m evaluasi_wajah.periksa_struktur_data` dan tinjau data rusak/label salah.

## E. Penanganan, retensi, dan audit

- Batasi penyimpanan pada disk terenkripsi dan akses peran yang disetujui; kunci enkripsi tidak berada di Git.
- Jangan mencatat foto, base64, embedding, token, atau nama peserta ke log/CSV.
- Catat hash, versi perangkat lunak, seed, spesifikasi mesin, setelan kamera, versi bobot, dan tanggal pengumpulan yang disetujui; hash tidak menggantikan perlindungan akses.
- Pisahkan consent dan kunci identitas dari data analisis. Hapus data mentah saat tenggat retensi etik tercapai; dokumentasikan bukti penghapusan.
- Perbarui berkas persetujuan bila tujuan, dataset, layanan pemroses, penerima, atau retensi berubah. Jangan melakukan pengumpulan sebelum seluruh kolom [ISI] pada lembar consent ditetapkan dan disetujui.
