<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Formulir Akun dan Profil Kampus -->
<!-- ========================================== -->
@extends('layout.app')

@php
    $jenisPeran = $akun
        ? ($akun->ADALAHBAAK ? 'BAAK' : ($akun->mahasiswa ? 'MAHASISWA' : ($akun->dosen?->JABATAN ? 'PIMPINAN' : 'DOSEN')))
        : old('JENISPERAN', 'MAHASISWA');
@endphp

@section('judul', $akun ? 'Ubah Data Pengguna' : 'Tambah Pengguna')

@section('konten')
    <div class="mx-auto" style="max-width: 900px">
        <section class="kartu mb-4">
            <a class="small text-decoration-none" href="{{ route('baak.pengguna.index') }}">&larr; Kembali ke data pengguna</a>
            <h1 class="h3 mt-3 mb-1">{{ $akun ? 'Ubah Data Pengguna' : 'Tambah Akun Kampus' }}</h1>
            <p class="teks-bantu mb-0">Data profil mengikuti peran akun dan dapat diperbarui oleh petugas BAAK.</p>
        </section>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="kartu">
            <form id="formPengguna" method="POST" action="{{ $akun ? route('baak.pengguna.update', $akun) : route('baak.pengguna.store') }}" data-peran="{{ $jenisPeran }}">
                @csrf
                @if ($akun)
                    @method('PUT')
                @endif
                <div class="row g-3">
                    @unless ($akun)
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold" for="JENISPERAN">Peran akun</label>
                            <select class="form-select" id="JENISPERAN" name="JENISPERAN">
                                @foreach (['MAHASISWA' => 'Mahasiswa', 'DOSEN' => 'Dosen', 'PIMPINAN' => 'Pimpinan', 'BAAK' => 'Petugas BAAK'] as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($jenisPeran === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Peran akun</label>
                            <input class="form-control" value="{{ $jenisPeran }}" disabled>
                            <div class="form-text">Peran akun tidak dapat diubah karena terkait riwayat akademik.</div>
                        </div>
                    @endunless
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="STATUSAKUN">Status akun</label>
                        <select class="form-select" id="STATUSAKUN" name="STATUSAKUN">
                            @foreach (['AKTIF', 'NONAKTIF'] as $status)
                                <option value="{{ $status }}" @selected(old('STATUSAKUN', $akun?->STATUSAKUN ?? 'AKTIF') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="USERNAME">Username / NIM / NIDN</label>
                        <input class="form-control" id="USERNAME" name="USERNAME" value="{{ old('USERNAME', $akun?->USERNAME) }}" required maxlength="255">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="NAMALENGKAP">Nama lengkap</label>
                        <input class="form-control" id="NAMALENGKAP" name="NAMALENGKAP" value="{{ old('NAMALENGKAP', $akun?->NAMALENGKAP) }}" required maxlength="255">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="EMAIL">Email</label>
                        <input class="form-control" type="email" id="EMAIL" name="EMAIL" value="{{ old('EMAIL', $akun?->EMAIL) }}" maxlength="255">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="NOMORTELEPON">Nomor telepon</label>
                        <input class="form-control" id="NOMORTELEPON" name="NOMORTELEPON" value="{{ old('NOMORTELEPON', $akun?->NOMORTELEPON) }}" maxlength="255">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="PASSWORD">{{ $akun ? 'Kata sandi baru (opsional)' : 'Kata sandi' }}</label>
                        <input class="form-control" type="password" id="PASSWORD" name="PASSWORD" autocomplete="new-password" {{ $akun ? '' : 'required' }}>
                        <div class="form-text">Minimal 12 karakter.</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold" for="PASSWORD_confirmation">Ulangi kata sandi</label>
                        <input class="form-control" type="password" id="PASSWORD_confirmation" name="PASSWORD_confirmation" autocomplete="new-password" {{ $akun ? '' : 'required' }}>
                    </div>

                    <div class="col-12 col-md-6" data-profil="MAHASISWA">
                        <label class="form-label fw-semibold" for="NIM">NIM</label>
                        <input class="form-control" id="NIM" name="NIM" value="{{ old('NIM', $akun?->mahasiswa?->NIM) }}" maxlength="255">
                    </div>
                    <div class="col-12 col-md-6" data-profil="MAHASISWA">
                        <label class="form-label fw-semibold" for="ANGKATAN">Angkatan</label>
                        <input class="form-control" id="ANGKATAN" name="ANGKATAN" value="{{ old('ANGKATAN', $akun?->mahasiswa?->ANGKATAN) }}" inputmode="numeric">
                    </div>

                    <div class="col-12 col-md-6" data-profil="DOSEN,PIMPINAN">
                        <label class="form-label fw-semibold" for="NIDN">NIDN</label>
                        <input class="form-control" id="NIDN" name="NIDN" value="{{ old('NIDN', $akun?->dosen?->NIDN) }}" maxlength="255">
                    </div>
                    <div class="col-12 col-md-6" data-profil="DOSEN,PIMPINAN">
                        <label class="form-label fw-semibold" for="NIP">NIP</label>
                        <input class="form-control" id="NIP" name="NIP" value="{{ old('NIP', $akun?->dosen?->NIP) }}" maxlength="255">
                    </div>

                    <div class="col-12 col-md-6" data-profil="MAHASISWA,DOSEN,PIMPINAN">
                        <label class="form-label fw-semibold" for="PROGRAMSTUDI">Program studi</label>
                        <input class="form-control" id="PROGRAMSTUDI" name="PROGRAMSTUDI" value="{{ old('PROGRAMSTUDI', $akun?->mahasiswa?->PROGRAMSTUDI ?? $akun?->dosen?->PROGRAMSTUDI) }}" maxlength="255">
                    </div>
                    <div class="col-12 col-md-6" data-profil="PIMPINAN">
                        <label class="form-label fw-semibold" for="JABATAN">Jabatan pimpinan</label>
                        <input class="form-control" id="JABATAN" name="JABATAN" value="{{ old('JABATAN', $akun?->dosen?->JABATAN) }}" maxlength="255">
                    </div>
                    <div class="col-12 col-md-6" data-profil="PIMPINAN">
                        <label class="form-label fw-semibold" for="LEVELPERSETUJUAN">Level persetujuan surat</label>
                        <input class="form-control" type="number" id="LEVELPERSETUJUAN" name="LEVELPERSETUJUAN" value="{{ old('LEVELPERSETUJUAN', $akun?->dosen?->LEVELPERSETUJUAN) }}" min="1" max="10">
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-primary" type="submit">{{ $akun ? 'Simpan perubahan' : 'Buat akun' }}</button>
                    <a class="btn btn-light border" href="{{ route('baak.pengguna.index') }}">Batal</a>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('skrip')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Menampilkan Field sesuai Peran yang Dipilih -->
    <!-- ========================================== -->
    <script>
        const formPengguna = document.getElementById('formPengguna');
        const pemilihPeran = document.getElementById('JENISPERAN');
        const peranAwal = formPengguna.dataset.peran;

        const aturFormProfil = () => {
            const peran = pemilihPeran ? pemilihPeran.value : peranAwal;
            formPengguna.querySelectorAll('[data-profil]').forEach((kelompok) => {
                const aktif = kelompok.dataset.profil.split(',').includes(peran);
                kelompok.hidden = !aktif;
                kelompok.querySelectorAll('input').forEach((input) => {
                    input.disabled = !aktif;
                });
            });
        };

        pemilihPeran?.addEventListener('change', aturFormProfil);
        aturFormProfil();
    </script>
@endpush
