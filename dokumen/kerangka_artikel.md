# Kerangka Artikel IMRaD

> Kerangka ini bukan hasil penelitian. Jangan mengisi angka, memilih model pemenang, atau menulis klaim sebelum protokol dijalankan dan data etik yang sah dianalisis.

## Judul sementara

**Perbandingan [ISI: model] untuk Verifikasi Wajah Presensi pada Kondisi Kampus: Evaluasi FAR/FRR dan Ketahanan terhadap Serangan Presentasi**

## Abstrak terstruktur

- **Latar belakang:** [ISI: masalah dan celah riset yang didukung sumber]
- **Tujuan:** membandingkan ArcFace, FaceNet, dan SFace pada protokol yang sama.
- **Metode:** [ISI: jumlah peserta anonim, split peserta, perangkat, kondisi, ambang validasi, bootstrap, uji berpasangan]
- **Hasil:** [ISI: hanya angka dari `evaluasi_wajah/hasil/tabel_hasil.csv`; sebutkan interval kepercayaan]
- **Kesimpulan:** [ISI: simpulan terbatas pada populasi, perangkat, dan protokol ini]
- **Etik/data:** [ISI: izin institusi, nomor persetujuan, ketersediaan data agregat]

## 1. Pendahuluan

### Konteks dan masalah

[ISI: risiko presensi berbasis foto, batas validasi manual/otomatis, tanpa mengklaim efektivitas sebelum diuji.]

### Tinjauan pustaka dan celah

[ISI: sumber ArcFace, FaceNet, SFace, biometrik, PAD, serta studi kampus yang relevan. Verifikasi lisensi kode dan bobot.]

### Pertanyaan penelitian

- **RQ1:** Bagaimana FAR, FRR, akurasi, AUC, dan EER ketiga model pada protokol pengambilan yang setara?
- **RQ2:** Apakah FRR berubah antar-kondisi pencahayaan, sudut, aksesori, dan jarak?
- **RQ3:** Bagaimana tingkat penerimaan foto cetak dan layar pada ambang yang dikunci dari validasi?
- **RQ4:** Bagaimana latensi CPU median dan p95 pada perangkat eksperimen?

### Hipotesis praregistrasi

- **H1:** [ISI hipotesis arah/perbedaan beserta dasar literatur; jangan menetapkan arah setelah melihat hasil.]
- **H2:** [ISI hipotesis kondisi yang akan dibandingkan dan uji statistik.]

## 2. Metode

### Desain dan etik

[ISI: desain, tanggal/sesi, izin etik/institusi, kriteria inklusi/eksklusi, proses consent, pengamanan, dan retensi.]

### Peserta dan dataset

[ISI: jumlah peserta, kode anonim, jumlah gambar per kondisi, data yang dikeluarkan beserta alasan, dan diagram alur.]

### Model dan implementasi

[ISI: versi paket, hash bobot, CPU/OS, lisensi, versi YuNet, ukuran input, lima landmark, alignment, normalisasi, serta parameter model.]

### Protokol genuine/impostor dan split

[ISI: probe melawan templat referensi; minimal tiga target impostor berbeda per peserta; split peserta dengan seed konfigurasi; jumlah pasangan setiap split.]

### Ambang dan metrik

[ISI: ambang EER dipilih hanya pada validasi, dikunci sebelum pengujian; definisi FAR/FRR/akurasi, ROC/AUC/EER; CI 95% bootstrap peserta 1000 ulangan; exact McNemar/alternatif berpasangan.]

### Kondisi dan serangan presentasi

[ISI: prosedur terang/redup/sudut/aksesori/jarak; prosedur foto cetak/layar; tingkat penerimaan serangan pada ambang terkunci.]

### Latensi

[ISI: CPU, versi paket, jumlah warm-up, N ulangan, median/p95, definisi batas pengukuran dan kondisi mesin.]

### Reproduksibilitas

[ISI: hash commit kode, file `lingkungan.txt`, konfigurasi, seed, hash bobot; jelaskan pembatasan akses dataset biometrik.]

## 3. Hasil dan Pembahasan

### Karakteristik data

[ISI: tabel deskriptif tanpa identitas; bedakan data penelitian dari mode DEMO.]

### Kinerja pengenalan dan ambang terkunci

**Tabel 1.** [ISI: ringkasan FAR, FRR, akurasi uji beserta CI 95%, AUC/EER semua data.] Sumber: `evaluasi_wajah/hasil/tabel_hasil.csv`.

**Gambar 1.** [ISI: kurva ROC] dari `evaluasi_wajah/hasil/kurva_roc.png`.

**Gambar 2.** [ISI: kurva DET] dari `evaluasi_wajah/hasil/kurva_det.png`.

[ISI interpretasi perbedaan dengan interval kepercayaan, batas ambang, ukuran data, dan keterbatasan; hindari klaim kausal.]

### Perbedaan keputusan berpasangan

[ISI: tabel uji exact McNemar dengan jumlah discordant dan p-value; laporkan semua pasangan model dan batas interpretasi.]

### FRR per kondisi

**Gambar 3.** [ISI: FRR per kondisi] dari `evaluasi_wajah/hasil/frr_per_kondisi.png`.

[ISI: bandingkan kondisi dengan jumlah genuine yang tersedia; tandai kondisi yang hilang.]

### Serangan presentasi dan latensi

[ISI: tingkat penerimaan serangan cetak dan layar pada ambang validasi; jangan menyebutnya bukti ketahanan umum/PAD tanpa cakupan serangan memadai.]

**Gambar 4.** [ISI: median/p95 latensi CPU] dari `evaluasi_wajah/hasil/kotak_latensi.png`.

### Keterbatasan

[ISI: ukuran/sumber sampel, perangkat, kondisi, representasi populasi, kualitas anotasi, lisensi/model, ambang, potensi bias, PAD terbatas, dan keterbatasan generalisasi.]

## 4. Kesimpulan

[ISI jawaban ringkas untuk RQ berdasarkan hasil; simpulan tidak melampaui dataset/protokol. Tuliskan rekomendasi validasi lanjutan, tata kelola biometrik, jalur review manusia, dan penghapusan sesuai retensi.]

## Data tambahan dan deklarasi

- Data/foto mentah: [ISI kebijakan akses etik; jangan mempublikasikan biometrik tanpa izin].
- Kode/konfigurasi: [ISI tautan/commit yang telah dibersihkan dari rahasia].
- Konflik kepentingan dan pendanaan: [ISI].
- Pernyataan penggunaan AI: [ISI sesuai kebijakan jurnal].
- Referensi: [ISI sitasi primer dan periksa lisensi/versi sumber].
