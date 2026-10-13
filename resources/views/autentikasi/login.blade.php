<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Masuk Satu Pintu (Login) -->
<!-- ========================================== -->
@extends('layout.utama')

@section('judul', 'Masuk Satu Pintu')

@push('gaya')
<style>
    .wadah-login {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 180px);
    }

    .kartu-login {
        width: 100%;
        max-width: 440px;
        background: rgba(18, 24, 38, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 2.5rem;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(79, 70, 229, 0.2);
    }

    .kepala-login {
        text-align: center;
        margin-bottom: 2rem;
    }

    .kepala-login h1 {
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #ffffff, #cbd5e1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .kepala-login p {
        color: var(--warna-teks-redup);
        font-size: 0.875rem;
    }

    .grup-form {
        margin-bottom: 1.25rem;
    }

    .label-form {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #cbd5e1;
    }

    .input-form {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #ffffff;
        font-size: 0.925rem;
        transition: all 0.2s ease;
    }

    .input-form:focus {
        outline: none;
        border-color: var(--warna-primer);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25);
        background: rgba(15, 23, 42, 0.9);
    }

    .pesan-galat {
        color: #fca5a5;
        font-size: 0.775rem;
        margin-top: 0.35rem;
    }

    .kotak-peringatan {
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 10px;
        padding: 0.85rem;
        margin-bottom: 1.25rem;
        color: #fca5a5;
        font-size: 0.825rem;
    }

    .kotak-petunjuk {
        margin-top: 1.75rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--warna-border);
        font-size: 0.775rem;
        color: var(--warna-teks-redup);
    }
</style>
@endpush

@section('konten')
<div class="wadah-login">
    <div class="kartu-login">
        <div class="kepala-login">
            <div style="width: 50px; height: 50px; margin: 0 auto 1rem; background: linear-gradient(135deg, #4f46e5, #06b6d4); border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; color: #fff; box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);">
                SC
            </div>
            <h1>Satu Pintu Masuk</h1>
            <p>Portal Akademik & Administrasi Terpadu</p>
        </div>

        @if($errors->has('USERNAME') && !old('USERNAME'))
            <div class="kotak-peringatan">
                {{ $errors->first('USERNAME') }}
            </div>
        @endif

        <!-- Formulir Autentikasi -->
        <form action="{{ route('login.proses') }}" method="POST">
            @csrf
            
            <div class="grup-form">
                <label for="USERNAME" class="label-form">NIM / NIDN / Kode Pengguna</label>
                <input 
                    type="text" 
                    id="USERNAME" 
                    name="USERNAME" 
                    value="{{ old('USERNAME') }}" 
                    class="input-form" 
                    placeholder="Contoh: DOSEN001 atau 220101001" 
                    required 
                    autofocus
                >
                @error('USERNAME')
                    <div class="pesan-galat">{{ $message }}</div>
                @enderror
            </div>

            <div class="grup-form">
                <label for="PASSWORD" class="label-form">Kata Sandi</label>
                <input 
                    type="password" 
                    id="PASSWORD" 
                    name="PASSWORD" 
                    class="input-form" 
                    placeholder="Masukkan kata sandi akun" 
                    required
                >
                @error('PASSWORD')
                    <div class="pesan-galat">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="tombol tombol-primer" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; margin-top: 0.5rem;">
                Masuk ke Portal
            </button>
        </form>

        <div class="kotak-petunjuk">
            <div style="font-weight: 600; color: #e2e8f0; margin-bottom: 0.35rem;">Akun Uji Coba Demo (Sandi: SmartCampus2026!):</div>
            <div>&bull; <strong>Pimpinan & Dosen:</strong> <code>DOSEN001</code> (Peran Ganda)</div>
            <div>&bull; <strong>Mahasiswa & Ormawa:</strong> <code>220101001</code> (Ketua BEM)</div>
            <div>&bull; <strong>Staf BAAK:</strong> <code>BAAK001</code></div>
            <div>&bull; <strong>Dosen Biasa:</strong> <code>DOSEN002</code></div>
            <div>&bull; <strong>Mahasiswa Biasa:</strong> <code>220101002</code></div>
        </div>
    </div>
</div>
@endsection
