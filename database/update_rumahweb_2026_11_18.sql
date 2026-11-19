-- Update SQL tambahan untuk RumahWeb / Localhost (aman dijalankan berulang kali)
CREATE TABLE IF NOT EXISTS `edukasi_requests` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `friendships` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `requester_id` bigint(20) unsigned NOT NULL,
  `addressee_id` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `friendships_requester_id_addressee_id_unique` (`requester_id`,`addressee_id`),
  KEY `friendships_addressee_id_status_index` (`addressee_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_messages` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_follows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `follower_id` bigint(20) unsigned NOT NULL,
  `following_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_follows_follower_id_following_id_unique` (`follower_id`,`following_id`),
  KEY `user_follows_following_id_index` (`following_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
