<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Halaman Pemilihan Peran Aktif Akun Multi-Role -->
<!-- ========================================== -->
@extends('layout.utama')

@section('judul', 'Pilih Peran Aktif')

@push('gaya')
<style>
    .wadah-pilih-peran {
        max-width: 760px;
        margin: 2rem auto;
    }

    .kepala-pilih {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .kepala-pilih h1 {
        font-size: 1.75rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #ffffff, #93c5fd);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .kepala-pilih p {
        color: var(--warna-teks-redup);
        font-size: 0.95rem;
    }

    .grid-peran {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .kartu-opsi-peran {
        background: rgba(18, 24, 38, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 1.75rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .kartu-opsi-peran:hover {
        transform: translateY(-4px);
        border-color: rgba(79, 70, 229, 0.5);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4), 0 0 20px rgba(79, 70, 229, 0.2);
    }

    .ikon-peran {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .peran-DOSEN .ikon-peran {
        background: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
    }
    .peran-PIMPINAN .ikon-peran {
        background: rgba(168, 85, 247, 0.2);
        color: #c084fc;
    }
    .peran-MAHASISWA .ikon-peran {
        background: rgba(16, 185, 129, 0.2);
        color: #34d399;
    }
    .peran-ORMAWA .ikon-peran {
        background: rgba(245, 158, 11, 0.2);
        color: #fbbf24;
    }
    .peran-BAAK .ikon-peran {
        background: rgba(236, 72, 153, 0.2);
        color: #f472b6;
    }

    .judul-peran {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
        color: #ffffff;
    }

    .deskripsi-peran {
        font-size: 0.85rem;
        color: var(--warna-teks-redup);
        line-height: 1.5;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('konten')
<div class="wadah-pilih-peran">
    <div class="kepala-pilih">
        <h1>Pilih Peran Masuk</h1>
        <p>Halo, <strong>{{ $pengguna->NAMALENGKAP }}</strong> ({{ $pengguna->USERNAME }}). Akun Anda memiliki lebih dari satu hak akses aktif. Silakan pilih portal kerja Anda saat ini:</p>
    </div>

    @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; color: #fca5a5; font-size: 0.875rem;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid-peran">
        @foreach($daftarPeran as $peran)
            @php
                $detailPeran = match($peran) {
                    'DOSEN' => [
                        'ikon' => '🎓',
                        'judul' => 'Dosen Pengampu',
                        'deskripsi' => 'Kelola jadwal kuliah, verifikasi presensi mahasiswa, materi, dan tugas perkuliahan.'
                    ],
                    'PIMPINAN' => [
                        'ikon' => '🏛️',
                        'judul' => 'Pimpinan Kampus',
                        'deskripsi' => 'Akses persetujuan administrasi surat berbekal Tanda Tangan Elektronik (TTE) berbasis QR Code Ed25519.'
                    ],
                    'MAHASISWA' => [
                        'ikon' => '📚',
                        'judul' => 'Mahasiswa',
                        'deskripsi' => 'Presensi kuliah cerdas, unggah tugas, pantau KRS, dan ajukan surat administrasi digital.'
                    ],
                    'ORMAWA' => [
                        'ikon' => '👥',
                        'judul' => 'Pengurus Ormawa',
                        'deskripsi' => 'Kelola proposal kegiatan organisasi mahasiswa, izin kegiatan, dan laporan pertanggungjawaban (LPJ).'
                    ],
                    'BAAK' => [
                        'ikon' => '🏢',
                        'judul' => 'Biro BAAK',
                        'deskripsi' => 'Verifikasi berkas administrasi akademik, validasi surat mahasiswa, dan registrasi.'
                    ],
                    default => [
                        'ikon' => '👤',
                        'judul' => $peran,
                        'deskripsi' => 'Akses sistem sesuai peran terdaftar.'
                    ]
                };
            @endphp

            <div class="kartu-opsi-peran peran-{{ $peran }}">
                <div>
                    <div class="ikon-peran">{{ $detailPeran['ikon'] }}</div>
                    <div class="judul-peran">{{ $detailPeran['judul'] }}</div>
                    <div class="deskripsi-peran">{{ $detailPeran['deskripsi'] }}</div>
                </div>

                <form action="{{ route('autentikasi.pilihperan.simpan') }}" method="POST">
                    @csrf
                    <input type="hidden" name="PERAN" value="{{ $peran }}">
                    <button type="submit" class="tombol tombol-primer" style="width: 100%;">
                        Masuk sebagai {{ $peran }} &rarr;
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
