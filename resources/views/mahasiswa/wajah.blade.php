<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Persetujuan dan Pendaftaran Biometrik -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Pendaftaran Wajah')

@section('konten')
    <section class="kartu mb-4">
        <h1 class="h3 mb-1">Pendaftaran wajah untuk presensi</h1>
        <p class="teks-bantu mb-2">Pendaftaran bersifat sukarela. Tanpa persetujuan, foto dan embedding tidak akan didaftarkan.</p>
        <p class="mb-0">Status: <span class="badge text-bg-secondary">{{ $mahasiswa->STATUSWAJAH }}</span></p>
        @if ($mahasiswa->TANGGALKONSENBIOMETRIK)
            <form class="mt-3" method="POST" action="{{ route('mahasiswa.wajah.cabut') }}" onsubmit="return confirm('Cabut persetujuan dan hapus foto serta embedding referensi?')">
                @csrf
                <button class="btn btn-sm btn-outline-danger" type="submit">Cabut persetujuan biometrik</button>
            </form>
        @endif
    </section>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="kartu">
        <h2 class="h5">Persetujuan dan foto referensi</h2>
        <p>Foto referensi disimpan secara privat dan representasi biometrik dienkripsi. BAAK meninjau pendaftaran sebelum dipakai untuk presensi otomatis. Hubungi BAAK untuk mencabut persetujuan.</p>
        <form id="form-wajah" method="POST" action="{{ route('mahasiswa.wajah.daftar') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="PERSETUJUAN" id="persetujuan" value="1" required>
                <label class="form-check-label" for="persetujuan">
                    Saya memahami pemrosesan data biometrik dan secara sukarela menyetujui pendaftaran foto wajah untuk validasi presensi.
                </label>
            </div>
            <div class="mb-3">
                <video id="kamera-wajah" class="w-100 rounded border" autoplay playsinline muted aria-label="Pratinjau kamera"></video>
                <canvas id="kanvas-wajah" class="d-none"></canvas>
                <input class="d-none" id="foto-wajah" type="file" name="foto" accept="image/jpeg" required>
                <p id="status-kamera" class="form-text">Aktifkan kamera, posisikan satu wajah menghadap ke depan, lalu ambil foto.</p>
            </div>
            <button class="btn btn-outline-primary" id="ambil-wajah" type="button" disabled>Ambil foto referensi</button>
            <button class="btn btn-primary" id="kirim-wajah" type="submit" disabled>Kirim untuk ditinjau BAAK</button>
        </form>
    </section>
@endsection

@push('skrip')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Kamera Langsung Tanpa Galeri Foto -->
    <!-- ========================================== -->
    <script>
        (() => {
            const video = document.getElementById('kamera-wajah');
            const kanvas = document.getElementById('kanvas-wajah');
            const masukanFoto = document.getElementById('foto-wajah');
            const tombolAmbil = document.getElementById('ambil-wajah');
            const tombolKirim = document.getElementById('kirim-wajah');
            const status = document.getElementById('status-kamera');
            let aliran;

            if (!navigator.mediaDevices?.getUserMedia) {
                status.textContent = 'Peramban ini tidak mendukung kamera. Gunakan peramban modern melalui HTTPS atau localhost.';
                return;
            }
            navigator.mediaDevices.getUserMedia({
                audio: false,
                video: { facingMode: 'user' }
            }).then((hasil) => {
                aliran = hasil;
                video.srcObject = hasil;
                tombolAmbil.disabled = false;
            }).catch(() => {
                status.textContent = 'Kamera tidak tersedia. Gunakan HTTPS atau localhost dan izinkan akses kamera.';
            });

            tombolAmbil.addEventListener('click', () => {
                if (!video.videoWidth || !video.videoHeight) {
                    status.textContent = 'Kamera belum siap. Tunggu pratinjau muncul.';
                    return;
                }
                kanvas.width = video.videoWidth;
                kanvas.height = video.videoHeight;
                kanvas.getContext('2d').drawImage(video, 0, 0);
                kanvas.toBlob((blob) => {
                    if (!blob) {
                        status.textContent = 'Foto gagal dibuat; silakan ulangi.';
                        return;
                    }
                    const berkas = new File([blob], 'referensi.jpg', { type: 'image/jpeg' });
                    const transfer = new DataTransfer();
                    transfer.items.add(berkas);
                    masukanFoto.files = transfer.files;
                    tombolKirim.disabled = false;
                    status.textContent = 'Foto diambil. Pastikan persetujuan dicentang sebelum mengirim.';
                }, 'image/jpeg', 0.88);
            });

            window.addEventListener('pagehide', () => {
                aliran?.getTracks().forEach((jalur) => jalur.stop());
            });
        })();
    </script>
@endpush
