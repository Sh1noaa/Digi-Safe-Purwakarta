-- Digi-Safe Purwakarta - Official UKK Database Dump (Structure + Foreign Keys + Realistic Dummy Data)
-- Generated: 2026-11-23 | Engine: InnoDB | Charset: utf8mb4

SET FOREIGN_KEY_CHECKS=0;

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: digi_safe_db
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
-- Table structure for table `account_otps`
--

DROP TABLE IF EXISTS `account_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_otps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(120) NOT NULL,
  `purpose` varchar(32) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `consumed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_otps_email_purpose_consumed_at_index` (`email`,`purpose`,`consumed_at`),
  KEY `account_otps_email_index` (`email`),
  KEY `account_otps_purpose_index` (`purpose`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned NOT NULL,
  `receiver_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_messages_sender_id_receiver_id_index` (`sender_id`,`receiver_id`),
  KEY `chat_messages_receiver_id_is_read_index` (`receiver_id`,`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `daily_streak_questions`
--

DROP TABLE IF EXISTS `daily_streak_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_streak_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_key` varchar(64) NOT NULL,
  `category_id` varchar(100) NOT NULL,
  `category_en` varchar(100) DEFAULT NULL,
  `difficulty_id` varchar(40) NOT NULL DEFAULT 'Lanjutan',
  `difficulty_en` varchar(40) NOT NULL DEFAULT 'Advanced',
  `prompt_id` text NOT NULL,
  `prompt_en` text DEFAULT NULL,
  `options_id_json` text NOT NULL,
  `options_en_json` text DEFAULT NULL,
  `answer` varchar(2) NOT NULL,
  `explanation_id` text NOT NULL,
  `explanation_en` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_streak_questions_question_key_unique` (`question_key`),
  KEY `daily_streak_questions_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edukasi_requests`
--

DROP TABLE IF EXISTS `edukasi_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edukasi_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `tema` varchar(180) NOT NULL,
  `kategori` varchar(80) NOT NULL DEFAULT 'Keamanan Akun',
  `preferred_type` varchar(20) NOT NULL DEFAULT 'teks',
  `deskripsi` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `user_notified_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `edukasi_requests_user_id_index` (`user_id`),
  KEY `edukasi_requests_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `friendships`
--

DROP TABLE IF EXISTS `friendships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friendships` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `requester_id` bigint(20) unsigned NOT NULL,
  `addressee_id` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `friendships_requester_id_addressee_id_unique` (`requester_id`,`addressee_id`),
  KEY `friendships_addressee_id_status_index` (`addressee_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `security_activity_logs`
--

DROP TABLE IF EXISTS `security_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `event` varchar(48) NOT NULL,
  `ip_address` varchar(64) DEFAULT NULL,
  `device_label` varchar(120) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `security_activity_logs_user_id_index` (`user_id`),
  KEY `security_activity_logs_event_index` (`event`),
  KEY `security_activity_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `benar` int(10) unsigned NOT NULL DEFAULT 0,
  `salah` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `skor_game_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_daily_streak_logs`
--

DROP TABLE IF EXISTS `user_daily_streak_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_daily_streak_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `challenge_date` varchar(10) NOT NULL,
  `question_key` varchar(64) NOT NULL,
  `selected_option` varchar(2) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `used_freeze_recovery` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_daily_streak_logs_user_id_challenge_date_index` (`user_id`,`challenge_date`),
  KEY `user_daily_streak_logs_user_id_index` (`user_id`),
  KEY `user_daily_streak_logs_challenge_date_index` (`challenge_date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_daily_streaks`
--

DROP TABLE IF EXISTS `user_daily_streaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_daily_streaks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `current_streak` int(10) unsigned NOT NULL DEFAULT 0,
  `longest_streak` int(10) unsigned NOT NULL DEFAULT 0,
  `freeze_recovery_left` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `total_completed` int(10) unsigned NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'idle',
  `last_completed_at` timestamp NULL DEFAULT NULL,
  `last_completed_date` varchar(10) DEFAULT NULL,
  `weekly_days_json` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_daily_streaks_user_id_unique` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_follows`
--

DROP TABLE IF EXISTS `user_follows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_follows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `follower_id` bigint(20) unsigned NOT NULL,
  `following_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_follows_follower_id_following_id_unique` (`follower_id`,`following_id`),
  KEY `user_follows_following_id_index` (`following_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `specialty_role` varchar(80) DEFAULT NULL,
  `avatar_border` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `oauth_uid` (`oauth_uid`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-23  9:17:55

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: digi_safe_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `badges`
--

LOCK TABLES `badges` WRITE;
/*!40000 ALTER TABLE `badges` DISABLE KEYS */;
INSERT INTO `badges` VALUES (1,'Phishing Expert','Menyelesaikan Level 1 dengan skor 80+','🎣',80),(2,'Speed Runner (<10d)','Menyelesaikan tantangan minigame dengan rata-rata waktu di bawah 10 detik.','bolt',50),(3,'Tanpa Salah (Flawless)','Menyelesaikan satu sesi permainan penuh tanpa jawaban salah sedikit pun.','check',60),(4,'Phishing Hunter','Menang dalam simulasi Phishing Hunter dengan skor minimal 60 poin.','shield',60),(5,'Cyber Defender','Berhasil mempertahankan sistem di Cyber Defender dengan skor minimal 60 poin.','defender',60),(6,'Link Inspector','Menang dalam inspeksi tautan Link or Trap dengan skor minimal 60 poin.','link',60),(7,'Master Siber','Mencapai skor tinggi 100+ poin dalam satu sesi permainan.','star',100);
/*!40000 ALTER TABLE `badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
INSERT INTO `chat_messages` VALUES (1,11,1,'Hello Razief Afkar Rusli! 🤖\n\nThank you for requesting the education topic \"Tentang Anu Dong\" (Category: Keamanan Akun). After review and discussion with the Digi-Safe Purwakarta Admin team, we have APPROVED your proposal to be added to our Education Center!\n\nStay tuned for the new learning module. — Automated message by Bot Digika',1,'2026-11-18 04:04:29','2026-11-18 04:04:32'),(2,11,1,'Hello Razief Afkar Rusli. 🤖\n\nThank you for submitting the education topic request \"bnbdd\" (Category: Anti-Phishing). After careful discussion by the Digi-Safe Purwakarta Admin team, we regret that we CANNOT APPROVE this request at the moment because it overlaps with existing modules or falls outside our current curriculum focus.\n\nFeel free to submit another interesting digital safety idea anytime! — Automated message by Bot Digika',1,'2026-11-18 04:05:04','2026-11-18 04:05:07'),(3,13,7,'P',0,'2026-11-19 05:59:24','2026-11-19 05:59:24'),(4,13,7,'SAYA MAU ROTI O NYA 2',0,'2026-11-19 05:59:34','2026-11-19 05:59:34');
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `daily_streak_questions`
--

LOCK TABLES `daily_streak_questions` WRITE;
/*!40000 ALTER TABLE `daily_streak_questions` DISABLE KEYS */;
INSERT INTO `daily_streak_questions` VALUES (1,'adv_apk_accessibility','Forensik Malware Android','Android Malware Forensics','Lanjutan','Advanced','Mengapa file .APK penipuan berkedok undangan nikah atau resi kurir di Indonesia secara spesifik meminta izin Android Accessibility Service selain izin READ_SMS?','Why do Indonesian banking trojan .APK files disguised as wedding invitations specifically request the Android Accessibility Service permission alongside READ_SMS?','{\"A\":\"Untuk mengompresi riwayat SMS agar memori internal ponsel korban tidak penuh\",\"B\":\"Agar malware dapat membaca isi layar, mengklik otomatis persetujuan izin berbahaya, mencegah uninstall, dan memasang overlay login palsu di aplikasi m-Banking\",\"C\":\"Untuk membuka kunci bootloader root tanpa perlu me-restart ponsel Android\",\"D\":\"Untuk mengenkripsi nomor IMSI kartu SIM langsung di dalam chip modem baseband\",\"E\":\"Untuk memaksa sinyal seluler turun otomatis dari jaringan 5G ke 2G GSM\"}','{\"A\":\"To compress SMS databases so they consume less internal phone storage\",\"B\":\"To programmatically read screen contents, auto-click permission prompts, bypass Play Protect, and overlay fake login views\",\"C\":\"To unlock root bootloader access without restarting the smartphone\",\"D\":\"To encrypt SIM card IMSI identifiers inside the baseband processor\",\"E\":\"To force the cellular modem to downgrade from 5G to 2G GSM\"}','B','Penyalahgunaan Accessibility Service memberi malware kendali penuh atas antarmuka layar: membaca PIN/OTP, menyetujui izin secara diam-diam, memblokir tombol Uninstall, hingga menyisipkan layar login palsu di atas aplikasi perbankan.','Abusing Accessibility Services lets malware inspect every UI element on screen, auto-approve dangerous permissions (like SMS & Notification Listener), prevent uninstallation, and inject credential-harvesting overlays on banking apps.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(2,'adv_oauth_pkce','OAuth 2.0 & Identitas','OAuth 2.0 & Identity','Lanjutan','Advanced','Pada alur OAuth 2.0 Authorization Code untuk aplikasi mobile/SPA, serangan spesifik apa yang dicegah oleh PKCE (Proof Key for Code Exchange) yang tidak bisa ditutup oleh static client_secret?','In a mobile or SPA OAuth 2.0 Authorization Code flow, what specific attack does PKCE (Proof Key for Code Exchange) mitigate that a static client_secret cannot?','{\"A\":\"SQL Injection pada tabel token di server autentikasi\",\"B\":\"DNS Cache Poisoning pada domain utama penyedia identitas\",\"C\":\"Penyadapan (interception) authorization code oleh aplikasi berbahaya yang mendaftarkan custom URI scheme yang sama\",\"D\":\"Brute-force kata sandi user melalui endpoint \\/userinfo\",\"E\":\"Pemakaian ulang refresh token JWT yang sudah kedaluwarsa setelah akun dihapus\"}','{\"A\":\"SQL Injection against the authorization server token table\",\"B\":\"DNS cache poisoning on the identity provider apex domain\",\"C\":\"Authorization code interception by a malicious app registered on the same custom URI scheme\",\"D\":\"Brute-forcing user passwords via the \\/userinfo endpoint\",\"E\":\"Replay of expired JWT refresh tokens after account deletion\"}','C','Aplikasi mobile/SPA adalah public client yang tidak aman menyimpan static secret. PKCE mengikat authorization code ke code_verifier & code_challenge unik per sesi, sehingga kode yang disadap aplikasi jahat tidak dapat ditukar menjadi access token.','Public clients (mobile apps/SPAs) cannot keep a static secret safely. PKCE binds each authorization code to a per-request cryptographic code_verifier/code_challenge, making intercepted codes useless to an attacker.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(3,'adv_dmarc_alignment','Keamanan Email & Spoofing','Email Authentication','Lanjutan','Advanced','Sebuah instansi sudah memasang SPF (~all) dan DKIM, namun email penipuan yang mengatasnamakan pimpinan tetap lolos validasi SPF karena penyerang memakai domain Return-Path miliknya sendiri. Kontrol apa yang secara tepat menutup celah ini?','An organization publishes SPF (\"~all\") and DKIM, yet spoofed emails pretending to be its CEO still pass SPF checks using an attacker-controlled Return-Path domain. Which control specifically closes this gap?','{\"A\":\"Mengaktifkan enkripsi oportunistik STARTTLS pada port 587\",\"B\":\"Menerapkan DMARC (p=reject) dengan alignment domain antara Header From yang tampil dan domain terautentikasi SPF\\/DKIM\",\"C\":\"Menambahkan MX record cadangan dengan angka prioritas lebih tinggi\",\"D\":\"Memblokir semua email masuk yang mengandung elemen tabel HTML\",\"E\":\"Mengganti mekanisme login SMTP dari PLAIN ke CRAM-MD5\"}','{\"A\":\"Enabling TLS 1.3 Opportunistic Encryption (STARTTLS) on port 587\",\"B\":\"Enforcing DMARC (p=reject) with identifier alignment between Header From and SPF\\/DKIM domains\",\"C\":\"Adding a second MX record with a higher priority number\",\"D\":\"Blocking all incoming emails that contain HTML tables\",\"E\":\"Switching SMTP authentication from PLAIN to CRAM-MD5\"}','B','SPF hanya memverifikasi alamat envelope Return-Path, bukan Header From yang dilihat korban. DMARC memastikan keselarasan (alignment) domain Header From dengan SPF/DKIM, serta kebijakan p=reject menolak email spoofing secara otomatis.','SPF alone validates only the SMTP envelope Return-Path, not the visible Header From. DMARC enforces domain alignment between the visible From header and the authenticated SPF/DKIM domain, with p=reject instructing receivers to drop unaligned spoofs.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(4,'adv_mfa_fatigue','Pertahanan Autentikasi','Authentication Defense','Lanjutan','Advanced','Penyerang yang mencuri password membombardir puluhan notifikasi push MFA pukul 02.00 dini hari sampai korban yang mengantuk menekan \"Approve\" (MFA Fatigue / Push Bombing). Mekanisme mana yang menetralisir baik MFA fatigue maupun proxy phishing AiTM?','An attacker with stolen credentials spams dozens of MFA push notifications at 2 AM until a drowsy employee taps \"Approve\" (MFA Fatigue / Push Bombing). Which defense neutralizes both MFA fatigue and real-time AiTM phishing proxies?','{\"A\":\"Memperpanjang masa berlaku OTP SMS dari 5 menit menjadi 30 menit\",\"B\":\"Mewajibkan pergantian password rutin setiap 30 hari sekali\",\"C\":\"Menggunakan autentikasi FIDO2 \\/ WebAuthn Passkey yang terikat kriptografis pada origin domain asli\",\"D\":\"Menyembunyikan halaman login di balik nomor port non-standar\",\"E\":\"Mengirimkan 6 digit OTP yang sama lewat email dan WhatsApp secara bersamaan\"}','{\"A\":\"Extending SMS OTP validity from 5 minutes to 30 minutes\",\"B\":\"Requiring users to change passwords every 30 days\",\"C\":\"Replacing push approvals with phishing-resistant FIDO2 \\/ WebAuthn Passkeys bound to the origin domain\",\"D\":\"Hiding the login URL behind a custom port number\",\"E\":\"Sending the same 6-digit OTP via both email and WhatsApp simultaneously\"}','C','FIDO2/WebAuthn Passkey mengikat tanda tangan kriptografis ke domain TLS asli secara ketat dan menuntut verifikasi lokal perangkat, sehingga kebal terhadap Push Bombing maupun reverse-proxy Adversary-in-the-Middle (AiTM).','FIDO2/WebAuthn cryptographically binds authentication signatures to the exact TLS origin domain and requires local user presence, neutralizing both push bombing and Adversary-in-the-Middle (AiTM) reverse-proxy phishing.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(5,'adv_jwt_alg_confusion','Keamanan API & Token','API Security','Lanjutan','Advanced','Sebuah backend memverifikasi token JWT bertanda tangan RS256 tetapi library-nya mempercayai field \"alg\" dari header token secara langsung. Bagaimana penyerang mengeksploitasi JWT Algorithm Confusion (RS256 ke HS256)?','A backend verifies RS256-signed JWTs using a library that trusts the token header\'s \"alg\" field. How does an attacker exploit JWT Algorithm Confusion (RS256 to HS256)?','{\"A\":\"Memecahkan private key RSA-4096 server secara offline dalam 2 detik\",\"B\":\"Mengubah header alg menjadi HS256 lalu menandatangani payload palsu memakai public key RSA server yang bersifat publik sebagai HMAC secret\",\"C\":\"Menyisipkan komentar SQL di dalam string Base64URL header JWT\",\"D\":\"Menghapus sertifikat TLS dari header HTTP Authorization\",\"E\":\"Mengompresi payload JWT dengan gzip agar validasi signature dilewati\"}','{\"A\":\"By brute-forcing the server\'s private RSA-4096 key offline in 2 seconds\",\"B\":\"By modifying alg to HS256 and signing a forged payload using the server\'s publicly available RSA public key as the HMAC secret\",\"C\":\"By appending SQL comments inside the Base64URL header\",\"D\":\"By stripping the TLS certificate from the Authorization header\",\"E\":\"By compressing the JWT payload with gzip to bypass signature verification\"}','B','Jika verifikator memakai variabel key yang sama untuk RS256 dan HS256 mengikuti header token yang tidak dipercaya, penyerang cukup menandatangani token palsu secara simetris (HS256) memakai file public key server.','If the verifier uses the same key variable for both asymmetric (RS256 public key) and symmetric (HS256 shared secret) verification based on the untrusted header, an attacker can sign forged tokens with the known public key bytes using HS256.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(6,'adv_ssrf_imds','Keamanan Cloud & Web','Cloud & Web Security','Lanjutan','Advanced','Aplikasi web mengambil gambar avatar dari URL input pengguna di sisi server (server-side fetch). Mengapa sekadar memblokir string \"localhost\" dan \"127.0.0.1\" tidak cukup mencegah SSRF di infrastruktur cloud?','A web app fetches user-supplied image URLs server-side for avatar import. Why is blocking only \"localhost\" and \"127.0.0.1\" insufficient against SSRF on cloud infrastructure?','{\"A\":\"Karena firewall cloud otomatis mengubah URL HTTP menjadi protokol FTP\",\"B\":\"Karena penyerang dapat menargetkan endpoint metadata cloud 169.254.169.254, alamat IPv6, format IP desimal\\/oktal, HTTP redirect 302, atau DNS rebinding untuk mencuri kredensial IAM\",\"C\":\"Karena browser mengabaikan header Content-Security-Policy pada URL gambar\",\"D\":\"Karena file PNG secara otomatis mengeksekusi kode PHP saat diunduh\",\"E\":\"Karena HTTPS mencegah server membaca nama hostname tujuan\"}','{\"A\":\"Because cloud firewalls automatically rewrite HTTP URLs to FTP\",\"B\":\"Because attackers can target link-local metadata endpoints like 169.254.169.254, IPv6 equivalents, decimal\\/octal IPs, or DNS rebinding to steal IAM credentials\",\"C\":\"Because browsers ignore Content-Security-Policy on image URLs\",\"D\":\"Because PNG files automatically execute PHP code during download\",\"E\":\"Because HTTPS prevents the server from inspecting the hostname\"}','B','Blacklist string mudah ditembus via metadata 169.254.169.254, IP privat RFC1918/IPv6, redirect 302, dan DNS rebinding. Pertahanan wajib me-resolve IP terlebih dahulu, memblokir seluruh rentang privat/link-local, menonaktifkan redirect, dan mengaktifkan IMDSv2.','Cloud metadata services (169.254.169.254), private RFC1918/IPv6 ranges, alternative IP encodings, HTTP 302 redirects, and DNS rebinding bypass naive string blacklists. Defense requires resolving DNS, validating all IPs against public-only allowlists, disabling redirects, and using IMDSv2.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(7,'adv_bcrypt_truncation','Kriptografi Aplikasi','Cryptography','Lanjutan','Advanced','Mengapa aplikasi web perlu membatasi panjang maksimum input password (atau beralih ke Argon2id) ketika menggunakan algoritma hashing bcrypt standar?','Why should applications enforce a sensible maximum password length (or pre-hash carefully / use Argon2id) when hashing passwords with standard bcrypt?','{\"A\":\"Bcrypt menolak seluruh password yang mengandung simbol huruf kapital\",\"B\":\"Bcrypt standar diam-diam memotong input di atas 72 byte (truncation) dan input super panjang tanpa batas dapat memicu serangan CPU DoS\",\"C\":\"Bcrypt menyimpan password dalam bentuk AES-128 ECB yang bisa didekripsi bila lebih dari 32 byte\",\"D\":\"Bcrypt tidak menghasilkan random salt unik untuk setiap akun pengguna\",\"E\":\"Hash bcrypt otomatis kedaluwarsa setelah 90 hari\"}','{\"A\":\"Bcrypt rejects any password containing uppercase symbols\",\"B\":\"Standard bcrypt silently truncates inputs at 72 bytes and huge inputs can cause CPU exhaustion DoS\",\"C\":\"Bcrypt stores passwords in reversible AES-128 ECB format above 32 bytes\",\"D\":\"Bcrypt does not generate a unique random salt per password\",\"E\":\"Bcrypt hashes expire automatically after 90 days\"}','B','Bcrypt hanya memproses 72 byte pertama sehingga dua passphrase panjang dengan awalan sama menghasilkan hash identik, sementara input ratusan kilobyte dapat membebani CPU server (Denial of Service).','Bcrypt only processes the first 72 bytes of input (making distinct long passphrases collide if identical in the first 72 bytes) and unbounded password inputs can trigger DoS during hashing.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(8,'adv_csp_nonce','Keamanan Browser','Browser Security','Lanjutan','Advanced','Mengapa header Content-Security-Policy (CSP) yang berisi \"script-src \'self\' \'unsafe-inline\'\" sebagian besar gagal menghentikan serangan Stored maupun Reflected XSS?','Why is a Content-Security-Policy (CSP) containing \"script-src \'self\' \'unsafe-inline\'\" largely ineffective at stopping Reflected or Stored XSS?','{\"A\":\"Karena \'self\' mengizinkan pemuatan skrip dari seluruh domain HTTPS publik\",\"B\":\"Karena \'unsafe-inline\' tetap mengizinkan blok <script> dan atribut event seperti onerror= hasil injeksi penyerang untuk dieksekusi langsung\",\"C\":\"Karena browser modern mengabaikan CSP jika situs sudah memakai HTTPS\",\"D\":\"Karena CSP hanya melindungi file CSS dan tidak pernah membatasi JavaScript\",\"E\":\"Karena \'unsafe-inline\' menonaktifkan flag HttpOnly pada cookie sesi\"}','{\"A\":\"Because \'self\' allows loading scripts from any HTTPS domain on the internet\",\"B\":\"Because \'unsafe-inline\' permits injected <script> blocks and inline event handlers (like onerror=) to execute directly\",\"C\":\"Because modern browsers ignore CSP headers delivered over HTTPS\",\"D\":\"Because CSP only protects CSS stylesheets and never restricts JavaScript\",\"E\":\"Because \'unsafe-inline\' disables HttpOnly on session cookies\"}','B','Direktif \'unsafe-inline\' membuka pintu penuh bagi payload XSS inline. Mitigasi CSP yang kuat memanfaatkan cryptographic nonce unik per request (script-src \'nonce-...\') atau hash ketat tanpa unsafe-inline.','Allowing \'unsafe-inline\' lets attacker-injected inline scripts run unrestricted. Effective XSS mitigation uses per-request cryptographic nonces (script-src \'nonce-...\') or strict hashes without unsafe-inline.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(9,'adv_session_fixation','Manajemen Sesi','Session Management','Lanjutan','Advanced','Tepat setelah pengguna berhasil login atau melakukan reset password via OTP, dua tindakan manajemen sesi sisi server apa yang wajib dilakukan untuk mencegah Session Fixation dan pembajakan persisten?','Immediately after a user successfully authenticates or resets their password, what two server-side session actions prevent Session Fixation and persistent account takeover?','{\"A\":\"Meng-encode username dengan Base64 di localStorage dan mematikan CSRF token\",\"B\":\"Meregenerasi Session ID saat eskalasi\\/perubahan autentikasi serta mencabut seluruh sesi perangkat lain dan token API lama milik akun tersebut\",\"C\":\"Mengatur SameSite=None tanpa atribut Secure serta masa aktif cookie 10 tahun\",\"D\":\"Menampilkan Session ID mentah di parameter query URL pada setiap redirect\",\"E\":\"Mengizinkan seluruh perangkat berbagi satu CSRF token statis yang sama\"}','{\"A\":\"Base64-encoding the username in localStorage and disabling CSRF tokens\",\"B\":\"Regenerating the session ID on privilege change and invalidating all other active sessions\\/API tokens for that account\",\"C\":\"Setting SameSite=None without Secure and extending cookie lifetime to 10 years\",\"D\":\"Sending the raw session ID in the URL query string for every redirect\",\"E\":\"Allowing concurrent sessions to share the same unrotated CSRF token\"}','B','Regenerasi Session ID mematahkan Session Fixation, sedangkan pencabutan seluruh sesi perangkat lain dan token API segera menendang keluar penyerang yang sempat masuk sebelumnya.','Session ID regeneration defeats Session Fixation, while revoking all other stored sessions and API tokens immediately evicts any attacker who previously compromised the account.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(10,'adv_subdomain_takeover','Keamanan Infrastruktur','Infrastructure Security','Lanjutan','Advanced','Sebuah sekolah menghapus halaman kampanye lamanya di penyedia SaaS pihak ketiga tetapi lupa menghapus DNS CNAME \"event.sekolah.sch.id -> app-lama.saas-host.net\". Risiko kritis apa yang muncul?','A school removes its old cloud landing page hosted on a third-party SaaS platform but forgets to delete the DNS CNAME record \"promo.school.sch.id -> old-app.saas-host.net\". What risk arises?','{\"A\":\"Seluruh siswa kehilangan koneksi internet di jaringan Wi-Fi sekolah\",\"B\":\"Subdomain Takeover: penyerang mengklaim \\\"app-lama.saas-host.net\\\", menyajikan situs phishing ber-HTTPS sah di bawah domain sekolah, dan berpotensi mencuri cookie wildcard\",\"C\":\"Database MySQL utama milik sekolah terhapus otomatis oleh DNS\",\"D\":\"DNSSEC otomatis memindahkan kepemilikan domain .sch.id ke pihak lain\",\"E\":\"Browser otomatis memblokir domain utama sekolah.sch.id\"}','{\"A\":\"All students lose internet access on the school Wi-Fi\",\"B\":\"Subdomain Takeover: an attacker claims \\\"old-app.saas-host.net\\\", serving phishing pages with valid HTTPS under the trusted school domain and potentially reading wildcard cookies\",\"C\":\"The school\'s primary MySQL database is automatically dropped\",\"D\":\"DNSSEC automatically transfers ownership of the .sch.id TLD\",\"E\":\"Browsers automatically block the apex domain school.sch.id\"}','B','Dangling CNAME memungkinkan siapa pun mendaftarkan ulang target resource yang kosong di platform SaaS, mewarisi reputasi subdomain resmi, sertifikat HTTPS valid, serta akses cookie yang di-scope ke *.sekolah.sch.id.','A dangling CNAME allows anyone to provision the unclaimed target resource on the SaaS provider, inheriting the trusted subdomain, TLS certificate issuance, and parent-domain cookies scoped to *.school.sch.id.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(11,'adv_idor_authz','Kontrol Akses (OWASP A01)','Access Control (OWASP A01)','Lanjutan','Advanced','Endpoint API GET /api/dokumen/1042 hanya mengecek apakah Bearer token valid lalu menjalankan Dokumen::find(1042). Mengubah 1042 ke 1043 menampilkan data warga lain. Apa nama celah ini dan perbaikan utamanya?','An API endpoint GET /api/invoices/1042 checks only that the user has a valid Bearer token, then fetches Invoice::find(1042). Changing 1042 to 1043 reveals another citizen\'s record. What is this vulnerability and its proper fix?','{\"A\":\"XSS; perbaiki dengan melakukan HTML escape pada output JSON\",\"B\":\"CSRF; perbaiki dengan menambahkan header X-Frame-Options\",\"C\":\"BOLA \\/ IDOR (Broken Object Level Authorization); perbaiki dengan membatasi query berdasarkan kepemilikan user (Auth::id()) atau otorisasi Policy pada setiap request\",\"D\":\"Clickjacking; perbaiki hanya dengan mengganti ID angka ke hash MD5 tanpa cek kepemilikan\",\"E\":\"Open Redirect; perbaiki dengan memvalidasi header Referer\"}','{\"A\":\"XSS; fix by escaping HTML in JSON responses\",\"B\":\"CSRF; fix by adding an X-Frame-Options header\",\"C\":\"BOLA \\/ IDOR (Broken Object Level Authorization); fix by scoping queries to Auth::id() or enforcing object-level Policy authorization on every request\",\"D\":\"Clickjacking; fix by switching from sequential IDs to MD5 hashes without authorization checks\",\"E\":\"Open Redirect; fix by validating the Referer header\"}','C','Autentikasi hanya membuktikan identitas, bukan hak akses atas objek tertentu. Celah BOLA/IDOR wajib ditutup dengan validasi otorisasi/kepemilikan di sisi server pada setiap akses baca maupun ubah.','Authentication proves who you are, NOT what objects you may access. BOLA/IDOR must be fixed via server-side object ownership/policy checks on every read/write operation (UUIDs alone are defense-in-depth, never a substitute for authorization).',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13'),(12,'adv_ransomware_32110','Ketahanan Insiden','Incident Resilience','Lanjutan','Advanced','Mengapa strategi pertahanan ransomware modern meningkatkan aturan backup klasik 3-2-1 menjadi aturan 3-2-1-1-0?','Why does modern ransomware defense upgrade the classic 3-2-1 backup rule to the 3-2-1-1-0 rule?','{\"A\":\"Agar kompresi backup berjalan dengan 0% beban CPU di flashdisk lokal\",\"B\":\"Untuk menambahkan minimal 1 salinan offline\\/air-gapped atau immutable (WORM) agar ransomware berhak admin tidak dapat mengenkripsi backup, serta 0 error saat uji pemulihan\",\"C\":\"Untuk menyimpan seluruh salinan backup di folder share SMB yang sama dengan server produksi\",\"D\":\"Untuk mematikan enkripsi pada backup offsite supaya proses restore lebih cepat\",\"E\":\"Untuk menjamin uptime 100% tanpa perlu pernah menguji proses restore backup\"}','{\"A\":\"To compress backups using 0% CPU overhead on local USB drives\",\"B\":\"To add at least 1 offline\\/air-gapped or immutable (WORM) copy so ransomware with admin credentials cannot encrypt backups, plus 0 unverified recovery errors\",\"C\":\"To store all backups inside the same SMB network share as production servers\",\"D\":\"To disable encryption on offsite backups for faster restoration\",\"E\":\"To guarantee zero downtime without ever testing restore procedures\"}','B','Ransomware modern secara aktif mencari dan mengenkripsi backup online serta Volume Shadow Copies. Salinan immutable (Write-Once-Read-Many) / air-gapped ditambah verifikasi restore bebas error menjamin pemulihan tanpa membayar tebusan.','Modern ransomware actively targets online backups and shadow copies. An immutable (Write-Once-Read-Many) or air-gapped copy combined with verified restore tests (0 errors) ensures guaranteed recovery without paying ransom.',1,NULL,'2026-11-23 00:29:13','2026-11-23 00:29:13');
/*!40000 ALTER TABLE `daily_streak_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `edukasi_requests`
--

LOCK TABLES `edukasi_requests` WRITE;
/*!40000 ALTER TABLE `edukasi_requests` DISABLE KEYS */;
INSERT INTO `edukasi_requests` VALUES (1,1,'Tentang Anu Dong','Keamanan Akun','link','karna menurut saya perlu untuk dibahas','approved',NULL,1,'2026-11-18 04:04:28',1,'2026-11-18 04:04:15','2026-11-18 04:04:45'),(2,1,'bnbdd','Anti-Phishing','teks','fffffadadadadsadadad','rejected',NULL,1,'2026-11-18 04:05:04',1,'2026-11-18 04:05:00','2026-11-18 04:10:47');
/*!40000 ALTER TABLE `edukasi_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `forum`
--

LOCK TABLES `forum` WRITE;
/*!40000 ALTER TABLE `forum` DISABLE KEYS */;
INSERT INTO `forum` VALUES (1,10,'Cara membedakan pesan kurir paket asli dengan modus file APK?','Kemarin saya menerima pesan WhatsApp dari nomor tidak dikenal yang mengirim file LIHAT-FOTO-PAKET.apk. Bagaimana langkah aman jika terlanjur mengunduh tetapi belum menginstalnya?','Phishing','terjawab',0,0,'2026-04-29 02:44:36',42),(2,4,'Apakah aman menggunakan Wi-Fi publik di taman kota untuk transaksi mobile banking?','Sering kali saat sedang di luar rumah saya menggunakan Wi-Fi gratis untuk membuka aplikasi perbankan. Apa risiko keamanan utamanya dan bagaimana cara mengatasinya?','Privasi','terjawab',0,0,'2026-08-15 03:27:20',68),(3,5,'Akun Instagram tiba-tiba mengirim DM tautan hadiah ke semua teman','Teman-teman saya memberi tahu bahwa akun Instagram saya mengirim pesan link phishing secara otomatis. Bagaimana cara memulihkan akun dan mencabut akses aplikasi pihak ketiga?','Malware','terjawab',0,0,'2026-08-19 08:40:45',95),(4,3,'Mengenali telepon penipuan yang mengaku petugas bank meminta kode OTP','Orang tua saya hampir tertipu telepon yang menyebutkan adanya biaya admin bulanan baru dan meminta 6 digit kode SMS. Bagaimana cara mengedukasi keluarga di rumah agar tidak mudah panik?','Penipuan','terjawab',0,0,'2026-08-20 04:31:22',53),(5,6,'Rekomendasi pengelola kata sandi (Password Manager) dan aktivasi 2FA','Saya masih sering menggunakan satu password yang sama untuk banyak akun email dan media sosial. Aplikasi autentikator 2FA apa yang paling mudah digunakan untuk pemula?','Tanya Pakar','terjawab',0,0,'2026-08-20 04:37:35',77),(6,8,'F','ddd','Malware','terjawab',0,0,'2026-11-16 22:15:33',1),(7,9,'tes tes','tes tes','Penipuan','terbuka',0,0,'2026-11-16 23:15:14',1),(8,1,'Gaesss','ssadadadadada','Privasi','terbuka',0,1,'2026-11-19 02:08:11',1);
/*!40000 ALTER TABLE `forum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `friendships`
--

LOCK TABLES `friendships` WRITE;
/*!40000 ALTER TABLE `friendships` DISABLE KEYS */;
INSERT INTO `friendships` VALUES (1,13,7,'pending','2026-11-19 05:59:24','2026-11-19 05:59:24');
/*!40000 ALTER TABLE `friendships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `jawaban_forum`
--

LOCK TABLES `jawaban_forum` WRITE;
/*!40000 ALTER TABLE `jawaban_forum` DISABLE KEYS */;
INSERT INTO `jawaban_forum` VALUES (1,1,10,'Jika file .apk baru terunduh namun belum diinstal, segera hapus file tersebut dari folder Download, pastikan opsi \"Install Unknown Apps\" pada browser/WhatsApp nonaktif, lalu periksa izin SMS di pengaturan ponsel Anda.',1,'2026-04-29 02:44:54'),(2,3,2,'Segera ganti password Instagram Anda, aktifkan Autentikasi Dua Faktor (2FA), lalu masuk ke menu Settings > Website Permissions > Apps and Websites untuk mencabut sesi perangkat atau aplikasi mencurigakan.',1,'2026-08-20 02:49:09'),(3,3,4,'Saya pernah mengalami hal serupa! Jangan lupa cek juga bagian \"Where You\'re Logged In\" dan keluarkan semua perangkat yang tidak dikenali.',0,'2026-08-20 04:02:10'),(4,3,5,'Terima kasih banyak sarannya! Setelah saya ganti sandi dan cabut akses sesi asing, pesan otomatisnya sudah berhenti.',0,'2026-08-20 04:15:57'),(5,2,2,'Sebaiknya hindari transaksi finansial di Wi-Fi publik tanpa enkripsi karena rawan penyadapan paket data (Man-in-the-Middle). Gunakan koneksi data seluler pribadi saat membuka mobile banking.',1,'2026-08-20 04:23:26'),(6,4,2,'Ingatkan keluarga bahwa pihak bank resmi tidak pernah meminta kode OTP, PIN, atau CVV dengan alasan apa pun. Jika menerima telepon mencurigakan, segera tutup dan hubungi Call Center resmi bank.',1,'2026-08-20 04:33:40'),(7,5,4,'Untuk autentikator 2FA, Google Authenticator atau Bitwarden sangat ringan dan mudah dipakai oleh pelajar maupun orang tua.',0,'2026-08-24 00:49:20'),(8,6,1,'ddd',1,'2026-11-16 22:15:58');
/*!40000 ALTER TABLE `jawaban_forum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `jawaban_forum_votes`
--

LOCK TABLES `jawaban_forum_votes` WRITE;
/*!40000 ALTER TABLE `jawaban_forum_votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `jawaban_forum_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `log_aktivitas`
--

LOCK TABLES `log_aktivitas` WRITE;
/*!40000 ALTER TABLE `log_aktivitas` DISABLE KEYS */;
INSERT INTO `log_aktivitas` VALUES (1,1,'Login berhasil','2026-04-29 02:35:00'),(2,1,'Login berhasil','2026-04-29 02:43:15'),(3,1,'Membuat pertanyaan: tes','2026-04-29 02:44:36'),(4,1,'Menyelesaikan Level 1 - Skor: 130','2026-04-29 03:09:36'),(5,1,'Login berhasil','2026-04-29 03:55:44'),(6,1,'Login berhasil','2026-04-29 04:13:23'),(7,2,'Login sebagai Admin','2026-04-29 04:16:27'),(8,2,'Login berhasil','2026-04-29 04:17:17'),(9,1,'Login berhasil','2026-04-29 04:34:40'),(10,1,'Login berhasil','2026-04-29 04:37:58'),(11,1,'Login berhasil','2026-04-29 05:48:39'),(12,1,'Login berhasil','2026-04-29 05:51:46'),(13,1,'Login berhasil','2026-04-30 00:00:04'),(14,1,'Login berhasil','2026-04-30 00:32:25'),(15,1,'Mengirim testimoni: Gilaa','2026-04-30 00:51:04'),(16,2,'Login berhasil','2026-04-30 00:51:29'),(17,2,'Mengirim testimoni: Gilaa','2026-04-30 01:14:24'),(18,1,'Login berhasil','2026-04-30 01:14:41'),(19,1,'Login berhasil','2026-04-30 01:54:00'),(20,1,'Login berhasil','2026-04-30 01:56:08'),(21,1,'Mengupdate profil','2026-04-30 01:56:33'),(22,1,'Mengupdate profil','2026-04-30 01:59:20'),(23,1,'Mengupdate profil','2026-04-30 04:48:59'),(24,1,'Login berhasil','2026-05-20 01:16:32'),(25,1,'Login berhasil','2026-07-27 02:55:42'),(26,4,'Login berhasil','2026-07-27 02:57:39'),(27,4,'Login berhasil','2026-07-29 00:41:19'),(28,1,'Login berhasil','2026-07-29 00:41:30'),(29,1,'Login berhasil','2026-07-29 01:12:56'),(30,1,'Login berhasil','2026-07-29 01:16:25'),(31,1,'Login sebagai Admin','2026-07-29 01:53:27'),(32,1,'Login berhasil','2026-07-30 07:40:57'),(33,1,'Mengupdate profil','2026-07-30 07:46:54'),(34,1,'Login berhasil','2026-08-05 06:36:32'),(35,1,'Login berhasil','2026-08-10 01:46:27'),(36,1,'Login berhasil','2026-08-10 04:20:23'),(37,1,'Login berhasil','2026-08-11 00:39:18'),(38,1,'Login berhasil','2026-08-11 01:16:47'),(39,1,'Login berhasil','2026-08-11 01:18:52'),(40,1,'Login berhasil','2026-08-11 01:19:10'),(41,1,'Login berhasil','2026-08-11 01:30:56'),(42,1,'Login berhasil','2026-08-11 01:36:58'),(43,4,'Login berhasil','2026-08-11 01:37:26'),(44,4,'Login berhasil','2026-08-11 01:37:38'),(45,1,'Login berhasil','2026-08-11 01:37:48'),(46,1,'Login sebagai Admin','2026-08-11 01:38:08'),(47,1,'Login sebagai Admin','2026-08-11 01:38:17'),(48,1,'Login berhasil','2026-08-12 02:30:43'),(49,1,'Login berhasil','2026-08-12 03:51:06'),(50,1,'Mengupdate profil','2026-08-12 06:38:46'),(51,1,'Mengupdate profil','2026-08-12 06:45:29'),(52,1,'Mengupdate profil','2026-08-12 06:48:30'),(53,1,'Mengupdate profil','2026-08-12 06:48:36'),(54,1,'Mengupdate profil','2026-08-12 06:48:58'),(55,1,'Mengupdate profil','2026-08-12 06:51:17'),(56,1,'Mengupdate profil','2026-08-12 06:51:24'),(57,1,'Mengupdate profil','2026-08-12 06:51:30'),(58,1,'Mengupdate profil','2026-08-12 06:51:31'),(59,1,'Mengirim testimoni: halo halo tes','2026-08-12 07:29:40'),(60,1,'Mengirim testimoni: 32423432432','2026-08-12 07:38:41'),(61,1,'Mengirim testimoni: 32423432432','2026-08-12 07:38:50'),(62,1,'Login berhasil','2026-08-14 16:19:41'),(63,1,'Mengupdate profil','2026-08-14 18:06:52'),(64,1,'Login berhasil','2026-08-15 03:02:04'),(65,1,'Membuat pertanyaan: modul testing','2026-08-15 03:27:20'),(66,1,'Login berhasil','2026-08-19 00:47:33'),(67,4,'Login berhasil','2026-08-19 04:05:14'),(68,4,'Menyelesaikan Level 1 - Skor: 120','2026-08-19 04:58:10'),(69,4,'Menyelesaikan Level 1 - Skor: 70','2026-08-19 05:09:02'),(70,4,'Login berhasil','2026-08-19 07:28:50'),(71,1,'Login berhasil','2026-08-19 07:28:59'),(72,1,'Mengupdate profil','2026-08-19 07:29:41'),(73,1,'Mengupdate profil','2026-08-19 07:30:08'),(74,1,'Membuat pertanyaan: 1313123123','2026-08-19 08:40:45'),(75,1,'Menyelesaikan Level 1 - Skor: 100','2026-08-19 08:59:34'),(76,1,'Login berhasil','2026-08-20 02:32:50'),(77,5,'Login berhasil','2026-08-20 03:58:33'),(78,1,'Login berhasil','2026-08-20 03:58:54'),(79,1,'Mengupdate profil','2026-08-20 04:16:27'),(80,5,'Login berhasil','2026-08-20 04:28:08'),(81,1,'Login berhasil','2026-08-20 04:28:24'),(82,1,'Membuat pertanyaan: halo hal otes tes','2026-08-20 04:31:22'),(83,1,'Membuat pertanyaan: 23432423423432dasdadasasdasd','2026-08-20 04:37:35'),(84,1,'Menyelesaikan Level 1 - Skor: 20','2026-08-20 07:45:02'),(85,1,'Login berhasil','2026-08-24 00:33:18'),(86,4,'Login berhasil','2026-08-24 00:48:44'),(87,4,'Mengupdate profil','2026-08-24 00:49:09'),(88,5,'Login berhasil','2026-08-24 00:49:28'),(89,5,'Login berhasil','2026-08-24 01:06:05'),(90,5,'Mengirim testimoni: 31313','2026-08-24 01:31:40'),(91,5,'Mengirim testimoni: 31313','2026-08-24 01:31:50'),(92,5,'Mengirim testimoni: 31313','2026-08-24 01:31:54'),(93,5,'Login berhasil','2026-08-24 01:32:01'),(94,5,'Login berhasil','2026-08-24 04:24:31'),(95,1,'Login berhasil','2026-08-24 06:08:03'),(96,1,'Login berhasil','2026-08-26 01:54:25'),(97,1,'Login berhasil','2026-08-26 03:14:04'),(98,6,'Registrasi dan Login berhasil dengan Google','2026-08-31 01:41:42'),(99,7,'Registrasi dan Login berhasil dengan Google','2026-08-31 01:42:02'),(100,7,'Mengupdate profil','2026-08-31 01:43:04'),(101,1,'Login berhasil','2026-08-31 05:57:05'),(102,1,'Login berhasil','2026-08-31 06:20:03'),(103,1,'Login berhasil','2026-09-01 01:43:10'),(104,1,'Mengirim testimoni: adad','2026-09-01 02:22:54'),(105,1,'Login berhasil','2026-09-03 06:55:48'),(106,1,'Login berhasil','2026-09-04 03:07:11'),(107,1,'Login berhasil','2026-09-09 00:23:20'),(108,1,'Login berhasil','2026-09-09 00:38:51'),(109,6,'Login berhasil dengan Google','2026-09-09 01:07:49'),(110,1,'Login berhasil','2026-09-09 01:09:27'),(111,4,'Login berhasil','2026-09-09 01:47:16'),(112,1,'Login berhasil','2026-09-09 01:47:23'),(113,1,'Login berhasil','2026-09-09 07:03:38'),(114,1,'Login berhasil','2026-09-14 00:57:00'),(115,1,'Login berhasil','2026-09-14 00:57:09');
/*!40000 ALTER TABLE `log_aktivitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000001_create_cache_table',1),(2,'0001_01_01_000002_create_jobs_table',1),(3,'2026_08_19_071032_create_sessions_table',2),(4,'2026_11_17_070001_add_url_and_type_to_modul_edukasi_table',3),(5,'2026_11_17_070002_add_is_starred_to_forum_table',4),(6,'2026_11_17_080001_add_banner_to_users_table',5),(7,'2026_11_19_120001_add_avatar_border_to_users_table',6),(8,'2026_11_20_000001_create_account_otps_table',7),(9,'2026_11_20_000002_create_security_activity_and_streak_tables',8);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `minigame_levels`
--

LOCK TABLES `minigame_levels` WRITE;
/*!40000 ALTER TABLE `minigame_levels` DISABLE KEYS */;
INSERT INTO `minigame_levels` VALUES (1,'easy','Pemula Siber','LEVEL 1 • EASY','Ciri penipuan dasar seperti hadiah undian palsu, file APK kurir, dan tautan mencurigakan.',15,10,5,3,1,'emerald',1),(2,'medium','Waspada Menengah','LEVEL 2 • MEDIUM','Samaran domain mirip instansi resmi, tagihan palsu, dan serangan malware jaringan.',13,15,5,3,2,'sky',1),(3,'hard','Detektif Siber','LEVEL 3 • HARD','Spear-phishing halus, manipulasi psikologis, dan gelombang serangan siber beruntun.',11,20,8,3,3,'amber',1),(4,'expert','Spesialis Forensik','LEVEL 4 • EXPERT','Analisis serangan Business Email Compromise (BEC), zero-day exploit, dan homograph URL.',9,25,10,2,4,'purple',1),(5,'master','Komandan Pertahanan','LEVEL 5 • MASTER','Simulasi ancaman APT (Advanced Persistent Threat) berkecepatan tinggi dengan toleransi minimal.',7,30,12,2,5,'red',1);
/*!40000 ALTER TABLE `minigame_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `modul_edukasi`
--

LOCK TABLES `modul_edukasi` WRITE;
/*!40000 ALTER TABLE `modul_edukasi` DISABLE KEYS */;
INSERT INTO `modul_edukasi` VALUES (7,'Jurnal ITEE — Keamanan Digital','Kajian akademik tentang keamanan informasi dan perlindungan privasi pengguna di era transformasi digital.','https://journal.pandawan.id/itee/article/view/665',NULL,'Jurnal Akademik','link','https://journal.pandawan.id/itee/article/view/665',10,'2026-11-16 23:36:03'),(8,'Bug Bounty 2025 — Kemendikdasmen','Program penguatan talenta siber dan ketahanan infrastruktur pendidikan nasional.','https://www.kemendikdasmen.go.id/en/press-release/14317-boosting-cyber-talent-strengthening-digital-security-in-education-in-indonesia-through-bug-bounty-2025',NULL,'Talenta Siber','link','https://www.kemendikdasmen.go.id/en/press-release/14317-boosting-cyber-talent-strengthening-digital-security-in-education-in-indonesia-through-bug-bounty-2025',10,'2026-11-16 23:36:03'),(9,'MAFINDO — Masyarakat Anti Fitnah Indonesia','Komunitas pemeriksa fakta dan rujukan verifikasi berita bohong (hoaks) di Indonesia.','https://mafindo.or.id/',NULL,'Cek Fakta','link','https://mafindo.or.id/',2,'2026-11-16 23:36:03'),(10,'Literasi Digital Indonesia','Portal resmi literasi digital nasional berbasis 4 pilar: Cakap, Aman, Budaya, dan Etis.','https://literasidigital.id/',NULL,'Portal Nasional','link','https://literasidigital.id/',2,'2026-11-16 23:36:03'),(11,'Bijak Bermedia Sosial — DJKN Kemenkeu','Panduan bijak menggunakan media sosial dan menjaga rekam jejak digital dari Kementerian Keuangan.','https://www.djkn.kemenkeu.go.id/artikel/baca/15818/Bijak-Bermedia-Sosial.html',NULL,'Etika Siber','link','https://www.djkn.kemenkeu.go.id/artikel/baca/15818/Bijak-Bermedia-Sosial.html',2,'2026-11-16 23:36:03'),(12,'Panduan Praktis Mengenali Modus File APK & Phishing WhatsApp','Penipuan bermodus file berekstensi .APK (seperti Surat Undangan Nikah, Resi Kurir Paket, atau Tagihan Listrik) bekerja dengan cara meminta izin baca SMS untuk mencuri kode OTP perbankan Anda.Langkah Pencegahan Utama:1. Jangan pernah membuka atau menginstal file berakhiran .apk yang dikirim melalui WhatsApp atau Telegram.2. Matikan izin \'Install Unknown Apps\' pada pengaturan browser dan aplikasi pesan Anda.3. Apabila terlanjur menginstal, segera matikan koneksi internet (aktifkan Mode Pesawat), hapus aplikasi mencurigakan tersebut, lalu hubungi pihak bank untuk memblokir sementara layanan mobile banking..','',NULL,'Panduan Publik','teks',NULL,10,'2026-11-16 23:36:03'),(14,'Panduan K3LH','K3LH adalah singkatan dari Kesehatan, Keselamatan Kerja, dan Lingkungan Hidup. Pedoman K3LH merupakan seperangkat aturan, prosedur, dan sistem manajemen yang diterapkan di tempat kerja atau sekolah untuk melindungi keselamatan manusia, mengamankan aset, serta menjaga kelestarian lingkungan.Landasan hukum utama pelaksanaan K3 di Indonesia diatur melalui Undang-Undang No. 1 Tahun 1970 tentang Keselamatan Kerja dan Peraturan Pemerintah (PP) No. 50 Tahun 2012 tentang Penerapan Sistem Manajemen Keselamatan dan Kesehatan Kerja (SMK3).Tujuan Utama K3LH• Melindungi Pekerja: Mencegah kecelakaan kerja, cedera, dan penyakit akibat kerja (PAK).• Keamanan Aset dan Alat: Menjaga peralatan, mesin, dan infrastruktur kerja agar beroperasi secara aman dan efisien.• Pelestarian Lingkungan: Mengendalikan limbah, polusi, dan dampak negatif proses kerja terhadap lingkungan.• Meningkatkan Produktivitas: Menciptakan suasana kerja yang sehat, nyaman, dan harmonis sehingga performa kerja meningkat.7 Langkah Penerapan Pedoman K3LH1. Memahami Konsep Dasar: Mengenal potensi bahaya, regulasi, dan tanggung jawab setiap individu di lingkungan kerja.2. Identifikasi dan Evaluasi Risiko: Melakukan pemetaan bahaya (fisik, kimia, biologis, ergosomi, dan psikososial) melalui metode seperti HIRARC (Hazard Identification, Risk Assessment, and Risk Control).3. Pencegahan dan Pengendalian: Menerapkan hierarki pengendalian risiko:	• Eliminasi: Menghilangkan sumber bahaya.	• Substitusi: Mengganti bahan/proses dengan yang lebih aman.	• Engineering Control: Modifikasi teknis atau rekayasa alat.	• Kontrol Administratif: Pembuatan SOP, rambu, dan aturan kerja.	• APD (Alat Pelindung Diri): Menggunakan helm, sepatu keselamatan, sarung tangan, kacamata, dan masker sesuai standar.4. Pelatihan dan Simulasi: Memberikan edukasi rutin, pelatihan tanggap darurat (kebakaran, gempa bumi), serta sertifikasi bagi tenaga kerja.5. Menerapkan Sistem Manajemen: Mengadopsi standar resmi seperti ISO 45001 (K3) dan ISO 14001 (Lingkungan Hidup).6. Membangun Kesadaran Tim: Saling mengingatkan dan menanamkan budaya K3 sebagai tanggung jawab bersama.7. Pemantauan dan Perbaikan Berkelanjutan: Melakukan inspeksi rutin, audit internal, serta evaluasi manajemen untuk perbaikan sistem secara terus-menerus (Plan-Do-Check-Act).Contoh Alat Pelindung Diri (APD) Standar• Kepala: Safety helmet (helm pengaman) untuk melindungi dari jatuhnya benda.• Mata: Safety goggles untuk menahan debu, serbuk, atau percikan bahan kimia.• Pernapasan: Masker atau respirator untuk menghindari gas beracun dan debu halus.• Pendengaran: Earplug atau earmuff untuk area dengan intensitas kebisingan tinggi.• Tangan: Sarung tangan khusus (kulit untuk las, karet untuk kelistrikan, katun untuk material kasar).• Kaki: Safety shoes berujung baja untuk mencegah tertimpa atau tertusuk benda tajam.• Ketinggian: Safety belt atau full body harness saat bekerja di atas ketinggian lebih dari 1,8 meter.','',NULL,'Panduan Publik','teks',NULL,1,'2026-11-18 02:49:57');
/*!40000 ALTER TABLE `modul_edukasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `progres_baca`
--

LOCK TABLES `progres_baca` WRITE;
/*!40000 ALTER TABLE `progres_baca` DISABLE KEYS */;
/*!40000 ALTER TABLE `progres_baca` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `security_activity_logs`
--

LOCK TABLES `security_activity_logs` WRITE;
/*!40000 ALTER TABLE `security_activity_logs` DISABLE KEYS */;
INSERT INTO `security_activity_logs` VALUES (1,15,'login_google','127.0.0.1','Chrome · Windows','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-20 05:24:59'),(2,1,'login','127.0.0.1','Chrome · Windows','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-20 05:25:37'),(3,1,'login','127.0.0.1','Chrome · Windows','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','2026-11-23 01:17:12');
/*!40000 ALTER TABLE `security_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `skor_game`
--

LOCK TABLES `skor_game` WRITE;
/*!40000 ALTER TABLE `skor_game` DISABLE KEYS */;
INSERT INTO `skor_game` VALUES (1,1,'level1',130,130,'2026-04-29 03:09:36',0,0),(2,1,'level3',150,150,'2026-04-30 00:33:41',0,0),(3,4,'level1',120,120,'2026-08-19 04:58:10',0,0),(4,4,'level1',70,120,'2026-08-19 05:09:02',0,0),(5,1,'level1',100,130,'2026-08-19 08:59:34',0,0),(6,1,'level1',20,130,'2026-08-20 07:45:02',0,0),(7,8,'link-trap',100,100,'2026-11-17 00:27:45',0,0),(8,1,'cyber-defender',0,0,'2026-11-19 03:16:39',0,3),(9,13,'phishing',65,65,'2026-11-19 06:49:16',7,2),(10,1,'daily-streak',40,40,'2026-11-20 04:18:58',1,0),(11,1,'wa-simulator',30,30,'2026-11-20 04:25:08',1,0),(12,1,'wa-simulator',30,30,'2026-11-20 04:30:04',1,0),(13,15,'daily-streak',40,40,'2026-11-20 05:25:26',1,0),(14,1,'wa-simulator',15,30,'2026-11-20 05:28:30',0,0);
/*!40000 ALTER TABLE `skor_game` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `soal_minigame`
--

LOCK TABLES `soal_minigame` WRITE;
/*!40000 ALTER TABLE `soal_minigame` DISABLE KEYS */;
INSERT INTO `soal_minigame` VALUES (1,'Apa ciri email phishing?','[\"Alamat pengirim resmi\",\"Meminta data pribadi\",\"Dari teman\",\"Ada logo perusahaan\"]','Meminta data pribadi','keamanan','mudah','level1'),(2,'Password yang paling aman adalah...','[\"12345678\",\"password123\",\"P@ssw0rd!2024\",\"namakamu\"]','P@ssw0rd!2024','password','mudah','level1'),(3,'Jika dapat info giveaway hadiah besar, yang harus dilakukan...','[\"Langsung klik\",\"Share ke teman\",\"Cek kebenaran dulu\",\"Isi data diri\"]','Cek kebenaran dulu','hoaks','mudah','level1'),(4,'Apa itu ransomware?','[\"Antivirus\",\"Malware enkripsi data\",\"WiFi cepat\",\"Password manager\"]','Malware enkripsi data','keamanan','sedang','level1'),(5,'Apa fungsi VPN?','[\"Mempercepat WiFi\",\"Mengenkripsi koneksi\",\"Virus Protection\",\"Editor video\"]','Mengenkripsi koneksi','keamanan','sedang','level1'),(6,'Apa itu 2FA?','[\"Password 2x lipat\",\"Verifikasi 2 langkah\",\"2 Facebook Account\",\"Dua Aplikasi\"]','Verifikasi 2 langkah','keamanan','sulit','level1'),(7,'Anda menerima pesan WhatsApp dari nomor tidak dikenal berisi file \"LIHAT-RESI-PAKET.apk\". Apa tindakan paling tepat?','{\"A\":\"Mengunduh dan menginstal file untuk mengecek paket\",\"B\":\"Menghapus pesan dan memblokir pengirim karena file .apk berpotensi mencuri OTP\",\"C\":\"Meneruskan pesan tersebut ke grup keluarga\",\"D\":\"Membalas pesan dengan mengirimkan nomor rekening\"}','B','Phishing Hunter','easy','phishing'),(8,'Email dengan subjek \"AKUN ANDA AKAN DIBLOKIR DALAM 10 MENIT\" berasal dari alamat support@bca-verifikasi-data.xyz. Indikator utama bahwa ini phishing adalah...','{\"A\":\"Menggunakan domain tidak resmi (.xyz) dan menciptakan kepanikan mendesak\",\"B\":\"Menggunakan bahasa Indonesia yang baku\",\"C\":\"Memiliki subjek huruf kapital\",\"D\":\"Dikirim pada jam kerja\"}','A','Phishing Hunter','medium','phishing'),(9,'Serangan spear-phishing yang menyamar sebagai surat resmi kepala instansi dengan lampiran dokumen ber-macro berbahaya paling efektif dicegah dengan...','{\"A\":\"Langsung mengaktifkan macro agar dokumen terbaca\",\"B\":\"Verifikasi silang melalui saluran komunikasi resmi dan menonaktifkan auto-run macro\",\"C\":\"Mengganti wallpaper komputer\",\"D\":\"Mematikan layar monitor sementara\"}','B','Phishing Hunter','hard','phishing'),(10,'Komputer mendadak menampilkan layar terkunci dan meminta tebusan kripto untuk membuka enkripsi file. Serangan ini disebut...','{\"A\":\"Adware\",\"B\":\"Ransomware\",\"C\":\"Defacement\",\"D\":\"Keylogger\"}','B','Cyber Defender','easy','cyber-defender'),(11,'Saat menggunakan Wi-Fi publik di kafe untuk bekerja, pertahanan terbaik agar lalu lintas data tidak disadap (Man-in-the-Middle) adalah...','{\"A\":\"Menggunakan mode penyamaran (Incognito) saja\",\"B\":\"Mengaktifkan koneksi VPN terenkripsi atau menggunakan hotspot pribadi\",\"C\":\"Menurunkan kecerahan layar laptop\",\"D\":\"Menghapus riwayat pencarian browser\"}','B','Cyber Defender','medium','cyber-defender'),(12,'Langkah mitigasi pertama ketika mendeteksi satu perangkat di jaringan kantor terinfeksi worm yang menyebar otomatis adalah...','{\"A\":\"Mengisolasi perangkat dari jaringan (cabut LAN \\/ matikan Wi-Fi) segera\",\"B\":\"Membiarkan perangkat tetap terhubung sambil mengunduh game\",\"C\":\"Membagikan folder kerja ke komputer lain lewat jaringan lokal\",\"D\":\"Menunggu hingga jam pulang kantor\"}','A','Cyber Defender','hard','cyber-defender'),(13,'Manakah di antara alamat URL berikut yang merupakan situs resmi instansi pemerintah Indonesia?','{\"A\":\"https:\\/\\/bantuan-kemensos.blogspot.com\",\"B\":\"https:\\/\\/kemendikdasmen.go.id\",\"C\":\"http:\\/\\/cek-bansos-resmi.my.id\",\"D\":\"https:\\/\\/pendaftaran-cpns.net\"}','B','Link or Trap','easy','link-trap'),(14,'Perhatikan tautan berikut: \"https://klikbca.com.login-session-id.top/auth\". Mengapa tautan tersebut berbahaya?','{\"A\":\"Karena menggunakan protokol HTTPS\",\"B\":\"Domain utamanya sebenarnya adalah login-session-id.top, bukan klikbca.com (Subdomain Trick)\",\"C\":\"Karena terlalu pendek\",\"D\":\"Karena tidak memiliki gambar logo\"}','B','Link or Trap','medium','link-trap'),(15,'Teknik penipuan domain yang mengganti huruf \"l\" kecil dengan angka \"1\" atau huruf Cyrillic serupa seperti \"paypa1.com\" disebut...','{\"A\":\"SQL Injection\",\"B\":\"Typosquatting \\/ Homograph Attack\",\"C\":\"Cross-Site Scripting\",\"D\":\"Brute Force\"}','B','Link or Trap','hard','link-trap'),(16,'Email dari \"ceo-office@perusahaan-anda.co.id\" tetapi header Reply-To mengarah ke \"transfer-cepat@protonmail.com\" meminta pencairan dana darurat. Langkah paling tepat?','{\"A\":\"Segera transfer agar tidak ditegur pimpinan\",\"B\":\"Verifikasi langsung melalui kanal komunikasi internal resmi (telepon kantor) dan tolak balasan ke email eksternal\",\"C\":\"Membalas email dan mengirim foto token perbankan\",\"D\":\"Mengunduh lampiran invoice.iso yang disertakan\"}','B','Phishing Hunter','expert','phishing'),(17,'Terdapat percobaan koneksi keluar mencurigakan dari server internal ke IP asing pada port 4444 (Reverse Shell). Pertahanan apa yang wajib diaktifkan?','{\"A\":\"Membiarkan port tetap terbuka\",\"B\":\"Menonaktifkan log audit server\",\"C\":\"Blokir trafik keluar dengan Firewall (Egress Filtering) & isolasi host\",\"D\":\"Mengabaikan peringatan IDS\"}','C','Cyber Defender','expert','cyber-defender'),(18,'Perhatikan tautan berikut: \"https://sso.purwakartakab.go.id@login-portal-resmi.top/auth\". Bagaimana status keamanan URL tersebut?','{\"A\":\"Aman karena diawali sso.purwakartakab.go.id\",\"B\":\"Trap! Karakter @ membuat browser mengabaikan teks sebelumnya dan mengarahkan ke domain palsu login-portal-resmi.top\",\"C\":\"Aman karena sudah menggunakan protokol HTTPS\",\"D\":\"Merupakan subdomain resmi pemerintah daerah\"}','B','Link or Trap','expert','link-trap'),(19,'Anda menerima undangan rapat kalender otomatis (.ics) yang menyisipkan tautan login OAuth aplikasi pihak ketiga yang meminta izin \"Baca & Kirim Email\". Apa analisis Anda?','{\"A\":\"Serangan Consent Phishing (OAuth Abuse) untuk mengambil alih akses email tanpa mencuri password\",\"B\":\"Fitur standar pembaruan kalender resmi\",\"C\":\"Aman selama tidak meminta nomor kartu kredit\",\"D\":\"Wajib disetujui agar jadwal rapat tersinkronisasi\"}','A','Phishing Hunter','master','phishing'),(20,'Serangan Ransomware Zero-Day mulai mengenkripsi folder bersama melalui protokol SMB di jaringan lokal. Langkah pertahanan darurat paling efektif?','{\"A\":\"Membayar tebusan kripto secepatnya\",\"B\":\"Mematikan monitor komputer saja\",\"C\":\"Putuskan koneksi jaringan (Isolasi Segmen Firewall) & hentikan layanan SMB segera\",\"D\":\"Menghapus seluruh backup offline\"}','C','Cyber Defender','master','cyber-defender'),(21,'Inspeksi URL: \"https://xn--bca-c1a.co.id/ibir/login\" (tampil di beberapa browser sebagai bca dengan titik bawah). Apakah tautan ini aman?','{\"A\":\"Aman karena berakhiran .co.id\",\"B\":\"Trap! Ini adalah serangan IDN Homograph menggunakan Punycode (xn--) untuk meniru domain perbankan\",\"C\":\"Aman karena merupakan server cadangan bank\",\"D\":\"Aman selama diakses melalui jaringan Wi-Fi rumah\"}','B','Link or Trap','master','link-trap');
/*!40000 ALTER TABLE `soal_minigame` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `testimoni`
--

LOCK TABLES `testimoni` WRITE;
/*!40000 ALTER TABLE `testimoni` DISABLE KEYS */;
INSERT INTO `testimoni` VALUES (2,1,'Sangat Membantu Mengenali Link Phishing','Simulasi Phishing Hunter di Digi-Safe membuat saya jauh lebih teliti memeriksa alamat domain sebelum klik tautan di grup WhatsApp warga.',5,'5',0,'approved','2026-04-30 01:14:24'),(6,5,'Edukasi yang Mudah Dipahami Orang Tua','Setelah membaca modul dan berdiskusi di forum, keluarga saya di rumah tidak lagi mudah panik saat menerima telepon yang mengaku petugas bank.',5,'5',0,'approved','2026-08-24 01:31:40'),(7,4,'Berhasil Memulihkan Akun Media Sosial','Panduan dari admin di Forum Komunitas sangat jelas. Saya berhasil mengamankan kembali akun yang sempat terhubung ke web phishing.',5,'5',1,'approved','2026-08-24 01:31:50'),(8,6,'Minigame Seru & Bikin Sadar Siber','Main Link or Trap bareng teman-teman sekolah jadi cara seru belajar bedain domain resmi .go.id dan domain jebakan.',5,'5',0,'approved','2026-08-24 01:31:54'),(9,3,'Ruang Tanya Jawab yang Aman','Fitur anonim dan respons cepat dari relawan membuat saya nyaman menceritakan kronologi penipuan digital tanpa rasa malu.',4,'4',1,'approved','2026-09-01 02:22:54'),(10,9,'testes','testes',4,'4',0,'approved','2026-11-16 23:19:41'),(11,8,'Mantaplahhh!!!!','Mantaplahhh!!!!',5,'5',1,'approved','2026-11-18 02:47:54'),(12,12,'mantappplah pokoknya website ini jadi lebih paham ','mantappplah pokoknya website ini jadi lebih paham mengenai edukasi digital',4,'4',0,'approved','2026-11-19 02:20:38'),(13,13,'ini buatan razief atau **','ini buatan razief atau **',5,'5',1,'approved','2026-11-19 05:55:28');
/*!40000 ALTER TABLE `testimoni` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `testimoni_reactions`
--

LOCK TABLES `testimoni_reactions` WRITE;
/*!40000 ALTER TABLE `testimoni_reactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `testimoni_reactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_badges`
--

LOCK TABLES `user_badges` WRITE;
/*!40000 ALTER TABLE `user_badges` DISABLE KEYS */;
INSERT INTO `user_badges` VALUES (1,1,1,'2026-04-29 03:09:36'),(2,4,1,'2026-08-19 04:58:10'),(3,4,1,'2026-08-19 05:09:02'),(4,1,1,'2026-08-19 08:59:34'),(5,1,1,'2026-08-20 07:45:02'),(6,13,2,'2026-11-19 06:49:16'),(7,13,4,'2026-11-19 06:49:16');
/*!40000 ALTER TABLE `user_badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_daily_streak_logs`
--

LOCK TABLES `user_daily_streak_logs` WRITE;
/*!40000 ALTER TABLE `user_daily_streak_logs` DISABLE KEYS */;
INSERT INTO `user_daily_streak_logs` VALUES (1,1,'2026-11-20','adv_jwt_alg_confusion','B',1,0,'2026-11-20 04:18:58','2026-11-20 04:18:58'),(2,15,'2026-11-20','adv_jwt_alg_confusion','B',1,0,'2026-11-20 05:25:26','2026-11-20 05:25:26');
/*!40000 ALTER TABLE `user_daily_streak_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_daily_streaks`
--

LOCK TABLES `user_daily_streaks` WRITE;
/*!40000 ALTER TABLE `user_daily_streaks` DISABLE KEYS */;
INSERT INTO `user_daily_streaks` VALUES (1,1,1,1,2,1,'active','2026-11-20 04:18:58','2026-11-20','{\"week\":\"2026-W47\",\"days\":{\"5\":\"done\"}}','2026-11-20 04:16:04','2026-11-20 04:18:58'),(2,15,1,1,2,1,'active','2026-11-20 05:25:26','2026-11-20','{\"week\":\"2026-W47\",\"days\":{\"5\":\"done\"}}','2026-11-20 05:24:59','2026-11-20 05:25:26'),(3,3,0,0,2,0,'idle',NULL,NULL,'{\"week\":\"2026-W47\",\"days\":[]}','2026-11-20 05:51:10','2026-11-20 05:51:10');
/*!40000 ALTER TABLE `user_daily_streaks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `user_follows`
--

LOCK TABLES `user_follows` WRITE;
/*!40000 ALTER TABLE `user_follows` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_follows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Razief','raziefafkarruslii@gmail.com','$2y$12$Oq9KeDip14Fhsr0dFIeWDOvtp4x9fJ5S8cfTkcbqxieEk5aQlnNV2','admin','Razief Afkar Rusli','tes tes','images/avatars/avatar_1_1794974482.jpg','images/banners/banner_1_1794974482.gif',NULL,'2026-04-29 02:34:53',NULL,NULL,'google',NULL,'id','light','{\"is_private_profile\":false,\"hide_location\":false,\"hide_email\":false,\"hide_minigame_stats\":false,\"hide_badges\":false,\"hide_activity\":false}','Cyber Guardian','aqua_crest'),(2,'admin','admin@digisafe.id','$2y$12$AfaNpJI3Ud813b46Uq8lg.5InKS1VPMj/z7yq1YptjsgmdRdc5.MO','admin','Administrator',NULL,NULL,NULL,NULL,'2026-04-29 04:16:11',NULL,NULL,'google',NULL,'id','light',NULL,NULL,NULL),(3,'testing','jujurbanyakkepang@gmail.com','$2y$10$GRubiz88sjsilq0w3o/r3OilggV1l1k/ZyX/PvDSBaW3McFx/Z3gm','user','akuntesting',NULL,NULL,NULL,NULL,'2026-04-30 01:55:45',NULL,NULL,'google',NULL,'id','light',NULL,NULL,NULL),(4,'testing123','testes@gmail.com','$2y$10$97ecfoDsXtgWOi9giaWEQON0NxUqY0hPl4W8VSqKn1LDlcwhk6PcO','user','testes1212','hhhhh','assets/uploads/avatar_4_1787532549.png',NULL,NULL,'2026-07-27 02:57:31',NULL,NULL,'google',NULL,'id','light',NULL,NULL,NULL),(5,'Fairuz','fairuzradhwa@gmail.com','$2y$10$0LGg3Senx09nWQYXwOtMduCxe7L.E0MbtrV9mOHwl2Hb2HmbQAEd6','user','Fairuz.R.Z',NULL,NULL,NULL,NULL,'2026-08-20 03:58:27',NULL,NULL,'google',NULL,'id','light',NULL,NULL,NULL),(6,'raziefrpl','raziefrpl@gmail.com','$2y$12$VmUf8iVTzL1Ym5d0RqIPuOdB9UqL5GmCYgOiUQAG/7EHFcVZvKlOi','user','Razief RPL',NULL,'https://lh3.googleusercontent.com/a/ACg8ocJ1WySMoYnEOEOUx5fcvR3rOykmOvs8XJkFRl4FX4KEzvHEyw=s96-c',NULL,NULL,'2026-08-31 01:41:42','2TTSQ','2026-09-09 09:03:09','google','104300402400830202938','id','light','{\"google_onboarded\":true}',NULL,NULL),(7,'aokaroti48','aokaroti48@gmail.com','$2y$12$rv0fSR/kg.kTv7Z6yUq4.e1.xlrOXCrM7zWo7xRCx4/76kmCaSJuC','user','roti aoka','','assets/uploads/avatar_7_1788140584.png',NULL,NULL,'2026-08-31 01:42:02',NULL,NULL,'google','101344524501404074519','id','light',NULL,NULL,NULL),(8,'Raziefff','fadlanhidayat@gmail.com','$2y$12$mBO2SZyRjFifURb/aejCgOrO353DSCrP2jACPi0Jc0A/AcF5BAzNW','user','Testing 123',NULL,'images/avatars/avatar_8_1794899841.jpeg','images/banners/banner_8_1794901709.png',NULL,'2026-11-17 04:23:48',NULL,NULL,'google',NULL,'id','light',NULL,NULL,NULL),(9,'fff','flyxmusaigen@gmail.com','$2y$12$dfgh304/YLcM8tv4r1i2KujUGi03ALiif6m2lI.VOh/0u5bPLMyyO','user','Raziefaaa',NULL,NULL,NULL,NULL,'2026-11-17 06:05:34',NULL,NULL,'google',NULL,'id','light',NULL,NULL,NULL),(10,'Razief Afkar Rusli','owner@digisafe.id','$2y$12$jcrl.nXBpJkUadrxefLXIer0lOAWYdiN9qzRuy8eHpPIzne/1dd3W','owner','Razief Afkar Rusli',NULL,NULL,NULL,NULL,'2026-11-17 06:36:03',NULL,NULL,'google',NULL,'id','light',NULL,NULL,NULL),(11,'bot_digika','bot.digika@digisafe.id','$2y$12$gd9U6wcsjLa1nQwk/H5jB.ITa9DFOKk4P2jtVjRxIIK5mydmgwJAK','user','Bot Digika','Asisten sistem otomatis Digi-Safe Purwakarta yang menyampaikan hasil evaluasi usulan tema edukasi.','images/bot-digika.jpg',NULL,NULL,'2026-11-18 03:56:13',NULL,NULL,'google',NULL,'id','light',NULL,'Sistem Otomatis',NULL),(12,'anjaygayot','anjaygayot@gmail.com','$2y$12$7KvukBihqr0iLLl/rxSaoetI6fCYeN3zn4lqihTneDK1yBqKSm6wS','user','anjay gayot',NULL,'https://lh3.googleusercontent.com/a/ACg8ocIWx2eADRSQXYuLtrK7Z1GK8KcAjYz7VucLdjbd_kqthWLuVA=s96-c',NULL,NULL,'2026-11-19 02:20:08',NULL,NULL,'google','105242003685073811235','en','light',NULL,NULL,'astral_orbit'),(13,'aninyc','anindrnsa@gmail.com','$2y$12$Q65mxssYobzWoKDs/ZMr2uy0E1jTo4J539ZNgKZJ4PrI.kDSzvwhO','user','Annida Khoerunnisa','Hi! hit me up!',NULL,NULL,'[\"Speed Runner (<10d)\",\"Phishing Hunter\"]','2026-11-19 05:52:18',NULL,NULL,'google',NULL,'id','dark',NULL,'Digital Educator',NULL),(14,'myllorevol','myllorevol@gmail.com','$2y$12$2vWxfWzGW5E5kzFI1SraoO5OghPnA.dagDwmxdz3MoHGRkzamitn.','user','myllorevol',NULL,'https://lh3.googleusercontent.com/a/ACg8ocKzKBdd3-0dEN-9DNxMHmaVweMVYEyP_lX1onNTT94ezAK0Aw=s96-c',NULL,NULL,'2026-11-20 00:51:13',NULL,NULL,'google','108789974780751557456','en','dark','{\"google_onboarded\":true}',NULL,NULL),(15,'rplfadlan','rplfadlan@gmail.com','$2y$12$7sy7lWxHKgAwn1m/JL.8DusgHqDjoXpP18ybg1CFQrB2VHbrrs6ba','user','Fadlan RPL',NULL,'https://lh3.googleusercontent.com/a/ACg8ocJWN-WXgsn87Yz8eQWnBkVGMom0nGZrR5-xAlQCOF4O39YMXw=s96-c',NULL,NULL,'2026-11-20 00:54:39',NULL,NULL,'google','102963534020006010658','id','light','{\"google_onboarded\":true}',NULL,NULL),(16,'admin_ukk','ukk.admin@digisafe.id','$2y$12$Gpf0bbCkvKnXKcJOB9hXguupC03oUOKhh/JmztDqi/fF9KpKxeNOu','admin','Admin Penguji UKK','Akun demo Administrator untuk pengujian UKK RPL Digi-Safe Purwakarta.',NULL,NULL,NULL,'2026-11-23 02:17:38',NULL,NULL,'google',NULL,'id','light',NULL,NULL,NULL),(17,'warga_ukk','ukk.warga@gmail.com','$2y$12$ESWJ2zDHCTh1ioEXTI5rbuQiodWZpBzM9vIi4P6..qAO6tQUQiy7y','user','Warga Demo UKK','Akun demo Pengguna (Warga Purwakarta) untuk pengujian UKK RPL Digi-Safe Purwakarta.',NULL,NULL,NULL,'2026-11-23 02:17:38',NULL,NULL,'google',NULL,'id','light',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-23  9:17:55

SET FOREIGN_KEY_CHECKS=1;
