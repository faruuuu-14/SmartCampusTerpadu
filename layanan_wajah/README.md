# Layanan Wajah Smart Campus

API FastAPI stateless ini hanya menerima unggahan di memori, tidak menyimpan foto/embedding, dan hanya mendengarkan `127.0.0.1`. Jangan membuka port ke jaringan publik. Semua permintaan model memerlukan `X-KUNCI-LAYANAN`; nilai `WAJAH_KUNCI` harus sama dengan Laravel.

## Persiapan Windows

Python yang tersedia saat pengembangan: 3.14.6. Buat lingkungan dan pasang dependensi terkunci:

```powershell
py -3.14 -m venv layanan_wajah\.venv
layanan_wajah\.venv\Scripts\python.exe -m pip install -r layanan_wajah\requirements.txt
layanan_wajah\.venv\Scripts\python.exe -m layanan_wajah.unduh_model
```

Simpan `WAJAH_KUNCI` di `.env` akar Laravel (jangan commit rahasia), lalu jalankan `layanan_wajah\jalankan_layanan_wajah.bat`. Pengunduh YuNet/SFace memeriksa SHA-256 sebelum menerima bobot dan mencetak nilainya. Hash versi yang diuji: YuNet `8f2383e4dd3cfbb4553ea8718107fc0423210dc964f9f4280604804ed2552fa4`, SFace `0ba9fbfa01b5270c96627c4ef784da859931e02f04419c829e83484087c34e79`. Untuk ArcFace, sediakan file ResNet-50 `w600k_r50.onnx` yang lisensinya mengizinkan penelitian dan atur `MODEL_ARCFACE` ke jalur lokalnya. Bobot ArcFace tidak diunduh otomatis karena batasan lisensinya. Bobot model tidak masuk Git.

Semua endpoint, termasuk `GET /v1/kesehatan`, memerlukan header `X-KUNCI-LAYANAN`. Endpoint `/v1/embedding` dan `/v1/verifikasi` menggunakan multipart `foto`, `model`, serta `referensi_base64` untuk verifikasi. Contoh model: `ArcFace`, `FaceNet`, `SFace`. Endpoint kesehatan hanya memeriksa proses dan kunci, bukan memuat bobot; tiap bobot diuji ketika model dipanggil. Jalankan tes dengan `layanan_wajah\.venv\Scripts\python.exe -m pytest layanan_wajah\tests evaluasi_wajah\tests`.

## Kontrol eksperimen dan batasan model

Semua model memakai deteksi YuNet yang sama dan lima landmark (mata, hidung, sudut mulut). Satu wajah harus terdeteksi untuk ekstraksi. Foto didekode dalam memori, landmark disejajarkan dengan transformasi afin parsial ke template lima titik, dan dipotong ke 112×112 untuk ArcFace/SFace atau 160×160 untuk FaceNet. Embedding dinormalisasi L2; jarak verifikasi adalah cosine similarity. Kualitas yang dilaporkan meliputi keyakinan detektor, proporsi luas wajah, kecerahan grayscale, dan varians Laplacian. Tidak ada keputusan ambang di layanan; Laravel menentukan jalur review/kehadiran.

- **ArcFace, InsightFace ResNet-50 `w600k_r50`**: bobot InsightFace Pack umumnya dibatasi penggunaan nonkomersial/riset. Periksa dan patuhi lisensi model spesifik; lisensi kode tidak otomatis melisensikan bobot.
- **FaceNet, `facenet-pytorch` InceptionResnetV1 VGGFace2**: kode paket MIT; lisensi/ketentuan sumber bobot VGGFace2 dan dataset pralatih tetap perlu ditinjau sebelum publikasi atau penggunaan komersial.
- **SFace, OpenCV `FaceRecognizerSF`**: kode OpenCV berlisensi Apache-2.0; periksa lisensi bobot ONNX OpenCV Zoo yang diunduh dan simpan hash bobot bersama hasil penelitian.
- **YuNet**: bobot OpenCV Zoo berbeda dari kode OpenCV; periksa lisensi dan simpan hash file yang digunakan.

Ketersediaan wheel untuk Python 3.14, InsightFace, PyTorch, dan facenet-pytorch berubah antar-rilis. Jika instalasi InsightFace gagal di Windows, catat versi dan gunakan pengganti yang dipatok serta dilaporkan (misalnya DeepFace Facenet512 atau model dlib yang lisensinya sesuai), jangan mengklaim ketiga model sebagai setara tanpa perubahan protokol dan konfigurasi. Hindari mengganti model atau bobot secara diam-diam.
