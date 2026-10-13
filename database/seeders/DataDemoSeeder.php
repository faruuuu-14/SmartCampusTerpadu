<?php

namespace Database\Seeders;

use App\Layanan\TandaTanganElektronik\KunciLayanan;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Organisasi;
use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// ==========================================
// PEMBAHASAN / BAGIAN: Seeder Data Demo Sistem Smart Campus
// ==========================================
class DataDemoSeeder extends Seeder
{
    // ==========================================
    // PEMBAHASAN / BAGIAN: Eksekusi Pengisian Data Demo
    // ==========================================
    public function run(): void
    {
        $kataSandiBersama = Hash::make('SmartCampus2026!');

        // ----------------------------------------------------
        // 1. Akun BAAK (Staf Administrasi)
        // ----------------------------------------------------
        $penggunaBaak = Pengguna::updateOrCreate(['USERNAME' => 'BAAK001'], [
            'USERNAME' => 'BAAK001',
            'PASSWORD' => $kataSandiBersama,
            'NAMALENGKAP' => 'Staf Administrasi BAAK',
            'EMAIL' => 'baak@smartcampus.ac.id',
            'NOMORTELEPON' => '6281234567001',
            'ADALAHBAAK' => true,
            'STATUSAKUN' => 'AKTIF',
        ]);

        // ----------------------------------------------------
        // 2. Dosen Berjabatan (Pimpinan Merangkap Dosen)
        // ----------------------------------------------------
        $penggunaPimpinan = Pengguna::updateOrCreate(['USERNAME' => 'DOSEN001'], [
            'USERNAME' => 'DOSEN001',
            'PASSWORD' => $kataSandiBersama,
            'NAMALENGKAP' => 'Prof. Dr. Ir. Budi Santoso, M.T.',
            'EMAIL' => 'budi.santoso@smartcampus.ac.id',
            'NOMORTELEPON' => '6281234567002',
            'ADALAHBAAK' => false,
            'STATUSAKUN' => 'AKTIF',
        ]);

        $dosenPimpinan = Dosen::updateOrCreate(['NIDN' => '0011223301'], [
            'IDPENGGUNA' => $penggunaPimpinan->IDPENGGUNA,
            'NIDN' => '0011223301',
            'NIP' => '197501012000031001',
            'NAMADOSEN' => 'Prof. Dr. Ir. Budi Santoso, M.T.',
            'PROGRAMSTUDI' => 'Teknik Informatika',
            'JABATAN' => 'Dekan Fakultas Teknik',
            'LEVELPERSETUJUAN' => 3,
        ]);

        // ----------------------------------------------------
        // 2.1 Pimpinan Demo Kedua untuk Persetujuan Berantai
        // ----------------------------------------------------
        $penggunaPimpinanKedua = Pengguna::updateOrCreate(['USERNAME' => 'DOSEN004'], [
            'USERNAME' => 'DOSEN004',
            'PASSWORD' => $kataSandiBersama,
            'NAMALENGKAP' => 'Dr. Maya Prameswari, M.Si.',
            'EMAIL' => 'maya.prameswari@smartcampus.ac.id',
            'NOMORTELEPON' => '6281234567005',
            'ADALAHBAAK' => false,
            'STATUSAKUN' => 'AKTIF',
        ]);

        Dosen::updateOrCreate(['NIDN' => '0044556604'], [
            'IDPENGGUNA' => $penggunaPimpinanKedua->IDPENGGUNA,
            'NIDN' => '0044556604',
            'NIP' => '197809152005012004',
            'NAMADOSEN' => 'Dr. Maya Prameswari, M.Si.',
            'PROGRAMSTUDI' => 'Teknik Informatika',
            'JABATAN' => 'Wakil Dekan Fakultas Teknik',
            'LEVELPERSETUJUAN' => 2,
        ]);

        // ----------------------------------------------------
        // 3. Dosen Biasa 1
        // ----------------------------------------------------
        $penggunaDosen2 = Pengguna::updateOrCreate(['USERNAME' => 'DOSEN002'], [
            'USERNAME' => 'DOSEN002',
            'PASSWORD' => $kataSandiBersama,
            'NAMALENGKAP' => 'Dr. Ratna Sari, S.Kom., M.Kom.',
            'EMAIL' => 'ratna.sari@smartcampus.ac.id',
            'NOMORTELEPON' => '6281234567003',
            'ADALAHBAAK' => false,
            'STATUSAKUN' => 'AKTIF',
        ]);

        $dosen2 = Dosen::updateOrCreate(['NIDN' => '0022334402'], [
            'IDPENGGUNA' => $penggunaDosen2->IDPENGGUNA,
            'NIDN' => '0022334402',
            'NIP' => '198205122008122002',
            'NAMADOSEN' => 'Dr. Ratna Sari, S.Kom., M.Kom.',
            'PROGRAMSTUDI' => 'Teknik Informatika',
            'JABATAN' => null,
            'LEVELPERSETUJUAN' => null,
        ]);

        // ----------------------------------------------------
        // 4. Dosen Biasa 2
        // ----------------------------------------------------
        $penggunaDosen3 = Pengguna::updateOrCreate(['USERNAME' => 'DOSEN003'], [
            'USERNAME' => 'DOSEN003',
            'PASSWORD' => $kataSandiBersama,
            'NAMALENGKAP' => 'Ahmad Fauzi, S.T., M.T.',
            'EMAIL' => 'ahmad.fauzi@smartcampus.ac.id',
            'NOMORTELEPON' => '6281234567004',
            'ADALAHBAAK' => false,
            'STATUSAKUN' => 'AKTIF',
        ]);

        $dosen3 = Dosen::updateOrCreate(['NIDN' => '0033445503'], [
            'IDPENGGUNA' => $penggunaDosen3->IDPENGGUNA,
            'NIDN' => '0033445503',
            'NIP' => '198809202015041003',
            'NAMADOSEN' => 'Ahmad Fauzi, S.T., M.T.',
            'PROGRAMSTUDI' => 'Sistem Informasi',
            'JABATAN' => null,
            'LEVELPERSETUJUAN' => null,
        ]);

        // ----------------------------------------------------
        // 5. Mahasiswa 1 (Ketua BEM - Peran Ganda Mahasiswa + Ormawa)
        // ----------------------------------------------------
        $penggunaMhs1 = Pengguna::updateOrCreate(['USERNAME' => '220101001'], [
            'USERNAME' => '220101001',
            'PASSWORD' => $kataSandiBersama,
            'NAMALENGKAP' => 'Ahmad Maulana',
            'EMAIL' => 'ahmad.maulana@student.smartcampus.ac.id',
            'NOMORTELEPON' => '6281234567011',
            'ADALAHBAAK' => false,
            'STATUSAKUN' => 'AKTIF',
        ]);

        $mhs1 = Mahasiswa::updateOrCreate(['NIM' => '220101001'], [
            'IDPENGGUNA' => $penggunaMhs1->IDPENGGUNA,
            'NIM' => '220101001',
            'NAMAMAHASISWA' => 'Ahmad Maulana',
            'PROGRAMSTUDI' => 'Teknik Informatika',
            'ANGKATAN' => '2022',
            'IDDOSENPA' => $dosenPimpinan->IDDOSEN,
        ]);

        // Organisasi BEM (Ketua: Mhs 1, Pembina: Dosen Pimpinan)
        Organisasi::updateOrCreate(['IDKETUA' => $mhs1->IDMAHASISWA], [
            'NAMAORGANISASI' => 'Badan Eksekutif Mahasiswa (BEM)',
            'JENISORGANISASI' => 'BEM',
            'IDKETUA' => $mhs1->IDMAHASISWA,
            'IDPEMBINA' => $dosenPimpinan->IDDOSEN,
            'PERIODE' => '2025/2026',
            'STATUSORGANISASI' => 'AKTIF',
        ]);

        // ----------------------------------------------------
        // 6. Mahasiswa 2
        // ----------------------------------------------------
        $penggunaMhs2 = Pengguna::updateOrCreate(['USERNAME' => '220101002'], [
            'USERNAME' => '220101002',
            'PASSWORD' => $kataSandiBersama,
            'NAMALENGKAP' => 'Siti Nurhaliza',
            'EMAIL' => 'siti.nurhaliza@student.smartcampus.ac.id',
            'NOMORTELEPON' => '6281234567012',
            'ADALAHBAAK' => false,
            'STATUSAKUN' => 'AKTIF',
        ]);

        $mhs2 = Mahasiswa::updateOrCreate(['NIM' => '220101002'], [
            'IDPENGGUNA' => $penggunaMhs2->IDPENGGUNA,
            'NIM' => '220101002',
            'NAMAMAHASISWA' => 'Siti Nurhaliza',
            'PROGRAMSTUDI' => 'Teknik Informatika',
            'ANGKATAN' => '2022',
            'IDDOSENPA' => $dosenPimpinan->IDDOSEN,
        ]);

        // ----------------------------------------------------
        // 7. Mahasiswa 3
        // ----------------------------------------------------
        $penggunaMhs3 = Pengguna::updateOrCreate(['USERNAME' => '220101003'], [
            'USERNAME' => '220101003',
            'PASSWORD' => $kataSandiBersama,
            'NAMALENGKAP' => 'Rian Pratama',
            'EMAIL' => 'rian.pratama@student.smartcampus.ac.id',
            'NOMORTELEPON' => '6281234567013',
            'ADALAHBAAK' => false,
            'STATUSAKUN' => 'AKTIF',
        ]);

        $mhs3 = Mahasiswa::updateOrCreate(['NIM' => '220101003'], [
            'IDPENGGUNA' => $penggunaMhs3->IDPENGGUNA,
            'NIM' => '220101003',
            'NAMAMAHASISWA' => 'Rian Pratama',
            'PROGRAMSTUDI' => 'Teknik Informatika',
            'ANGKATAN' => '2022',
            'IDDOSENPA' => $dosen2->IDDOSEN,
        ]);

        // ----------------------------------------------------
        // 8. Mahasiswa 4
        // ----------------------------------------------------
        $penggunaMhs4 = Pengguna::updateOrCreate(['USERNAME' => '230201004'], [
            'USERNAME' => '230201004',
            'PASSWORD' => $kataSandiBersama,
            'NAMALENGKAP' => 'Dewi Lestari',
            'EMAIL' => 'dewi.lestari@student.smartcampus.ac.id',
            'NOMORTELEPON' => '6281234567014',
            'ADALAHBAAK' => false,
            'STATUSAKUN' => 'AKTIF',
        ]);

        $mhs4 = Mahasiswa::updateOrCreate(['NIM' => '230201004'], [
            'IDPENGGUNA' => $penggunaMhs4->IDPENGGUNA,
            'NIM' => '230201004',
            'NAMAMAHASISWA' => 'Dewi Lestari',
            'PROGRAMSTUDI' => 'Sistem Informasi',
            'ANGKATAN' => '2023',
            'IDDOSENPA' => $dosen3->IDDOSEN,
        ]);

        // ----------------------------------------------------
        // 9. Mahasiswa 5
        // ----------------------------------------------------
        $penggunaMhs5 = Pengguna::updateOrCreate(['USERNAME' => '230201005'], [
            'USERNAME' => '230201005',
            'PASSWORD' => $kataSandiBersama,
            'NAMALENGKAP' => 'Fajar Nugraha',
            'EMAIL' => 'fajar.nugraha@student.smartcampus.ac.id',
            'NOMORTELEPON' => '6281234567015',
            'ADALAHBAAK' => false,
            'STATUSAKUN' => 'AKTIF',
        ]);

        $mhs5 = Mahasiswa::updateOrCreate(['NIM' => '230201005'], [
            'IDPENGGUNA' => $penggunaMhs5->IDPENGGUNA,
            'NIM' => '230201005',
            'NAMAMAHASISWA' => 'Fajar Nugraha',
            'PROGRAMSTUDI' => 'Sistem Informasi',
            'ANGKATAN' => '2023',
            'IDDOSENPA' => $dosen3->IDDOSEN,
        ]);

        // ----------------------------------------------------
        // 10. Jadwal Kuliah (3 Jadwal)
        // ----------------------------------------------------
        $jadwal1 = Jadwal::updateOrCreate(['KODEMATAKULIAH' => 'IF301', 'KELAS' => 'IF-A'], [
            'IDDOSEN' => $dosenPimpinan->IDDOSEN,
            'KODEMATAKULIAH' => 'IF301',
            'NAMAMATAKULIAH' => 'Rekayasa Perangkat Lunak',
            'SKS' => 3,
            'KELAS' => 'IF-A',
            'HARI' => 'SENIN',
            'JAMMULAI' => '08:00:00',
            'JAMSELESAI' => '10:30:00',
            'RUANGAN' => 'Lab Komputer 1',
            'LINTANG' => -6.2000000,
            'BUJUR' => 106.8166667,
            'RADIUSMETER' => 100,
            'MODEKEHADIRAN' => 'WAJIBKELAS',
            'MENITPENGINGAT' => 30,
        ]);

        $jadwal2 = Jadwal::updateOrCreate(['KODEMATAKULIAH' => 'IF302', 'KELAS' => 'IF-B'], [
            'IDDOSEN' => $dosen2->IDDOSEN,
            'KODEMATAKULIAH' => 'IF302',
            'NAMAMATAKULIAH' => 'Kecerdasan Buatan',
            'SKS' => 3,
            'KELAS' => 'IF-B',
            'HARI' => 'SELASA',
            'JAMMULAI' => '10:30:00',
            'JAMSELESAI' => '13:00:00',
            'RUANGAN' => 'Ruang Teori 204',
            'LINTANG' => -6.2000000,
            'BUJUR' => 106.8166667,
            'RADIUSMETER' => 100,
            'MODEKEHADIRAN' => 'WAJIBKELAS',
            'MENITPENGINGAT' => 30,
        ]);

        $jadwal3 = Jadwal::updateOrCreate(['KODEMATAKULIAH' => 'SI201', 'KELAS' => 'SI-A'], [
            'IDDOSEN' => $dosen3->IDDOSEN,
            'KODEMATAKULIAH' => 'SI201',
            'NAMAMATAKULIAH' => 'Basis Data Terdistribusi',
            'SKS' => 3,
            'KELAS' => 'SI-A',
            'HARI' => 'RABU',
            'JAMMULAI' => '13:30:00',
            'JAMSELESAI' => '16:00:00',
            'RUANGAN' => 'Lab Sistem Informasi',
            'LINTANG' => -6.2000000,
            'BUJUR' => 106.8166667,
            'RADIUSMETER' => 100,
            'MODEKEHADIRAN' => 'BEBAS',
            'MENITPENGINGAT' => 30,
        ]);

        // ----------------------------------------------------
        // 11. Pengisian Kartu Rencana Studi (KRS)
        // ----------------------------------------------------
        // Mahasiswa 1, 2, 3 mengambil Jadwal 1 & 2
        foreach ([$mhs1, $mhs2, $mhs3] as $mhs) {
            Krs::updateOrCreate([
                'IDJADWAL' => $jadwal1->IDJADWAL,
                'IDMAHASISWA' => $mhs->IDMAHASISWA,
            ]);

            Krs::updateOrCreate([
                'IDJADWAL' => $jadwal2->IDJADWAL,
                'IDMAHASISWA' => $mhs->IDMAHASISWA,
            ]);
        }

        // Mahasiswa 4, 5 mengambil Jadwal 3
        foreach ([$mhs4, $mhs5] as $mhs) {
            Krs::updateOrCreate([
                'IDJADWAL' => $jadwal3->IDJADWAL,
                'IDMAHASISWA' => $mhs->IDMAHASISWA,
            ]);
        }

        // ==========================================
        // PEMBAHASAN / BAGIAN: Provisioning Kunci untuk Pimpinan Demo
        // ==========================================
        $kunciLayanan = app(KunciLayanan::class);
        Dosen::query()
            ->whereNotNull('JABATAN')
            ->with('pengguna')
            ->get()
            ->each(fn (Dosen $dosen) => $kunciLayanan->buatJikaBelumAda($dosen->pengguna));
    }
}
