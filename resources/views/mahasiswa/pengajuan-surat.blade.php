<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Form dan Riwayat Administrasi Surat -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Pengajuan Surat')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Form Pengajuan Surat Dinamis -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <h1 class="h3 mb-3">Pengajuan Surat</h1>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $pesan)
                        <li>{{ $pesan }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('mahasiswa.surat.simpan') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="jenis-surat">Jenis surat</label>
                    <select class="form-select" id="jenis-surat" name="JENISSURAT" required>
                        <option value="">Pilih jenis surat</option>
                        @foreach ([
                            'SURATAKTIF' => 'Surat Keterangan Mahasiswa Aktif',
                            'TRANSKRIP' => 'Permohonan Transkrip',
                            'MAGANG' => 'Surat Pengantar Magang',
                            'DISPENSASI' => 'Surat Dispensasi',
                            'IZINSAKIT' => 'Surat Izin Sakit',
                            'PROPOSALORMAWA' => 'Pengajuan Proposal ORMAWA',
                            'LPJORMAWA' => 'Laporan Pertanggungjawaban ORMAWA',
                        ] as $kode => $nama)
                            <option value="{{ $kode }}" @selected(old('JENISSURAT') === $kode)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label" for="perihal-surat">Perihal</label>
                    <input class="form-control" id="perihal-surat" name="PERIHAL" maxlength="255" value="{{ old('PERIHAL') }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="keperluan-surat">Keperluan</label>
                    <textarea class="form-control" id="keperluan-surat" name="KEPERLUAN" rows="3" maxlength="3000" required>{{ old('KEPERLUAN') }}</textarea>
                </div>

                <fieldset id="bidang-dinamis" class="row g-3 m-0 p-0">
                    <legend class="h6">Data formulir tambahan</legend>
                    <div class="col-12 col-md-6" data-jenis="MAGANG">
                        <label class="form-label" for="nama-perusahaan">Nama perusahaan/instansi</label>
                        <input class="form-control" id="nama-perusahaan" name="DATAFORMULIR[NAMAPERUSAHAAN]" value="{{ old('DATAFORMULIR.NAMAPERUSAHAAN') }}" disabled>
                    </div>
                    <div class="col-12 col-md-6" data-jenis="MAGANG">
                        <label class="form-label" for="alamat-perusahaan">Alamat perusahaan/instansi</label>
                        <input class="form-control" id="alamat-perusahaan" name="DATAFORMULIR[ALAMATPERUSAHAAN]" value="{{ old('DATAFORMULIR.ALAMATPERUSAHAAN') }}" disabled>
                    </div>
                    <div class="col-12 col-md-6" data-jenis="MAGANG,DISPENSASI,IZINSAKIT">
                        <label class="form-label" for="tanggal-mulai">Tanggal mulai</label>
                        <input class="form-control" id="tanggal-mulai" name="DATAFORMULIR[TANGGALMULAI]" type="date" value="{{ old('DATAFORMULIR.TANGGALMULAI') }}" disabled>
                    </div>
                    <div class="col-12 col-md-6" data-jenis="MAGANG,DISPENSASI,IZINSAKIT">
                        <label class="form-label" for="tanggal-selesai">Tanggal selesai</label>
                        <input class="form-control" id="tanggal-selesai" name="DATAFORMULIR[TANGGALSELESAI]" type="date" value="{{ old('DATAFORMULIR.TANGGALSELESAI') }}" disabled>
                    </div>
                    <div class="col-12 col-md-6" data-jenis="DISPENSASI,PROPOSALORMAWA,LPJORMAWA">
                        <label class="form-label" for="nama-kegiatan">Nama kegiatan</label>
                        <input class="form-control" id="nama-kegiatan" name="DATAFORMULIR[NAMAKEGIATAN]" value="{{ old('DATAFORMULIR.NAMAKEGIATAN') }}" disabled>
                    </div>
                    <div class="col-12" data-jenis="DISPENSASI,IZINSAKIT,PROPOSALORMAWA,LPJORMAWA">
                        <label class="form-label" for="alasan-surat">Alasan/keterangan</label>
                        <textarea class="form-control" id="alasan-surat" name="DATAFORMULIR[ALASAN]" rows="2" disabled>{{ old('DATAFORMULIR.ALASAN') }}</textarea>
                    </div>
                </fieldset>

                <div class="col-12">
                    <label class="form-label" for="lampiran-surat">Lampiran (opsional, maksimal 5 MB)</label>
                    <input class="form-control" id="lampiran-surat" name="FILELAMPIRAN" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                    <div class="form-text">Format yang diterima: PDF, JPG, PNG, dan WebP.</div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Kirim Pengajuan</button>
                </div>
            </div>
        </form>
    </section>

    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Riwayat dan Status Pengajuan Pemohon -->
    <!-- ========================================== -->
    <section class="kartu">
        <h2 class="h5 mb-3">Riwayat Pengajuan Saya</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Tanggal</th><th>Jenis</th><th>Perihal</th><th>Status</th><th>Data formulir</th></tr></thead>
                <tbody>
                    @forelse ($pengajuan as $surat)
                        <tr>
                            <td>{{ $surat->TANGGALBUAT?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td>{{ $surat->JENISSURAT }}</td>
                            <td>{{ $surat->PERIHAL }}</td>
                            <td><span class="badge text-bg-secondary">{{ $surat->STATUSADMINISTRASI }}</span></td>
                            <td>
                                @if ($surat->DATAFORMULIR)
                                    <details>
                                        <summary>Lihat</summary>
                                        <dl class="small mt-2 mb-0">
                                            @foreach ($surat->DATAFORMULIR as $nama => $nilai)
                                                <dt>{{ $nama }}</dt><dd>{{ $nilai }}</dd>
                                            @endforeach
                                        </dl>
                                    </details>
                                @else
                                    -
                                @endif
                                @if ($surat->FILELAMPIRAN)
                                    <div class="small text-secondary mt-1">Lampiran tersimpan</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="text-secondary" colspan="5">Belum ada pengajuan surat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $pengajuan->links() }}
    </section>
@endsection

@push('skrip')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Pengaturan Bidang Formulir Berdasar Jenis Surat -->
    <!-- ========================================== -->
    <script>
        const pilihanJenisSurat = document.getElementById('jenis-surat');
        const kelompokBidang = document.querySelectorAll('#bidang-dinamis [data-jenis]');

        function sesuaikanBidangSurat() {
            const jenisTerpilih = pilihanJenisSurat.value;

            kelompokBidang.forEach((kelompok) => {
                const jenisYangMembutuhkan = kelompok.dataset.jenis.split(',');
                const aktif = jenisYangMembutuhkan.includes(jenisTerpilih);
                kelompok.querySelectorAll('input, textarea').forEach((bidang) => {
                    bidang.disabled = !aktif;
                    bidang.required = aktif;
                });
            });
        }

        pilihanJenisSurat.addEventListener('change', sesuaikanBidangSurat);
        sesuaikanBidangSurat();
    </script>
@endpush
