<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Masuk Portal Akademik -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Masuk Portal')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Panel Sambutan dan Form Autentikasi -->
    <!-- ========================================== -->
    <div class="login-konten mx-auto">
        <div class="kartu-login row g-0">
            <section class="login-sambutan col-lg-5 d-flex flex-column justify-content-between p-4 p-lg-5">
                <div>
                    <span class="badge rounded-pill text-bg-light text-primary mb-4">SMART CAMPUS TERPADU</span>
                    <h1 class="display-6 fw-bold mb-3">Satu pintu untuk kegiatan kampus.</h1>
                    <p class="mb-4 opacity-75">Akses layanan akademik, administrasi, presensi, dan kolaborasi sesuai peran Anda.</p>
                </div>
                <div class="fitur-login pt-3">
                    <div class="d-flex gap-3 py-2"><span>✓</span><span>Dashboard mahasiswa, dosen, BAAK, dan pimpinan</span></div>
                    <div class="d-flex gap-3 py-2"><span>✓</span><span>Presensi dan pengajuan surat dalam satu portal</span></div>
                    <div class="d-flex gap-3 py-2"><span>✓</span><span>WhatsApp sebagai kanal penghubung layanan</span></div>
                </div>
            </section>

            <section class="col-lg-7 p-4 p-md-5">
                <div class="mb-4">
                    <h2 class="h3 fw-bold mb-2">Selamat datang</h2>
                    <p class="teks-bantu mb-0">Masuk menggunakan akun kampus Anda.</p>
                </div>

                @if ($errors->has('USERNAME') && ! old('USERNAME'))
                    <div class="alert alert-danger" role="alert">{{ $errors->first('USERNAME') }}</div>
                @endif

                <!-- ========================================== -->
                <!-- PEMBAHASAN / BAGIAN: Form Login Pengguna -->
                <!-- ========================================== -->
                <form action="{{ route('login.proses') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="USERNAME" class="form-label fw-semibold">NIM / NIDN / Kode Pengguna</label>
                        <input id="USERNAME" name="USERNAME" value="{{ old('USERNAME') }}" class="form-control login-input @error('USERNAME') is-invalid @enderror" placeholder="Contoh: DOSEN001 atau 220101001" autocomplete="username" required autofocus>
                        @error('USERNAME')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="PASSWORD" class="form-label fw-semibold">Kata sandi</label>
                        <input type="password" id="PASSWORD" name="PASSWORD" class="form-control login-input @error('PASSWORD') is-invalid @enderror" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
                        @error('PASSWORD')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">Masuk ke portal</button>
                </form>

                <!-- ========================================== -->
                <!-- PEMBAHASAN / BAGIAN: Referensi Akun Demo -->
                <!-- ========================================== -->
                <details class="mt-4">
                    <summary class="small fw-bold teks-bantu">Lihat akun demo untuk pengujian</summary>
                    <div class="small teks-bantu mt-3">
                        <p class="mb-2">Kata sandi akun demo: <code>SmartCampus2026!</code></p>
                        <ul class="mb-0 ps-3">
                            <li>BAAK: <code>BAAK001</code></li>
                            <li>Dosen dan Pimpinan: <code>DOSEN001</code></li>
                            <li>Dosen: <code>DOSEN002</code></li>
                            <li>Mahasiswa dan ORMAWA: <code>220101001</code></li>
                            <li>Mahasiswa: <code>220101002</code></li>
                        </ul>
                    </div>
                </details>
            </section>
        </div>
    </div>
@endsection
