-- Skema Smart Campus tanpa data akun atau data akademik.
-- Impor hanya ke basis data baru/kosong karena perintah DROP TABLE di bawah akan mengganti tabel bernama sama.
CREATE DATABASE IF NOT EXISTS `SMARTCAMPUS` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `SMARTCAMPUS`;

/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `administrasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `administrasi` (
  `IDADMINISTRASI` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDPEMOHON` bigint unsigned NOT NULL,
  `IDORGANISASI` bigint unsigned DEFAULT NULL,
  `JENISSURAT` enum('SURATAKTIF','TRANSKRIP','MAGANG','DISPENSASI','IZINSAKIT','PROPOSALORMAWA','LPJORMAWA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `PERIHAL` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `KEPERLUAN` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `DATAFORMULIR` json DEFAULT NULL,
  `FILELAMPIRAN` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `STATUSADMINISTRASI` enum('DIAJUKAN','VERIFIKASIBAAK','PERLUREVISI','MENUNGGUPERSETUJUAN','DITOLAK','TERBIT') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DIAJUKAN',
  `NOMORSURAT` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ISISURATKANONIK` longtext COLLATE utf8mb4_unicode_ci,
  `HASHISI` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `HASHBERKAS` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FILEAKHIR` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TANGGALTERBIT` datetime DEFAULT NULL,
  `CATATAN` text COLLATE utf8mb4_unicode_ci,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDADMINISTRASI`),
  UNIQUE KEY `administrasi_nomorsurat_unique` (`NOMORSURAT`),
  KEY `administrasi_idpemohon_foreign` (`IDPEMOHON`),
  KEY `administrasi_idorganisasi_foreign` (`IDORGANISASI`),
  CONSTRAINT `administrasi_idorganisasi_foreign` FOREIGN KEY (`IDORGANISASI`) REFERENCES `organisasi` (`IDORGANISASI`) ON DELETE SET NULL,
  CONSTRAINT `administrasi_idpemohon_foreign` FOREIGN KEY (`IDPEMOHON`) REFERENCES `pengguna` (`IDPENGGUNA`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `antrianpengingat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `antrianpengingat` (
  `IDANTRIANPENGINGAT` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDJADWAL` bigint unsigned NOT NULL,
  `IDMAHASISWA` bigint unsigned NOT NULL,
  `WAKTUKULIAH` datetime NOT NULL,
  `WAKTUKIRIM` datetime NOT NULL,
  `STATUSPENGINGAT` enum('MENUNGGU','TERKIRIM','GAGAL') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MENUNGGU',
  `TANGGALKIRIM` datetime DEFAULT NULL,
  `CATATAN` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDANTRIANPENGINGAT`),
  UNIQUE KEY `antrianpengingat_idjadwal_idmahasiswa_waktukuliah_unique` (`IDJADWAL`,`IDMAHASISWA`,`WAKTUKULIAH`),
  KEY `antrianpengingat_statuspengingat_waktukirim_index` (`STATUSPENGINGAT`,`WAKTUKIRIM`),
  KEY `antrianpengingat_idmahasiswa_foreign` (`IDMAHASISWA`),
  CONSTRAINT `antrianpengingat_idjadwal_foreign` FOREIGN KEY (`IDJADWAL`) REFERENCES `jadwal` (`IDJADWAL`) ON DELETE CASCADE,
  CONSTRAINT `antrianpengingat_idmahasiswa_foreign` FOREIGN KEY (`IDMAHASISWA`) REFERENCES `mahasiswa` (`IDMAHASISWA`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dosen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dosen` (
  `IDDOSEN` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDPENGGUNA` bigint unsigned NOT NULL,
  `NIDN` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NIP` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NAMADOSEN` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PROGRAMSTUDI` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `JABATAN` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LEVELPERSETUJUAN` tinyint DEFAULT NULL,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDDOSEN`),
  UNIQUE KEY `dosen_idpengguna_unique` (`IDPENGGUNA`),
  UNIQUE KEY `dosen_nidn_unique` (`NIDN`),
  CONSTRAINT `dosen_idpengguna_foreign` FOREIGN KEY (`IDPENGGUNA`) REFERENCES `pengguna` (`IDPENGGUNA`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jadwal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal` (
  `IDJADWAL` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDDOSEN` bigint unsigned NOT NULL,
  `KODEMATAKULIAH` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NAMAMATAKULIAH` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `SKS` int NOT NULL,
  `KELAS` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `HARI` enum('SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU') COLLATE utf8mb4_unicode_ci NOT NULL,
  `JAMMULAI` time NOT NULL,
  `JAMSELESAI` time NOT NULL,
  `RUANGAN` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `LINTANG` decimal(10,7) NOT NULL,
  `BUJUR` decimal(10,7) NOT NULL,
  `RADIUSMETER` int NOT NULL DEFAULT '100',
  `MODEKEHADIRAN` enum('WAJIBKELAS','BEBAS') COLLATE utf8mb4_unicode_ci NOT NULL,
  `MENITPENGINGAT` int NOT NULL DEFAULT '30',
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDJADWAL`),
  KEY `jadwal_iddosen_foreign` (`IDDOSEN`),
  CONSTRAINT `jadwal_iddosen_foreign` FOREIGN KEY (`IDDOSEN`) REFERENCES `dosen` (`IDDOSEN`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `krs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `krs` (
  `IDKRS` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDJADWAL` bigint unsigned NOT NULL,
  `IDMAHASISWA` bigint unsigned NOT NULL,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDKRS`),
  UNIQUE KEY `krs_idjadwal_idmahasiswa_unique` (`IDJADWAL`,`IDMAHASISWA`),
  KEY `krs_idmahasiswa_foreign` (`IDMAHASISWA`),
  CONSTRAINT `krs_idjadwal_foreign` FOREIGN KEY (`IDJADWAL`) REFERENCES `jadwal` (`IDJADWAL`) ON DELETE CASCADE,
  CONSTRAINT `krs_idmahasiswa_foreign` FOREIGN KEY (`IDMAHASISWA`) REFERENCES `mahasiswa` (`IDMAHASISWA`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mahasiswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mahasiswa` (
  `IDMAHASISWA` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDPENGGUNA` bigint unsigned NOT NULL,
  `NIM` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NAMAMAHASISWA` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PROGRAMSTUDI` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ANGKATAN` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IDDOSENPA` bigint unsigned DEFAULT NULL,
  `EMBEDDINGWAJAH` blob,
  `FOTOREFERENSI` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TANGGALKONSENBIOMETRIK` timestamp NULL DEFAULT NULL,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDMAHASISWA`),
  UNIQUE KEY `mahasiswa_idpengguna_unique` (`IDPENGGUNA`),
  UNIQUE KEY `mahasiswa_nim_unique` (`NIM`),
  KEY `mahasiswa_iddosenpa_foreign` (`IDDOSENPA`),
  CONSTRAINT `mahasiswa_iddosenpa_foreign` FOREIGN KEY (`IDDOSENPA`) REFERENCES `dosen` (`IDDOSEN`) ON DELETE SET NULL,
  CONSTRAINT `mahasiswa_idpengguna_foreign` FOREIGN KEY (`IDPENGGUNA`) REFERENCES `pengguna` (`IDPENGGUNA`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organisasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisasi` (
  `IDORGANISASI` bigint unsigned NOT NULL AUTO_INCREMENT,
  `NAMAORGANISASI` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `JENISORGANISASI` enum('BEM','HMJ','UKM') COLLATE utf8mb4_unicode_ci NOT NULL,
  `IDKETUA` bigint unsigned NOT NULL,
  `IDPEMBINA` bigint unsigned NOT NULL,
  `PERIODE` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `STATUSORGANISASI` enum('AKTIF','NONAKTIF') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AKTIF',
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDORGANISASI`),
  KEY `organisasi_idketua_foreign` (`IDKETUA`),
  KEY `organisasi_idpembina_foreign` (`IDPEMBINA`),
  CONSTRAINT `organisasi_idketua_foreign` FOREIGN KEY (`IDKETUA`) REFERENCES `mahasiswa` (`IDMAHASISWA`) ON DELETE CASCADE,
  CONSTRAINT `organisasi_idpembina_foreign` FOREIGN KEY (`IDPEMBINA`) REFERENCES `dosen` (`IDDOSEN`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pengguna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengguna` (
  `IDPENGGUNA` bigint unsigned NOT NULL AUTO_INCREMENT,
  `USERNAME` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `PASSWORD` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `NAMALENGKAP` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EMAIL` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `NOMORTELEPON` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TANGGALBINDING` timestamp NULL DEFAULT NULL,
  `ADALAHBAAK` tinyint(1) NOT NULL DEFAULT '0',
  `STATUSAKUN` enum('AKTIF','NONAKTIF') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AKTIF',
  `KUNCIPUBLIK` text COLLATE utf8mb4_unicode_ci,
  `KUNCIPRIVAT` text COLLATE utf8mb4_unicode_ci,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDPENGGUNA`),
  UNIQUE KEY `pengguna_username_unique` (`USERNAME`),
  UNIQUE KEY `pengguna_email_unique` (`EMAIL`),
  UNIQUE KEY `pengguna_nomortelepon_unique` (`NOMORTELEPON`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pengumpulan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengumpulan` (
  `IDPENGUMPULAN` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDTUGAS` bigint unsigned NOT NULL,
  `IDMAHASISWA` bigint unsigned NOT NULL,
  `STATUSPENGUMPULAN` enum('BELUM','SUDAH','TERLAMBAT','DINILAI') COLLATE utf8mb4_unicode_ci NOT NULL,
  `FILEKUMPUL` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `HASHFILE` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `WAKTUKUMPUL` datetime DEFAULT NULL,
  `FOTOVERIFIKASI` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `SKORWAJAH` decimal(5,4) DEFAULT NULL,
  `NILAI` decimal(5,2) DEFAULT NULL,
  `UMPANBALIK` text COLLATE utf8mb4_unicode_ci,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDPENGUMPULAN`),
  UNIQUE KEY `pengumpulan_idtugas_idmahasiswa_unique` (`IDTUGAS`,`IDMAHASISWA`),
  KEY `pengumpulan_idmahasiswa_foreign` (`IDMAHASISWA`),
  CONSTRAINT `pengumpulan_idmahasiswa_foreign` FOREIGN KEY (`IDMAHASISWA`) REFERENCES `mahasiswa` (`IDMAHASISWA`) ON DELETE CASCADE,
  CONSTRAINT `pengumpulan_idtugas_foreign` FOREIGN KEY (`IDTUGAS`) REFERENCES `tugas` (`IDTUGAS`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `persetujuansurat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `persetujuansurat` (
  `IDPERSETUJUAN` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDADMINISTRASI` bigint unsigned NOT NULL,
  `IDPENYETUJU` bigint unsigned NOT NULL,
  `PERANPENYETUJU` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `URUTAN` int NOT NULL,
  `STATUSPERSETUJUAN` enum('MENUNGGU','DISETUJUI','DITOLAK') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'MENUNGGU',
  `CATATAN` text COLLATE utf8mb4_unicode_ci,
  `WAKTUKEPUTUSAN` datetime DEFAULT NULL,
  `MUATANTTD` text COLLATE utf8mb4_unicode_ci,
  `TANDATANGAN` text COLLATE utf8mb4_unicode_ci,
  `ALGORITMA` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ED25519',
  `SIDIKJARIKUNCI` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TOKENVERIFIKASI` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDPERSETUJUAN`),
  UNIQUE KEY `persetujuansurat_idadministrasi_urutan_unique` (`IDADMINISTRASI`,`URUTAN`),
  UNIQUE KEY `persetujuansurat_tokenverifikasi_unique` (`TOKENVERIFIKASI`),
  KEY `persetujuansurat_idpenyetuju_foreign` (`IDPENYETUJU`),
  CONSTRAINT `persetujuansurat_idadministrasi_foreign` FOREIGN KEY (`IDADMINISTRASI`) REFERENCES `administrasi` (`IDADMINISTRASI`) ON DELETE CASCADE,
  CONSTRAINT `persetujuansurat_idpenyetuju_foreign` FOREIGN KEY (`IDPENYETUJU`) REFERENCES `pengguna` (`IDPENGGUNA`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pertemuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pertemuan` (
  `IDPERTEMUAN` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDJADWAL` bigint unsigned NOT NULL,
  `PERTEMUANKE` int NOT NULL,
  `TANGGALPERTEMUAN` date NOT NULL,
  `STATUSPERTEMUAN` enum('DIJADWALKAN','BERLANGSUNG','SELESAI','DIBATALKAN') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DIJADWALKAN',
  `MODEKEHADIRAN` enum('WAJIBKELAS','BEBAS') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `BUKAPRESENSI` datetime DEFAULT NULL,
  `TUTUPPRESENSI` datetime DEFAULT NULL,
  `TOKENSESI` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ALASANBATAL` text COLLATE utf8mb4_unicode_ci,
  `DIBATALKANVIA` enum('WEB','BOT') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `JUDULMATERI` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FILEMATERI` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDPERTEMUAN`),
  UNIQUE KEY `pertemuan_idjadwal_pertemuanke_unique` (`IDJADWAL`,`PERTEMUANKE`),
  CONSTRAINT `pertemuan_idjadwal_foreign` FOREIGN KEY (`IDJADWAL`) REFERENCES `jadwal` (`IDJADWAL`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `presensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `presensi` (
  `IDPRESENSI` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDPERTEMUAN` bigint unsigned NOT NULL,
  `IDMAHASISWA` bigint unsigned NOT NULL,
  `WAKTUKIRIM` datetime NOT NULL,
  `FILEFOTO` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `LINTANG` decimal(10,7) DEFAULT NULL,
  `BUJUR` decimal(10,7) DEFAULT NULL,
  `AKURASIGPS` decimal(8,2) DEFAULT NULL,
  `JARAKMETER` decimal(8,2) DEFAULT NULL,
  `SKORWAJAH` decimal(5,4) DEFAULT NULL,
  `SKORLINGKUNGAN` decimal(5,4) DEFAULT NULL,
  `LIVENESSLOLOS` tinyint(1) DEFAULT NULL,
  `SKORRISIKO` tinyint NOT NULL DEFAULT '0',
  `STATUSPRESENSI` enum('HADIR','PERLUREVIEW','DITOLAK','IZIN','SAKIT','ALPHA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `IDPENINJAU` bigint unsigned DEFAULT NULL,
  `SIDIKJARIPERANGKAT` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `IDPESANWA` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDPRESENSI`),
  UNIQUE KEY `presensi_idpertemuan_idmahasiswa_unique` (`IDPERTEMUAN`,`IDMAHASISWA`),
  UNIQUE KEY `presensi_idpesanwa_unique` (`IDPESANWA`),
  KEY `presensi_idmahasiswa_foreign` (`IDMAHASISWA`),
  KEY `presensi_idpeninjau_foreign` (`IDPENINJAU`),
  CONSTRAINT `presensi_idmahasiswa_foreign` FOREIGN KEY (`IDMAHASISWA`) REFERENCES `mahasiswa` (`IDMAHASISWA`) ON DELETE CASCADE,
  CONSTRAINT `presensi_idpeninjau_foreign` FOREIGN KEY (`IDPENINJAU`) REFERENCES `pengguna` (`IDPENGGUNA`) ON DELETE SET NULL,
  CONSTRAINT `presensi_idpertemuan_foreign` FOREIGN KEY (`IDPERTEMUAN`) REFERENCES `pertemuan` (`IDPERTEMUAN`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `riwayattindakansurat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `riwayattindakansurat` (
  `IDRIWAYAT` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDADMINISTRASI` bigint unsigned NOT NULL,
  `IDPELAKU` bigint unsigned NOT NULL,
  `TINDAKAN` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `CATATAN` text COLLATE utf8mb4_unicode_ci,
  `WAKTU` datetime NOT NULL,
  PRIMARY KEY (`IDRIWAYAT`),
  KEY `riwayattindakansurat_idadministrasi_waktu_index` (`IDADMINISTRASI`,`WAKTU`),
  KEY `riwayattindakansurat_idpelaku_foreign` (`IDPELAKU`),
  CONSTRAINT `riwayattindakansurat_idadministrasi_foreign` FOREIGN KEY (`IDADMINISTRASI`) REFERENCES `administrasi` (`IDADMINISTRASI`) ON DELETE CASCADE,
  CONSTRAINT `riwayattindakansurat_idpelaku_foreign` FOREIGN KEY (`IDPELAKU`) REFERENCES `pengguna` (`IDPENGGUNA`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tugas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tugas` (
  `IDTUGAS` bigint unsigned NOT NULL AUTO_INCREMENT,
  `IDJADWAL` bigint unsigned NOT NULL,
  `IDDOSEN` bigint unsigned NOT NULL,
  `JUDULTUGAS` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DESKRIPSI` text COLLATE utf8mb4_unicode_ci,
  `FILETUGAS` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TANGGALTENGGAT` datetime NOT NULL,
  `MODEPENGERJAAN` enum('BEBAS','WAJIBKELAS') COLLATE utf8mb4_unicode_ci NOT NULL,
  `DIBUATVIA` enum('WEB','BOT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `TANGGALSIARAN` datetime DEFAULT NULL,
  `TANGGALBUAT` timestamp NULL DEFAULT NULL,
  `TANGGALUBAH` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`IDTUGAS`),
  KEY `tugas_idjadwal_foreign` (`IDJADWAL`),
  KEY `tugas_iddosen_foreign` (`IDDOSEN`),
  CONSTRAINT `tugas_iddosen_foreign` FOREIGN KEY (`IDDOSEN`) REFERENCES `dosen` (`IDDOSEN`) ON DELETE CASCADE,
  CONSTRAINT `tugas_idjadwal_foreign` FOREIGN KEY (`IDJADWAL`) REFERENCES `jadwal` (`IDJADWAL`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2026_01_01_000001_create_pengguna_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2026_01_01_000002_create_dosen_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2026_01_01_000003_create_mahasiswa_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_01_01_000004_create_organisasi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_01_01_000005_create_jadwal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_01_01_000006_create_krs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_01_01_000007_create_pertemuan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_01_01_000008_create_presensi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_01_01_000009_create_tugas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_01_01_000010_create_pengumpulan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_01_01_000011_create_administrasi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_01_01_000012_create_persetujuansurat_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_10_14_013616_create_riwayattindakansurat_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_10_14_021800_create_antrianpengingat_table',1);
-- ==========================================
-- PEMBAHASAN / BAGIAN: Kolom Pendaftaran Wajah dan Peninjauan Manual Presensi
-- ==========================================
ALTER TABLE `mahasiswa`
  ADD `MODELWAJAH` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD `STATUSWAJAH` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BELUM',
  ADD `IDVERIFIKASIBIOMETRIK` bigint unsigned DEFAULT NULL,
  ADD `WAKTUTINJAUBIOMETRIK` datetime DEFAULT NULL,
  ADD CONSTRAINT `mahasiswa_idverifikasibiometrik_foreign`
    FOREIGN KEY (`IDVERIFIKASIBIOMETRIK`) REFERENCES `pengguna` (`IDPENGGUNA`) ON DELETE SET NULL;

ALTER TABLE `presensi`
  ADD `ALASANWAJAH` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

INSERT INTO `migrations` (`id`, `migration`, `batch`)
VALUES (15,'2026_10_14_114548_add_modelwajah_and_status_to_mahasiswa_table',1);
