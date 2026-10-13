<!-- ========================================== -->
<!-- PEMBAHASAN / BAGIAN: Pengelolaan Sesi dan Tautan Presensi -->
<!-- ========================================== -->
@extends('layout.app')

@section('judul', 'Kelola Pertemuan')

@section('konten')
    <!-- ========================================== -->
    <!-- PEMBAHASAN / BAGIAN: Daftar Jadwal Mengajar -->
    <!-- ========================================== -->
    <section class="kartu mb-4">
        <h1 class="h3 mb-2">Kelola Pertemuan</h1>
        <p class="text-secondary mb-0">Buka atau tutup sesi presensi untuk jadwal yang Anda ampu. Tautan presensi mahasiswa berlaku lima menit.</p>
    </section>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    @forelse ($jadwal as $mataKuliah)
        @php($pertemuanAktif = $mataKuliah->pertemuan->firstWhere('STATUSPERTEMUAN', 'BERLANGSUNG'))
        <section class="kartu mb-4">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <h2 class="h5 mb-1">{{ $mataKuliah->NAMAMATAKULIAH }} ({{ $mataKuliah->KODEMATAKULIAH }})</h2>
                    <p class="small text-secondary mb-0">Kelas {{ $mataKuliah->KELAS }} &middot; {{ $mataKuliah->HARI }} {{ $mataKuliah->JAMMULAI }}–{{ $mataKuliah->JAMSELESAI }} &middot; {{ $mataKuliah->RUANGAN }}</p>
                </div>
                @if ($pertemuanAktif)
                    <form method="POST" action="{{ route('dosen.pertemuan.tutup', $pertemuanAktif) }}">
                        @csrf
                        <button class="btn btn-outline-danger" type="submit">Tutup Sesi {{ $pertemuanAktif->PERTEMUANKE }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('dosen.pertemuan.buka', $mataKuliah) }}">
                        @csrf
                        <button class="btn btn-primary" type="submit">Buka Sesi Berikutnya</button>
                    </form>
                @endif
            </div>

            @if ($pertemuanAktif)
                <div class="border-top mt-3 pt-3">
                    <h3 class="h6">Sesi {{ $pertemuanAktif->PERTEMUANKE }} sedang berlangsung</h3>
                    <p class="small text-secondary">Presensi dibuka {{ $pertemuanAktif->BUKAPRESENSI?->format('d/m/Y H:i') }} dan dijadwalkan tutup {{ $pertemuanAktif->TUTUPPRESENSI?->format('d/m/Y H:i') }}.</p>
                    <p class="small mb-2">Status presensi tercatat: {{ $pertemuanAktif->presensi->count() }} dari {{ $mataKuliah->krs->count() }} mahasiswa.</p>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Mahasiswa</th><th>NIM</th><th>Status</th><th>Tindakan / tautan</th></tr></thead>
                            <tbody>
                                @foreach ($mataKuliah->krs as $krs)
                                    @php($mahasiswa = $krs->mahasiswa)
                                    @php($rekam = $pertemuanAktif->presensi->firstWhere('IDMAHASISWA', $mahasiswa->IDMAHASISWA))
                                    <tr>
                                        <td>{{ $mahasiswa->pengguna?->NAMALENGKAP ?? $mahasiswa->NAMAMAHASISWA }}</td>
                                        <td>{{ $mahasiswa->NIM }}</td>
                                        <td>{{ $rekam?->STATUSPRESENSI ?? 'Belum presensi' }}</td>
                                        <td>
                                            @if ($rekam?->STATUSPRESENSI === 'PERLUREVIEW')
                                                <div class="d-flex flex-wrap gap-1">
                                                    <form method="POST" action="{{ route('dosen.presensi.tinjau', $rekam) }}">
                                                        @csrf
                                                        <input type="hidden" name="KEPUTUSAN" value="HADIR">
                                                        <button class="btn btn-sm btn-success" type="submit">Setujui</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('dosen.presensi.tinjau', $rekam) }}">
                                                        @csrf
                                                        <input type="hidden" name="KEPUTUSAN" value="DITOLAK">
                                                        <button class="btn btn-sm btn-outline-danger" type="submit">Tolak</button>
                                                    </form>
                                                </div>
                                            @elseif ($rekam)
                                                <span class="text-secondary">Sudah tercatat</span>
                                            @elseif (isset($tautanPresensi[$pertemuanAktif->IDPERTEMUAN][$mahasiswa->IDMAHASISWA]))
                                                <a href="{{ $tautanPresensi[$pertemuanAktif->IDPERTEMUAN][$mahasiswa->IDMAHASISWA]['url'] }}" target="_blank" rel="noopener noreferrer">Buka tautan</a>
                                            @else
                                                <span class="text-secondary">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p class="border-top mt-3 pt-3 small text-secondary mb-0">
                    {{ $mataKuliah->pertemuan->isNotEmpty() ? 'Sesi terakhir: '.$mataKuliah->pertemuan->first()->STATUSPERTEMUAN : 'Belum ada sesi pertemuan.' }}
                    Durasi sesi baru {{ $durasiPresensi }} menit.
                </p>
            @endif
        </section>
    @empty
        <section class="kartu text-secondary">Belum ada jadwal mengajar yang terdaftar pada akun ini.</section>
    @endforelse
@endsection
