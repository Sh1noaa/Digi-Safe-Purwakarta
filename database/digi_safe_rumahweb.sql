-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: digi_safe_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_badge` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `kriteria_skor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badges`
--

LOCK TABLES `badges` WRITE;
/*!40000 ALTER TABLE `badges` DISABLE KEYS */;
INSERT INTO `badges` VALUES (1,'Phishing Expert','Menyelesaikan Level 1 dengan skor 80+','🎣',80),(2,'Speed Runner (<10d)','Menyelesaikan tantangan minigame dengan rata-rata waktu di bawah 10 detik.','bolt',50),(3,'Tanpa Salah (Flawless)','Menyelesaikan satu sesi permainan penuh tanpa jawaban salah sedikit pun.','check',60),(4,'Phishing Hunter','Menang dalam simulasi Phishing Hunter dengan skor minimal 60 poin.','shield',60),(5,'Cyber Defender','Berhasil mempertahankan sistem di Cyber Defender dengan skor minimal 60 poin.','defender',60),(6,'Link Inspector','Menang dalam inspeksi tautan Link or Trap dengan skor minimal 60 poin.','link',60),(7,'Master Siber','Mencapai skor tinggi 100+ poin dalam satu sesi permainan.','star',100);
/*!40000 ALTER TABLE `badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum`
--

DROP TABLE IF EXISTS `forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `pertanyaan` text DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `status` enum('terbuka','terjawab','tertutup') DEFAULT 'terbuka',
  `is_hidden` tinyint(1) DEFAULT 0,
  `is_starred` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `views` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum`
--

LOCK TABLES `forum` WRITE;
/*!40000 ALTER TABLE `forum` DISABLE KEYS */;
INSERT INTO `forum` VALUES (1,10,'Cara membedakan pesan kurir paket asli dengan modus file APK?','Kemarin saya menerima pesan WhatsApp dari nomor tidak dikenal yang mengirim file LIHAT-FOTO-PAKET.apk. Bagaimana langkah aman jika terlanjur mengunduh tetapi belum menginstalnya?','Phishing','terjawab',0,1,'2026-04-29 02:44:36',42),(2,4,'Apakah aman menggunakan Wi-Fi publik di taman kota untuk transaksi mobile banking?','Sering kali saat sedang di luar rumah saya menggunakan Wi-Fi gratis untuk membuka aplikasi perbankan. Apa risiko keamanan utamanya dan bagaimana cara mengatasinya?','Privasi','terjawab',0,0,'2026-08-15 03:27:20',68),(3,5,'Akun Instagram tiba-tiba mengirim DM tautan hadiah ke semua teman','Teman-teman saya memberi tahu bahwa akun Instagram saya mengirim pesan link phishing secara otomatis. Bagaimana cara memulihkan akun dan mencabut akses aplikasi pihak ketiga?','Malware','terjawab',0,0,'2026-08-19 08:40:45',95),(4,3,'Mengenali telepon penipuan yang mengaku petugas bank meminta kode OTP','Orang tua saya hampir tertipu telepon yang menyebutkan adanya biaya admin bulanan baru dan meminta 6 digit kode SMS. Bagaimana cara mengedukasi keluarga di rumah agar tidak mudah panik?','Penipuan','terjawab',0,0,'2026-08-20 04:31:22',53),(5,6,'Rekomendasi pengelola kata sandi (Password Manager) dan aktivasi 2FA','Saya masih sering menggunakan satu password yang sama untuk banyak akun email dan media sosial. Aplikasi autentikator 2FA apa yang paling mudah digunakan untuk pemula?','Tanya Pakar','terjawab',0,0,'2026-08-20 04:37:35',77),(6,8,'F','ddd','Malware','terjawab',0,0,'2026-11-16 22:15:33',1),(7,9,'tes tes','tes tes','Penipuan','terbuka',0,0,'2026-11-16 23:15:14',1);
/*!40000 ALTER TABLE `forum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jawaban_forum`
--

DROP TABLE IF EXISTS `jawaban_forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jawaban_forum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `jawaban` text DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `forum_id` (`forum_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `jawaban_forum_ibfk_1` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`),
  CONSTRAINT `jawaban_forum_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jawaban_forum`
--

LOCK TABLES `jawaban_forum` WRITE;
/*!40000 ALTER TABLE `jawaban_forum` DISABLE KEYS */;
INSERT INTO `jawaban_forum` VALUES (1,1,10,'Jika file .apk baru terunduh namun belum diinstal, segera hapus file tersebut dari folder Download, pastikan opsi \"Install Unknown Apps\" pada browser/WhatsApp nonaktif, lalu periksa izin SMS di pengaturan ponsel Anda.',1,'2026-04-29 02:44:54'),(2,3,2,'Segera ganti password Instagram Anda, aktifkan Autentikasi Dua Faktor (2FA), lalu masuk ke menu Settings > Website Permissions > Apps and Websites untuk mencabut sesi perangkat atau aplikasi mencurigakan.',1,'2026-08-20 02:49:09'),(3,3,4,'Saya pernah mengalami hal serupa! Jangan lupa cek juga bagian \"Where You\'re Logged In\" dan keluarkan semua perangkat yang tidak dikenali.',0,'2026-08-20 04:02:10'),(4,3,5,'Terima kasih banyak sarannya! Setelah saya ganti sandi dan cabut akses sesi asing, pesan otomatisnya sudah berhenti.',0,'2026-08-20 04:15:57'),(5,2,2,'Sebaiknya hindari transaksi finansial di Wi-Fi publik tanpa enkripsi karena rawan penyadapan paket data (Man-in-the-Middle). Gunakan koneksi data seluler pribadi saat membuka mobile banking.',1,'2026-08-20 04:23:26'),(6,4,2,'Ingatkan keluarga bahwa pihak bank resmi tidak pernah meminta kode OTP, PIN, atau CVV dengan alasan apa pun. Jika menerima telepon mencurigakan, segera tutup dan hubungi Call Center resmi bank.',1,'2026-08-20 04:33:40'),(7,5,4,'Untuk autentikator 2FA, Google Authenticator atau Bitwarden sangat ringan dan mudah dipakai oleh pelajar maupun orang tua.',0,'2026-08-24 00:49:20'),(8,6,1,'ddd',1,'2026-11-16 22:15:58');
/*!40000 ALTER TABLE `jawaban_forum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jawaban_forum_votes`
--

DROP TABLE IF EXISTS `jawaban_forum_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jawaban_forum_votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jawaban_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_type` enum('up','down') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jawaban_vote_unique` (`jawaban_id`,`user_id`),
  KEY `jawaban_forum_votes_jawaban_id_index` (`jawaban_id`),
  KEY `jawaban_forum_votes_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jawaban_forum_votes`
--

LOCK TABLES `jawaban_forum_votes` WRITE;
/*!40000 ALTER TABLE `jawaban_forum_votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `jawaban_forum_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_aktivitas`
--

DROP TABLE IF EXISTS `log_aktivitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `aktivitas` varchar(255) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_aktivitas`
--

LOCK TABLES `log_aktivitas` WRITE;
/*!40000 ALTER TABLE `log_aktivitas` DISABLE KEYS */;
INSERT INTO `log_aktivitas` VALUES (1,1,'Login berhasil','2026-04-29 02:35:00'),(2,1,'Login berhasil','2026-04-29 02:43:15'),(3,1,'Membuat pertanyaan: tes','2026-04-29 02:44:36'),(4,1,'Menyelesaikan Level 1 - Skor: 130','2026-04-29 03:09:36'),(5,1,'Login berhasil','2026-04-29 03:55:44'),(6,1,'Login berhasil','2026-04-29 04:13:23'),(7,2,'Login sebagai Admin','2026-04-29 04:16:27'),(8,2,'Login berhasil','2026-04-29 04:17:17'),(9,1,'Login berhasil','2026-04-29 04:34:40'),(10,1,'Login berhasil','2026-04-29 04:37:58'),(11,1,'Login berhasil','2026-04-29 05:48:39'),(12,1,'Login berhasil','2026-04-29 05:51:46'),(13,1,'Login berhasil','2026-04-30 00:00:04'),(14,1,'Login berhasil','2026-04-30 00:32:25'),(15,1,'Mengirim testimoni: Gilaa','2026-04-30 00:51:04'),(16,2,'Login berhasil','2026-04-30 00:51:29'),(17,2,'Mengirim testimoni: Gilaa','2026-04-30 01:14:24'),(18,1,'Login berhasil','2026-04-30 01:14:41'),(19,1,'Login berhasil','2026-04-30 01:54:00'),(20,1,'Login berhasil','2026-04-30 01:56:08'),(21,1,'Mengupdate profil','2026-04-30 01:56:33'),(22,1,'Mengupdate profil','2026-04-30 01:59:20'),(23,1,'Mengupdate profil','2026-04-30 04:48:59'),(24,1,'Login berhasil','2026-05-20 01:16:32'),(25,1,'Login berhasil','2026-07-27 02:55:42'),(26,4,'Login berhasil','2026-07-27 02:57:39'),(27,4,'Login berhasil','2026-07-29 00:41:19'),(28,1,'Login berhasil','2026-07-29 00:41:30'),(29,1,'Login berhasil','2026-07-29 01:12:56'),(30,1,'Login berhasil','2026-07-29 01:16:25'),(31,1,'Login sebagai Admin','2026-07-29 01:53:27'),(32,1,'Login berhasil','2026-07-30 07:40:57'),(33,1,'Mengupdate profil','2026-07-30 07:46:54'),(34,1,'Login berhasil','2026-08-05 06:36:32'),(35,1,'Login berhasil','2026-08-10 01:46:27'),(36,1,'Login berhasil','2026-08-10 04:20:23'),(37,1,'Login berhasil','2026-08-11 00:39:18'),(38,1,'Login berhasil','2026-08-11 01:16:47'),(39,1,'Login berhasil','2026-08-11 01:18:52'),(40,1,'Login berhasil','2026-08-11 01:19:10'),(41,1,'Login berhasil','2026-08-11 01:30:56'),(42,1,'Login berhasil','2026-08-11 01:36:58'),(43,4,'Login berhasil','2026-08-11 01:37:26'),(44,4,'Login berhasil','2026-08-11 01:37:38'),(45,1,'Login berhasil','2026-08-11 01:37:48'),(46,1,'Login sebagai Admin','2026-08-11 01:38:08'),(47,1,'Login sebagai Admin','2026-08-11 01:38:17'),(48,1,'Login berhasil','2026-08-12 02:30:43'),(49,1,'Login berhasil','2026-08-12 03:51:06'),(50,1,'Mengupdate profil','2026-08-12 06:38:46'),(51,1,'Mengupdate profil','2026-08-12 06:45:29'),(52,1,'Mengupdate profil','2026-08-12 06:48:30'),(53,1,'Mengupdate profil','2026-08-12 06:48:36'),(54,1,'Mengupdate profil','2026-08-12 06:48:58'),(55,1,'Mengupdate profil','2026-08-12 06:51:17'),(56,1,'Mengupdate profil','2026-08-12 06:51:24'),(57,1,'Mengupdate profil','2026-08-12 06:51:30'),(58,1,'Mengupdate profil','2026-08-12 06:51:31'),(59,1,'Mengirim testimoni: halo halo tes','2026-08-12 07:29:40'),(60,1,'Mengirim testimoni: 32423432432','2026-08-12 07:38:41'),(61,1,'Mengirim testimoni: 32423432432','2026-08-12 07:38:50'),(62,1,'Login berhasil','2026-08-14 16:19:41'),(63,1,'Mengupdate profil','2026-08-14 18:06:52'),(64,1,'Login berhasil','2026-08-15 03:02:04'),(65,1,'Membuat pertanyaan: modul testing','2026-08-15 03:27:20'),(66,1,'Login berhasil','2026-08-19 00:47:33'),(67,4,'Login berhasil','2026-08-19 04:05:14'),(68,4,'Menyelesaikan Level 1 - Skor: 120','2026-08-19 04:58:10'),(69,4,'Menyelesaikan Level 1 - Skor: 70','2026-08-19 05:09:02'),(70,4,'Login berhasil','2026-08-19 07:28:50'),(71,1,'Login berhasil','2026-08-19 07:28:59'),(72,1,'Mengupdate profil','2026-08-19 07:29:41'),(73,1,'Mengupdate profil','2026-08-19 07:30:08'),(74,1,'Membuat pertanyaan: 1313123123','2026-08-19 08:40:45'),(75,1,'Menyelesaikan Level 1 - Skor: 100','2026-08-19 08:59:34'),(76,1,'Login berhasil','2026-08-20 02:32:50'),(77,5,'Login berhasil','2026-08-20 03:58:33'),(78,1,'Login berhasil','2026-08-20 03:58:54'),(79,1,'Mengupdate profil','2026-08-20 04:16:27'),(80,5,'Login berhasil','2026-08-20 04:28:08'),(81,1,'Login berhasil','2026-08-20 04:28:24'),(82,1,'Membuat pertanyaan: halo hal otes tes','2026-08-20 04:31:22'),(83,1,'Membuat pertanyaan: 23432423423432dasdadasasdasd','2026-08-20 04:37:35'),(84,1,'Menyelesaikan Level 1 - Skor: 20','2026-08-20 07:45:02'),(85,1,'Login berhasil','2026-08-24 00:33:18'),(86,4,'Login berhasil','2026-08-24 00:48:44'),(87,4,'Mengupdate profil','2026-08-24 00:49:09'),(88,5,'Login berhasil','2026-08-24 00:49:28'),(89,5,'Login berhasil','2026-08-24 01:06:05'),(90,5,'Mengirim testimoni: 31313','2026-08-24 01:31:40'),(91,5,'Mengirim testimoni: 31313','2026-08-24 01:31:50'),(92,5,'Mengirim testimoni: 31313','2026-08-24 01:31:54'),(93,5,'Login berhasil','2026-08-24 01:32:01'),(94,5,'Login berhasil','2026-08-24 04:24:31'),(95,1,'Login berhasil','2026-08-24 06:08:03'),(96,1,'Login berhasil','2026-08-26 01:54:25'),(97,1,'Login berhasil','2026-08-26 03:14:04'),(98,6,'Registrasi dan Login berhasil dengan Google','2026-08-31 01:41:42'),(99,7,'Registrasi dan Login berhasil dengan Google','2026-08-31 01:42:02'),(100,7,'Mengupdate profil','2026-08-31 01:43:04'),(101,1,'Login berhasil','2026-08-31 05:57:05'),(102,1,'Login berhasil','2026-08-31 06:20:03'),(103,1,'Login berhasil','2026-09-01 01:43:10'),(104,1,'Mengirim testimoni: adad','2026-09-01 02:22:54'),(105,1,'Login berhasil','2026-09-03 06:55:48'),(106,1,'Login berhasil','2026-09-04 03:07:11'),(107,1,'Login berhasil','2026-09-09 00:23:20'),(108,1,'Login berhasil','2026-09-09 00:38:51'),(109,6,'Login berhasil dengan Google','2026-09-09 01:07:49'),(110,1,'Login berhasil','2026-09-09 01:09:27'),(111,4,'Login berhasil','2026-09-09 01:47:16'),(112,1,'Login berhasil','2026-09-09 01:47:23'),(113,1,'Login berhasil','2026-09-09 07:03:38'),(114,1,'Login berhasil','2026-09-14 00:57:00'),(115,1,'Login berhasil','2026-09-14 00:57:09');
/*!40000 ALTER TABLE `log_aktivitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000001_create_cache_table',1),(2,'0001_01_01_000002_create_jobs_table',1),(3,'2026_08_19_071032_create_sessions_table',2),(4,'2026_11_17_070001_add_url_and_type_to_modul_edukasi_table',3),(5,'2026_11_17_070002_add_is_starred_to_forum_table',4),(6,'2026_11_17_080001_add_banner_to_users_table',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `minigame_levels`
--

DROP TABLE IF EXISTS `minigame_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `minigame_levels` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL,
  `nama_level` varchar(100) NOT NULL,
  `badge_label` varchar(60) NOT NULL DEFAULT 'LEVEL',
  `deskripsi` text DEFAULT NULL,
  `waktu_detik` int(11) NOT NULL DEFAULT 15,
  `poin_benar` int(11) NOT NULL DEFAULT 10,
  `penalti_salah` int(11) NOT NULL DEFAULT 5,
  `hp_awal` int(11) NOT NULL DEFAULT 3,
  `urutan` int(11) NOT NULL DEFAULT 1,
  `warna` varchar(30) NOT NULL DEFAULT 'emerald',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `minigame_levels_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `minigame_levels`
--

LOCK TABLES `minigame_levels` WRITE;
/*!40000 ALTER TABLE `minigame_levels` DISABLE KEYS */;
INSERT INTO `minigame_levels` VALUES (1,'easy','Pemula Siber','LEVEL 1 • EASY','Ciri penipuan dasar seperti hadiah undian palsu, file APK kurir, dan tautan mencurigakan.',15,10,5,3,1,'emerald',1),(2,'medium','Waspada Menengah','LEVEL 2 • MEDIUM','Samaran domain mirip instansi resmi, tagihan palsu, dan serangan malware jaringan.',13,15,5,3,2,'sky',1),(3,'hard','Detektif Siber','LEVEL 3 • HARD','Spear-phishing halus, manipulasi psikologis, dan gelombang serangan siber beruntun.',11,20,8,3,3,'amber',1),(4,'expert','Spesialis Forensik','LEVEL 4 • EXPERT','Analisis serangan Business Email Compromise (BEC), zero-day exploit, dan homograph URL.',9,25,10,2,4,'purple',1),(5,'master','Komandan Pertahanan','LEVEL 5 • MASTER','Simulasi ancaman APT (Advanced Persistent Threat) berkecepatan tinggi dengan toleransi minimal.',7,30,12,2,5,'red',1);
/*!40000 ALTER TABLE `minigame_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modul_edukasi`
--

DROP TABLE IF EXISTS `modul_edukasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modul_edukasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `konten` text NOT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `gambar_url` varchar(500) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'link',
  `url` varchar(500) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `modul_edukasi_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modul_edukasi`
--

LOCK TABLES `modul_edukasi` WRITE;
/*!40000 ALTER TABLE `modul_edukasi` DISABLE KEYS */;
INSERT INTO `modul_edukasi` VALUES (7,'Jurnal ITEE — Keamanan Digital','Kajian akademik tentang keamanan informasi dan perlindungan privasi pengguna di era transformasi digital.','https://journal.pandawan.id/itee/article/view/665',NULL,'Jurnal Akademik','link','https://journal.pandawan.id/itee/article/view/665',10,'2026-11-16 23:36:03'),(8,'Bug Bounty 2025 — Kemendikdasmen','Program penguatan talenta siber dan ketahanan infrastruktur pendidikan nasional.','https://www.kemendikdasmen.go.id/en/press-release/14317-boosting-cyber-talent-strengthening-digital-security-in-education-in-indonesia-through-bug-bounty-2025',NULL,'Talenta Siber','link','https://www.kemendikdasmen.go.id/en/press-release/14317-boosting-cyber-talent-strengthening-digital-security-in-education-in-indonesia-through-bug-bounty-2025',10,'2026-11-16 23:36:03'),(9,'MAFINDO — Masyarakat Anti Fitnah Indonesia','Komunitas pemeriksa fakta dan rujukan verifikasi berita bohong (hoaks) di Indonesia.','https://mafindo.or.id/',NULL,'Cek Fakta','link','https://mafindo.or.id/',2,'2026-11-16 23:36:03'),(10,'Literasi Digital Indonesia','Portal resmi literasi digital nasional berbasis 4 pilar: Cakap, Aman, Budaya, dan Etis.','https://literasidigital.id/',NULL,'Portal Nasional','link','https://literasidigital.id/',2,'2026-11-16 23:36:03'),(11,'Bijak Bermedia Sosial — DJKN Kemenkeu','Panduan bijak menggunakan media sosial dan menjaga rekam jejak digital dari Kementerian Keuangan.','https://www.djkn.kemenkeu.go.id/artikel/baca/15818/Bijak-Bermedia-Sosial.html',NULL,'Etika Siber','link','https://www.djkn.kemenkeu.go.id/artikel/baca/15818/Bijak-Bermedia-Sosial.html',2,'2026-11-16 23:36:03'),(12,'Panduan Praktis Mengenali Modus File APK & Phishing WhatsApp','Penipuan bermodus file berekstensi .APK (seperti Surat Undangan Nikah, Resi Kurir Paket, atau Tagihan Listrik) bekerja dengan cara meminta izin baca SMS untuk mencuri kode OTP perbankan Anda.\n\nLangkah Pencegahan Utama:\n1. Jangan pernah membuka atau menginstal file berakhiran .apk yang dikirim melalui WhatsApp atau Telegram.\n2. Matikan izin \'Install Unknown Apps\' pada pengaturan browser dan aplikasi pesan Anda.\n3. Apabila terlanjur menginstal, segera matikan koneksi internet (aktifkan Mode Pesawat), hapus aplikasi mencurigakan tersebut, lalu hubungi pihak bank untuk memblokir sementara layanan mobile banking.','',NULL,'Panduan Publik','teks',NULL,10,'2026-11-16 23:36:03'),(14,'ddddada','dddddd','',NULL,'Panduan Publik','teks',NULL,13,'2026-11-17 04:16:49');
/*!40000 ALTER TABLE `modul_edukasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progres_baca`
--

DROP TABLE IF EXISTS `progres_baca`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progres_baca` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `modul_id` int(11) DEFAULT NULL,
  `selesai` tinyint(1) DEFAULT 0,
  `persentase` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `modul_id` (`modul_id`),
  CONSTRAINT `progres_baca_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `progres_baca_ibfk_2` FOREIGN KEY (`modul_id`) REFERENCES `modul_edukasi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progres_baca`
--

LOCK TABLES `progres_baca` WRITE;
/*!40000 ALTER TABLE `progres_baca` DISABLE KEYS */;
/*!40000 ALTER TABLE `progres_baca` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('5eF5ll0KySdYKFY60f4XnWjZA3kIcrRX5bYUzBAb',NULL,'127.0.0.1','Symfony','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY1FWbG15bDJGRlNUSFNiZ2tmNWxYdGlRYWxIaUhCU2MwR2wxNndKYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly9sb2NhbGhvc3QvbWluaWdhbWUiO3M6NToicm91dGUiO3M6MTQ6Im1pbmlnYW1lLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794918060),('7crBR3zZyy0IRwzYugZBd4PFWr6jlYekwZ9XumkB',13,'127.0.0.1','Symfony','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUjU3aGpWNVhPTEd2NTFiejNaY1k5STMwYkVrYkV1cE9QOVpZbk5oVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHA6Ly9sb2NhbGhvc3QvZm9ydW0iO3M6NToicm91dGUiO3M6MTE6ImZvcnVtLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTM7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjE1ODNiOGMxMWEwNjFjMGQxMjgxYzFiNzEwODMxZmI3MzNhOTc1ZGMyNzJhMmM1MjI1Yzg4YmY1Y2FiNDE1ZGYiO30=',1794912054),('7S2rjdcbB2ZEwlaNgg4vzIead8zwreBAoqQdBmI1',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS3BRSTV4RVVPVzRvOUdUaUNhQWx2WWZDdU1naEFabURkdlp3QzR0TyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo3OiJiZXJhbmRhIjt9fQ==',1794918298),('aeXkJYR9G9KW9VJXJ2iHCetWhF8REnStVBjAahTW',13,'127.0.0.1','Symfony','YTo3OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMztzOjY6Il90b2tlbiI7czo0MDoidU54U01RaGJvSXhCSkNLNHJ3WWFnaXd0RTB3VXlPY3RFMG9PUmVtViI7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjE1ODNiOGMxMWEwNjFjMGQxMjgxYzFiNzEwODMxZmI3MzNhOTc1ZGMyNzJhMmM1MjI1Yzg4YmY1Y2FiNDE1ZGYiO3M6NjoibG9jYWxlIjtzOjI6ImVuIjtzOjU6InRoZW1lIjtzOjQ6ImRhcmsiO3M6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjE2OiJodHRwOi8vbG9jYWxob3N0IjtzOjU6InJvdXRlIjtzOjc6ImJlcmFuZGEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1794916465),('BgDA4yjaXQREQuoF8jyFo5bFVDHInbrAHYItiFyv',13,'127.0.0.1','Symfony','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiajFST3hqSWtqNmN0anVLWmV3Rlo5dmp4b2lqU054NXRoWmFRQjhPdiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHA6Ly9sb2NhbGhvc3QvYWRtaW4iO3M6NToicm91dGUiO3M6MTU6ImFkbWluLmRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEzO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIxNTgzYjhjMTFhMDYxYzBkMTI4MWMxYjcxMDgzMWZiNzMzYTk3NWRjMjcyYTJjNTIyNWM4OGJmNWNhYjQxNWRmIjt9',1794912033),('fGgZW9gg3U8bH7Le7Sm3l9KJzU8yzGV0IsAxg6Dv',13,'127.0.0.1','Symfony','YTo3OntzOjY6Il90b2tlbiI7czo0MDoiN2lVeDU2RXVydThTY0kxZXlUOHJNTHBLdnR4VGF5cUxyTUpuY3JEYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTY6Imh0dHA6Ly9sb2NhbGhvc3QiO3M6NToicm91dGUiO3M6NzoiYmVyYW5kYSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEzO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIxNTgzYjhjMTFhMDYxYzBkMTI4MWMxYjcxMDgzMWZiNzMzYTk3NWRjMjcyYTJjNTIyNWM4OGJmNWNhYjQxNWRmIjtzOjY6ImxvY2FsZSI7czoyOiJpZCI7czo1OiJ0aGVtZSI7czo1OiJsaWdodCI7fQ==',1794913656),('KHrvD02HrSLsil8Uw7AF0nqnLFtamE28INaEWjNg',NULL,'127.0.0.1','Symfony','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUh2dFJ4YTIxTFUyeWZvSTg5aGthVHJLampGcU5BeU9VTXRzZWhUeiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly9sb2NhbGhvc3QvbWluaWdhbWUiO3M6NToicm91dGUiO3M6MTQ6Im1pbmlnYW1lLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794917257),('LvWsGv5diMqtRVn44WmGejiZnQAhXBPdsY7tTtvR',13,'127.0.0.1','Symfony','YTo1OntzOjY6Il90b2tlbiI7czo0MDoielpuT1Y2YmMyd1hFNjJOandSZlJMdlpQNENieURoNnY3QWtNWGVKaSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly9sb2NhbGhvc3QvZm9ydW0vbGl2ZS1kYXRhIjtzOjU6InJvdXRlIjtzOjE1OiJmb3J1bS5saXZlLWRhdGEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMztzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMTU4M2I4YzExYTA2MWMwZDEyODFjMWI3MTA4MzFmYjczM2E5NzVkYzI3MmEyYzUyMjVjODhiZjVjYWI0MTVkZiI7fQ==',1794912068),('M3vVI3PvgI7pRVuxheM8AM2lqayHBIi8hljesRIf',1,'127.0.0.1','Symfony','YTo3OntzOjY6Il90b2tlbiI7czo0MDoiSjJ1QU5TZGdCUDVXOU9DcWRvdzAyUWF5SDk4QXp4bktxTjhJNlYyWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3QvcHJvZmlsZS8xMyI7czo1OiJyb3V0ZSI7czoxMjoicHJvZmlsZS5zaG93Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiNTkyZGE2ZTZhMzc2YjMzNjdmMDJkMjAwNTg1Y2JjZTBiOGZlMDI3YjA0ZTY5YmUyNDE5YmIxYTkyY2ViMThjMCI7czo2OiJsb2NhbGUiO3M6MjoiaWQiO3M6NToidGhlbWUiO3M6NToibGlnaHQiO30=',1794913644),('O1s5QSFQtiZ4uZRcXod4s48Owfkchwx8wMMiwORI',13,'127.0.0.1','Symfony','YTo3OntzOjY6Il90b2tlbiI7czo0MDoicXdoTXRKRFQ1dmN1R3NpUWJjSTVuWVZhbUlZZ0dvQmhLOHBuNTRsayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTY6Imh0dHA6Ly9sb2NhbGhvc3QiO3M6NToicm91dGUiO3M6NzoiYmVyYW5kYSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEzO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIxNTgzYjhjMTFhMDYxYzBkMTI4MWMxYjcxMDgzMWZiNzMzYTk3NWRjMjcyYTJjNTIyNWM4OGJmNWNhYjQxNWRmIjtzOjY6ImxvY2FsZSI7czoyOiJpZCI7czo1OiJ0aGVtZSI7czo1OiJsaWdodCI7fQ==',1794913644),('PZ2XsyNyDUImYf1s0r2fT2T9KKw2caK4xfPm9lzM',13,'127.0.0.1','Symfony','YTo4OntzOjY6Il90b2tlbiI7czo0MDoiOGw4elNDb1UwUGc0NTlTQndQdWhXTWFqY045T2wyc1JNZEV3a2V6YSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly9sb2NhbGhvc3QvbWluaWdhbWUiO3M6NToicm91dGUiO3M6MTQ6Im1pbmlnYW1lLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNToiaHR0cDovL2xvY2FsaG9zdC9taW5pZ2FtZS9saW5rLXRyYXAiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMztzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiMTU4M2I4YzExYTA2MWMwZDEyODFjMWI3MTA4MzFmYjczM2E5NzVkYzI3MmEyYzUyMjVjODhiZjVjYWI0MTVkZiI7czo2OiJsb2NhbGUiO3M6MjoiZW4iO3M6NToidGhlbWUiO3M6NToibGlnaHQiO30=',1794917258),('ZmOGXb4Gct94ZZntZFu444eRWtFpQcTHUOToT84g',NULL,'127.0.0.1','Symfony','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ0t2VTNabzFhVG9LT1BQOHhQZHJNY3dGWGtFS1VFR0d6aDBqZ2hkUCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHA6Ly9sb2NhbGhvc3QvZm9ydW0iO3M6NToicm91dGUiO3M6MTE6ImZvcnVtLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1794912075);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skor_game`
--

DROP TABLE IF EXISTS `skor_game`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skor_game` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `game_type` varchar(50) DEFAULT NULL,
  `skor` int(11) DEFAULT 0,
  `skor_tertinggi` int(11) DEFAULT 0,
  `played_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `skor_game_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skor_game`
--

LOCK TABLES `skor_game` WRITE;
/*!40000 ALTER TABLE `skor_game` DISABLE KEYS */;
INSERT INTO `skor_game` VALUES (1,1,'level1',130,130,'2026-04-29 03:09:36'),(2,1,'level3',150,150,'2026-04-30 00:33:41'),(3,4,'level1',120,120,'2026-08-19 04:58:10'),(4,4,'level1',70,120,'2026-08-19 05:09:02'),(5,1,'level1',100,130,'2026-08-19 08:59:34'),(6,1,'level1',20,130,'2026-08-20 07:45:02'),(7,8,'link-trap',100,100,'2026-11-17 00:27:45');
/*!40000 ALTER TABLE `skor_game` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `soal_minigame`
--

DROP TABLE IF EXISTS `soal_minigame`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `soal_minigame` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pertanyaan` text NOT NULL,
  `pilihan_json` text DEFAULT NULL,
  `jawaban_benar` varchar(255) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `tingkat` varchar(30) DEFAULT 'easy',
  `game_level` varchar(50) DEFAULT 'phishing',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `soal_minigame`
--

LOCK TABLES `soal_minigame` WRITE;
/*!40000 ALTER TABLE `soal_minigame` DISABLE KEYS */;
INSERT INTO `soal_minigame` VALUES (1,'Apa ciri email phishing?','[\"Alamat pengirim resmi\",\"Meminta data pribadi\",\"Dari teman\",\"Ada logo perusahaan\"]','Meminta data pribadi','keamanan','mudah','level1'),(2,'Password yang paling aman adalah...','[\"12345678\",\"password123\",\"P@ssw0rd!2024\",\"namakamu\"]','P@ssw0rd!2024','password','mudah','level1'),(3,'Jika dapat info giveaway hadiah besar, yang harus dilakukan...','[\"Langsung klik\",\"Share ke teman\",\"Cek kebenaran dulu\",\"Isi data diri\"]','Cek kebenaran dulu','hoaks','mudah','level1'),(4,'Apa itu ransomware?','[\"Antivirus\",\"Malware enkripsi data\",\"WiFi cepat\",\"Password manager\"]','Malware enkripsi data','keamanan','sedang','level1'),(5,'Apa fungsi VPN?','[\"Mempercepat WiFi\",\"Mengenkripsi koneksi\",\"Virus Protection\",\"Editor video\"]','Mengenkripsi koneksi','keamanan','sedang','level1'),(6,'Apa itu 2FA?','[\"Password 2x lipat\",\"Verifikasi 2 langkah\",\"2 Facebook Account\",\"Dua Aplikasi\"]','Verifikasi 2 langkah','keamanan','sulit','level1'),(7,'Anda menerima pesan WhatsApp dari nomor tidak dikenal berisi file \"LIHAT-RESI-PAKET.apk\". Apa tindakan paling tepat?','{\"A\":\"Mengunduh dan menginstal file untuk mengecek paket\",\"B\":\"Menghapus pesan dan memblokir pengirim karena file .apk berpotensi mencuri OTP\",\"C\":\"Meneruskan pesan tersebut ke grup keluarga\",\"D\":\"Membalas pesan dengan mengirimkan nomor rekening\"}','B','Phishing Hunter','easy','phishing'),(8,'Email dengan subjek \"AKUN ANDA AKAN DIBLOKIR DALAM 10 MENIT\" berasal dari alamat support@bca-verifikasi-data.xyz. Indikator utama bahwa ini phishing adalah...','{\"A\":\"Menggunakan domain tidak resmi (.xyz) dan menciptakan kepanikan mendesak\",\"B\":\"Menggunakan bahasa Indonesia yang baku\",\"C\":\"Memiliki subjek huruf kapital\",\"D\":\"Dikirim pada jam kerja\"}','A','Phishing Hunter','medium','phishing'),(9,'Serangan spear-phishing yang menyamar sebagai surat resmi kepala instansi dengan lampiran dokumen ber-macro berbahaya paling efektif dicegah dengan...','{\"A\":\"Langsung mengaktifkan macro agar dokumen terbaca\",\"B\":\"Verifikasi silang melalui saluran komunikasi resmi dan menonaktifkan auto-run macro\",\"C\":\"Mengganti wallpaper komputer\",\"D\":\"Mematikan layar monitor sementara\"}','B','Phishing Hunter','hard','phishing'),(10,'Komputer mendadak menampilkan layar terkunci dan meminta tebusan kripto untuk membuka enkripsi file. Serangan ini disebut...','{\"A\":\"Adware\",\"B\":\"Ransomware\",\"C\":\"Defacement\",\"D\":\"Keylogger\"}','B','Cyber Defender','easy','cyber-defender'),(11,'Saat menggunakan Wi-Fi publik di kafe untuk bekerja, pertahanan terbaik agar lalu lintas data tidak disadap (Man-in-the-Middle) adalah...','{\"A\":\"Menggunakan mode penyamaran (Incognito) saja\",\"B\":\"Mengaktifkan koneksi VPN terenkripsi atau menggunakan hotspot pribadi\",\"C\":\"Menurunkan kecerahan layar laptop\",\"D\":\"Menghapus riwayat pencarian browser\"}','B','Cyber Defender','medium','cyber-defender'),(12,'Langkah mitigasi pertama ketika mendeteksi satu perangkat di jaringan kantor terinfeksi worm yang menyebar otomatis adalah...','{\"A\":\"Mengisolasi perangkat dari jaringan (cabut LAN \\/ matikan Wi-Fi) segera\",\"B\":\"Membiarkan perangkat tetap terhubung sambil mengunduh game\",\"C\":\"Membagikan folder kerja ke komputer lain lewat jaringan lokal\",\"D\":\"Menunggu hingga jam pulang kantor\"}','A','Cyber Defender','hard','cyber-defender'),(13,'Manakah di antara alamat URL berikut yang merupakan situs resmi instansi pemerintah Indonesia?','{\"A\":\"https:\\/\\/bantuan-kemensos.blogspot.com\",\"B\":\"https:\\/\\/kemendikdasmen.go.id\",\"C\":\"http:\\/\\/cek-bansos-resmi.my.id\",\"D\":\"https:\\/\\/pendaftaran-cpns.net\"}','B','Link or Trap','easy','link-trap'),(14,'Perhatikan tautan berikut: \"https://klikbca.com.login-session-id.top/auth\". Mengapa tautan tersebut berbahaya?','{\"A\":\"Karena menggunakan protokol HTTPS\",\"B\":\"Domain utamanya sebenarnya adalah login-session-id.top, bukan klikbca.com (Subdomain Trick)\",\"C\":\"Karena terlalu pendek\",\"D\":\"Karena tidak memiliki gambar logo\"}','B','Link or Trap','medium','link-trap'),(15,'Teknik penipuan domain yang mengganti huruf \"l\" kecil dengan angka \"1\" atau huruf Cyrillic serupa seperti \"paypa1.com\" disebut...','{\"A\":\"SQL Injection\",\"B\":\"Typosquatting \\/ Homograph Attack\",\"C\":\"Cross-Site Scripting\",\"D\":\"Brute Force\"}','B','Link or Trap','hard','link-trap'),(16,'Email dari \"ceo-office@perusahaan-anda.co.id\" tetapi header Reply-To mengarah ke \"transfer-cepat@protonmail.com\" meminta pencairan dana darurat. Langkah paling tepat?','{\"A\":\"Segera transfer agar tidak ditegur pimpinan\",\"B\":\"Verifikasi langsung melalui kanal komunikasi internal resmi (telepon kantor) dan tolak balasan ke email eksternal\",\"C\":\"Membalas email dan mengirim foto token perbankan\",\"D\":\"Mengunduh lampiran invoice.iso yang disertakan\"}','B','Phishing Hunter','expert','phishing'),(17,'Terdapat percobaan koneksi keluar mencurigakan dari server internal ke IP asing pada port 4444 (Reverse Shell). Pertahanan apa yang wajib diaktifkan?','{\"A\":\"Membiarkan port tetap terbuka\",\"B\":\"Menonaktifkan log audit server\",\"C\":\"Blokir trafik keluar dengan Firewall (Egress Filtering) & isolasi host\",\"D\":\"Mengabaikan peringatan IDS\"}','C','Cyber Defender','expert','cyber-defender'),(18,'Perhatikan tautan berikut: \"https://sso.purwakartakab.go.id@login-portal-resmi.top/auth\". Bagaimana status keamanan URL tersebut?','{\"A\":\"Aman karena diawali sso.purwakartakab.go.id\",\"B\":\"Trap! Karakter @ membuat browser mengabaikan teks sebelumnya dan mengarahkan ke domain palsu login-portal-resmi.top\",\"C\":\"Aman karena sudah menggunakan protokol HTTPS\",\"D\":\"Merupakan subdomain resmi pemerintah daerah\"}','B','Link or Trap','expert','link-trap'),(19,'Anda menerima undangan rapat kalender otomatis (.ics) yang menyisipkan tautan login OAuth aplikasi pihak ketiga yang meminta izin \"Baca & Kirim Email\". Apa analisis Anda?','{\"A\":\"Serangan Consent Phishing (OAuth Abuse) untuk mengambil alih akses email tanpa mencuri password\",\"B\":\"Fitur standar pembaruan kalender resmi\",\"C\":\"Aman selama tidak meminta nomor kartu kredit\",\"D\":\"Wajib disetujui agar jadwal rapat tersinkronisasi\"}','A','Phishing Hunter','master','phishing'),(20,'Serangan Ransomware Zero-Day mulai mengenkripsi folder bersama melalui protokol SMB di jaringan lokal. Langkah pertahanan darurat paling efektif?','{\"A\":\"Membayar tebusan kripto secepatnya\",\"B\":\"Mematikan monitor komputer saja\",\"C\":\"Putuskan koneksi jaringan (Isolasi Segmen Firewall) & hentikan layanan SMB segera\",\"D\":\"Menghapus seluruh backup offline\"}','C','Cyber Defender','master','cyber-defender'),(21,'Inspeksi URL: \"https://xn--bca-c1a.co.id/ibir/login\" (tampil di beberapa browser sebagai bca dengan titik bawah). Apakah tautan ini aman?','{\"A\":\"Aman karena berakhiran .co.id\",\"B\":\"Trap! Ini adalah serangan IDN Homograph menggunakan Punycode (xn--) untuk meniru domain perbankan\",\"C\":\"Aman karena merupakan server cadangan bank\",\"D\":\"Aman selama diakses melalui jaringan Wi-Fi rumah\"}','B','Link or Trap','master','link-trap');
/*!40000 ALTER TABLE `soal_minigame` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimoni`
--

DROP TABLE IF EXISTS `testimoni`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimoni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `cerita` text DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `mood` varchar(10) DEFAULT NULL,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `status_approval` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `testimoni_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimoni`
--

LOCK TABLES `testimoni` WRITE;
/*!40000 ALTER TABLE `testimoni` DISABLE KEYS */;
INSERT INTO `testimoni` VALUES (2,1,'Sangat Membantu Mengenali Link Phishing','Simulasi Phishing Hunter di Digi-Safe membuat saya jauh lebih teliti memeriksa alamat domain sebelum klik tautan di grup WhatsApp warga.',5,'5',0,'approved','2026-04-30 01:14:24'),(6,5,'Edukasi yang Mudah Dipahami Orang Tua','Setelah membaca modul dan berdiskusi di forum, keluarga saya di rumah tidak lagi mudah panik saat menerima telepon yang mengaku petugas bank.',5,'5',0,'approved','2026-08-24 01:31:40'),(7,4,'Berhasil Memulihkan Akun Media Sosial','Panduan dari admin di Forum Komunitas sangat jelas. Saya berhasil mengamankan kembali akun yang sempat terhubung ke web phishing.',5,'5',1,'approved','2026-08-24 01:31:50'),(8,6,'Minigame Seru & Bikin Sadar Siber','Main Link or Trap bareng teman-teman sekolah jadi cara seru belajar bedain domain resmi .go.id dan domain jebakan.',5,'5',0,'approved','2026-08-24 01:31:54'),(9,3,'Ruang Tanya Jawab yang Aman','Fitur anonim dan respons cepat dari relawan membuat saya nyaman menceritakan kronologi penipuan digital tanpa rasa malu.',4,'4',1,'approved','2026-09-01 02:22:54'),(10,9,'testes','testes',4,'4',0,'approved','2026-11-16 23:19:41');
/*!40000 ALTER TABLE `testimoni` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimoni_reactions`
--

DROP TABLE IF EXISTS `testimoni_reactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimoni_reactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `testimoni_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reaction_type` enum('like','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reaction` (`testimoni_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `testimoni_reactions_ibfk_1` FOREIGN KEY (`testimoni_id`) REFERENCES `testimoni` (`id`) ON DELETE CASCADE,
  CONSTRAINT `testimoni_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimoni_reactions`
--

LOCK TABLES `testimoni_reactions` WRITE;
/*!40000 ALTER TABLE `testimoni_reactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `testimoni_reactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_badges`
--

DROP TABLE IF EXISTS `user_badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `badge_id` int(11) DEFAULT NULL,
  `diperoleh_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `badge_id` (`badge_id`),
  CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_badges`
--

LOCK TABLES `user_badges` WRITE;
/*!40000 ALTER TABLE `user_badges` DISABLE KEYS */;
INSERT INTO `user_badges` VALUES (1,1,1,'2026-04-29 03:09:36'),(2,4,1,'2026-08-19 04:58:10'),(3,4,1,'2026-08-19 05:09:02'),(4,1,1,'2026-08-19 08:59:34'),(5,1,1,'2026-08-20 07:45:02');
/*!40000 ALTER TABLE `user_badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('owner','admin','user') DEFAULT 'user',
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `badge_json` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `oauth_provider` varchar(50) DEFAULT 'google',
  `oauth_uid` varchar(255) DEFAULT NULL,
  `locale` varchar(10) NOT NULL DEFAULT 'id',
  `theme` varchar(10) NOT NULL DEFAULT 'light',
  `privacy_settings` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `oauth_uid` (`oauth_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Razief','raziefafkarruslii@gmail.com','$2y$12$RH5K9WC9VFGm9TLetSUZD.RDsFf.8eqlO1nERNmiWrLHRGEsPkCY2','admin','Razief Afkar Rusli','tes tes','assets/uploads/avatar_1_1787199387.jpeg',NULL,NULL,'2026-04-29 02:34:53',NULL,NULL,'google',NULL,'id','light',NULL),(2,'admin','admin@digisafe.id','$2y$12$AfaNpJI3Ud813b46Uq8lg.5InKS1VPMj/z7yq1YptjsgmdRdc5.MO','admin','Administrator',NULL,NULL,NULL,NULL,'2026-04-29 04:16:11',NULL,NULL,'google',NULL,'id','light',NULL),(3,'testing','jujurbanyakkepang@gmail.com','$2y$10$GRubiz88sjsilq0w3o/r3OilggV1l1k/ZyX/PvDSBaW3McFx/Z3gm','user','akuntesting',NULL,NULL,NULL,NULL,'2026-04-30 01:55:45',NULL,NULL,'google',NULL,'id','light',NULL),(4,'testing123','testes@gmail.com','$2y$10$97ecfoDsXtgWOi9giaWEQON0NxUqY0hPl4W8VSqKn1LDlcwhk6PcO','user','testes1212','hhhhh','assets/uploads/avatar_4_1787532549.png',NULL,NULL,'2026-07-27 02:57:31',NULL,NULL,'google',NULL,'id','light',NULL),(5,'Fairuz','fairuzradhwa@gmail.com','$2y$10$0LGg3Senx09nWQYXwOtMduCxe7L.E0MbtrV9mOHwl2Hb2HmbQAEd6','user','Fairuz.R.Z',NULL,NULL,NULL,NULL,'2026-08-20 03:58:27',NULL,NULL,'google',NULL,'id','light',NULL),(6,'raziefrpl','raziefrpl@gmail.com','$2y$10$VIt6q5ZIkBOf6G3yaUIrteyYz8n6OlDULeoymPYxuff3Y2t7Gr39S','user','Razief RPL',NULL,'https://lh3.googleusercontent.com/a/ACg8ocJ1WySMoYnEOEOUx5fcvR3rOykmOvs8XJkFRl4FX4KEzvHEyw=s96-c',NULL,NULL,'2026-08-31 01:41:42','2TTSQ','2026-09-09 09:03:09','google','104300402400830202938','id','light',NULL),(7,'aokaroti48','aokaroti48@gmail.com','','user','roti aoka','','assets/uploads/avatar_7_1788140584.png',NULL,NULL,'2026-08-31 01:42:02',NULL,NULL,'google','101344524501404074519','id','light',NULL),(8,'Raziefff','fadlanhidayat@gmail.com','$2y$12$mBO2SZyRjFifURb/aejCgOrO353DSCrP2jACPi0Jc0A/AcF5BAzNW','user','Testing 123',NULL,'images/avatars/avatar_8_1794899841.jpeg','images/banners/banner_8_1794901709.png',NULL,'2026-11-17 04:23:48',NULL,NULL,'google',NULL,'id','light',NULL),(9,'fff','flyxmusaigen@gmail.com','$2y$12$dfgh304/YLcM8tv4r1i2KujUGi03ALiif6m2lI.VOh/0u5bPLMyyO','user','Raziefaaa',NULL,NULL,NULL,NULL,'2026-11-17 06:05:34',NULL,NULL,'google',NULL,'id','light',NULL),(10,'Razief Afkar Rusli','owner@digisafe.id','$2y$12$jcrl.nXBpJkUadrxefLXIer0lOAWYdiN9qzRuy8eHpPIzne/1dd3W','owner','Razief Afkar Rusli',NULL,NULL,NULL,NULL,'2026-11-17 06:36:03',NULL,NULL,'google',NULL,'id','light',NULL),(11,'fadlan1234','fadlanrpl@gmail.com','$2y$12$DtWoWMaIQKlchDnNI9N7Pu8aPcRYY21ZIraj3h8y2u5QpH2xSOcIi','user','Fadlan',NULL,NULL,NULL,NULL,'2026-11-17 09:07:34',NULL,NULL,'google',NULL,'id','light',NULL),(12,'fadlan1234ss','afafafd@gmail.com','$2y$12$nd0UtxwcHurjbb3u.S4kkOzSFJICvnCwZRjarF0LJKsWpRRUnm6me','user','fadafff',NULL,NULL,NULL,NULL,'2026-11-17 10:28:36',NULL,NULL,'google',NULL,'id','light',NULL),(13,'owner_akses','owner.akses@digisafe.id','$2y$12$zVsXAu14kLmD.6.10Lwpme3oYUukX23LVLiDs9SALh7tG4f/HSzta','owner','Owner Digi-Safe','Platform Owner & Administrator Utama Digi-Safe Purwakarta.',NULL,NULL,NULL,'2026-11-17 03:37:07',NULL,NULL,'google',NULL,'id','light',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'digi_safe_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-17 19:28:34
