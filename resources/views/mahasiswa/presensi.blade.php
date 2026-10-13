<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Presensi dengan Kamera, GPS, dan Liveness -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Presensi Kuliah')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Identitas Jadwal dan Tantangan -->
    <!-- ========================================== -->
    <section class="kartu mx-auto" style="max-width: 720px">
        <h1 class="h3">Presensi Kuliah</h1>
        <p class="mb-1"><strong>{{ $pertemuan->jadwal->NAMAMATAKULIAH }}</strong> &middot; Pertemuan {{ $pertemuan->PERTEMUANKE }}</p>
        <p class="text-secondary">Mahasiswa: {{ $mahasiswa->pengguna?->NAMALENGKAP ?? $mahasiswa->NAMAMAHASISWA }} ({{ $mahasiswa->NIM }})</p>
        <div class="alert alert-info">
            <strong>Tantangan liveness:</strong> {{ $tantangan['instruksi'] }}
            <div class="small mt-1">Lakukan gerakan di depan kamera. Foto hanya diambil dari kamera, bukan dari galeri.</div>
        </div>

        <form id="form-presensi" action="{{ request()->fullUrl() }}" method="POST">
            @csrf
            <input type="hidden" name="TANTANGAN" value="{{ $tantangan['token'] }}">
            <video id="pratinjau-kamera" class="w-100 rounded bg-dark mb-3" playsinline autoplay muted aria-label="Pratinjau kamera" style="max-height: 420px; object-fit: cover"></video>
            <canvas id="kanvas-foto" class="d-none"></canvas>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button id="tombol-kamera" class="btn btn-primary" type="button">Aktifkan Kamera Depan</button>
                <button id="tombol-kirim" class="btn btn-success" type="submit" disabled>Ambil Foto dan Kirim Presensi</button>
            </div>
            <div class="form-check mb-3">
                <input id="konfirmasi-liveness" class="form-check-input" type="checkbox" value="1" required>
                <label class="form-check-label" for="konfirmasi-liveness">Saya telah melakukan gerakan sesuai tantangan di atas.</label>
            </div>
            <p id="pesan-presensi" class="mb-0 text-secondary" role="status" aria-live="polite">Aktifkan kamera dan izinkan akses lokasi saat diminta.</p>
        </form>
    </section>
@endsection

@push('skrip')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Ambil Foto Kamera dan Kirim Data GPS -->
    <!-- ========================================== -->
    <script>
        const formulirPresensi = document.getElementById('form-presensi');
        const pratinjauKamera = document.getElementById('pratinjau-kamera');
        const tombolKamera = document.getElementById('tombol-kamera');
        const tombolKirim = document.getElementById('tombol-kirim');
        const pesanPresensi = document.getElementById('pesan-presensi');
        let aliranKamera = null;

        tombolKamera.addEventListener('click', async () => {
            try {
                aliranKamera = await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: { facingMode: { ideal: 'user' }, width: { ideal: 1280 }, height: { ideal: 1280 } }
                });
                pratinjauKamera.srcObject = aliranKamera;
                await pratinjauKamera.play();
                tombolKirim.disabled = false;
                tombolKamera.disabled = true;
                pesanPresensi.textContent = 'Kamera aktif. Lakukan tantangan liveness, lalu ambil foto.';
            } catch (kesalahan) {
                pesanPresensi.textContent = `Kamera tidak dapat diaktifkan: ${kesalahan.message}`;
            }
        });

        formulirPresensi.addEventListener('submit', async (peristiwa) => {
            peristiwa.preventDefault();

            if (!document.getElementById('konfirmasi-liveness').checked || !aliranKamera) {
                pesanPresensi.textContent = 'Aktifkan kamera dan selesaikan tantangan liveness terlebih dahulu.';
                return;
            }

            tombolKirim.disabled = true;
            pesanPresensi.textContent = 'Mengambil lokasi GPS...';

            try {
                const posisi = await new Promise((selesai, gagal) => {
                    navigator.geolocation.getCurrentPosition(selesai, gagal, {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 15000
                    });
                });
                const kanvas = document.getElementById('kanvas-foto');
                const batas = 1280;
                const skala = Math.min(1, batas / Math.max(pratinjauKamera.videoWidth, pratinjauKamera.videoHeight));
                kanvas.width = Math.round(pratinjauKamera.videoWidth * skala);
                kanvas.height = Math.round(pratinjauKamera.videoHeight * skala);
                kanvas.getContext('2d').drawImage(pratinjauKamera, 0, 0, kanvas.width, kanvas.height);
                const foto = await new Promise((selesai) => kanvas.toBlob(selesai, 'image/jpeg', 0.72));

                if (!foto) {
                    throw new Error('Foto dari kamera gagal diproses.');
                }

                const data = new FormData(formulirPresensi);
                data.set('FOTOPRESENSI', foto, 'presensi.jpg');
                data.set('LINTANG', posisi.coords.latitude);
                data.set('BUJUR', posisi.coords.longitude);
                data.set('AKURASIGPS', posisi.coords.accuracy);
                data.set('LIVENESSLOLOS', '1');
                pesanPresensi.textContent = 'Mengirim foto dan data presensi...';

                const respons = await fetch(formulirPresensi.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: data
                });
                const hasil = await respons.json();

                if (!respons.ok) {
                    throw new Error(hasil.message || Object.values(hasil.errors || {}).flat().join(' ') || 'Presensi gagal dikirim.');
                }

                pesanPresensi.textContent = hasil.pesan;
                if (aliranKamera) {
                    aliranKamera.getTracks().forEach((jalur) => jalur.stop());
                    aliranKamera = null;
                }
            } catch (kesalahan) {
                pesanPresensi.textContent = `Presensi tidak berhasil: ${kesalahan.message}`;
                tombolKirim.disabled = false;
            }
        });

        window.addEventListener('beforeunload', () => {
            if (aliranKamera) {
                aliranKamera.getTracks().forEach((jalur) => jalur.stop());
            }
        });
    </script>
@endpush
