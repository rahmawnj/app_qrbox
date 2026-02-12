
CREATE TABLE IF NOT EXISTS `bypass_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `device_id` bigint unsigned NOT NULL,
  `type` enum('bypass','session') COLLATE utf8mb4_unicode_ci NOT NULL,
  `bypass_activation` timestamp NULL DEFAULT NULL COMMENT 'Waktu alat diaktifkan/dibypass',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Catatan tambahan untuk bypass',
  `bypass_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'off',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bypass_records_device_id_foreign` (`device_id`),
  CONSTRAINT `bypass_records_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.bypass_records: ~0 rows (approximately)
DELETE FROM `bypass_records`;

-- Dumping structure for table qrbox_app.cashiers
CREATE TABLE IF NOT EXISTS `cashiers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `outlet_id` bigint unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cashiers_user_id_foreign` (`user_id`),
  KEY `cashiers_outlet_id_foreign` (`outlet_id`),
  CONSTRAINT `cashiers_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cashiers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.cashiers: ~0 rows (approximately)
DELETE FROM `cashiers`;

-- Dumping structure for table qrbox_app.devices
CREATE TABLE IF NOT EXISTS `devices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outlet_id` bigint unsigned NOT NULL,
  `device_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'off',
  `service_type_id` bigint unsigned DEFAULT NULL,
  `bypass_activation` timestamp NULL DEFAULT NULL,
  `bypass_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_1` json DEFAULT NULL,
  `option_2` json DEFAULT NULL,
  `option_3` json DEFAULT NULL,
  `option_4` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `devices_code_unique` (`code`),
  KEY `devices_outlet_id_foreign` (`outlet_id`),
  KEY `devices_service_type_id_foreign` (`service_type_id`),
  CONSTRAINT `devices_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `devices_service_type_id_foreign` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.devices: ~1 rows (approximately)
DELETE FROM `devices`;
INSERT INTO `devices` (`id`, `name`, `code`, `outlet_id`, `device_status`, `service_type_id`, `bypass_activation`, `bypass_note`, `option_1`, `option_2`, `option_3`, `option_4`, `created_at`, `updated_at`) VALUES
	(1, 'Main Machine Laundry', 'DEV-WHNTZR', 1, 'off', 1, NULL, NULL, '{"name": "Laundry Menu 1", "type": "washer", "price": 9000, "active": true, "duration": 0, "description": "75"}', '{"name": "Laundry Menu 2", "type": "dryer_a", "price": 20000, "active": true, "duration": 33, "description": "75"}', '{"name": "Laundry Menu 3", "type": "dryer_b", "price": 15000, "active": true, "duration": 112, "description": "75"}', '{"name": "Laundry Menu 4", "type": "none", "price": 0, "active": false, "duration": 0, "description": "-"}', '2026-02-11 08:14:48', '2026-02-11 08:14:48');

-- Dumping structure for table qrbox_app.device_service_type
CREATE TABLE IF NOT EXISTS `device_service_type` (
  `device_id` bigint unsigned NOT NULL,
  `service_type_id` bigint unsigned NOT NULL,
  `price` int NOT NULL DEFAULT '0',
  UNIQUE KEY `device_service_type_device_id_service_type_id_unique` (`device_id`,`service_type_id`),
  KEY `device_service_type_service_type_id_foreign` (`service_type_id`),
  CONSTRAINT `device_service_type_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `device_service_type_service_type_id_foreign` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.device_service_type: ~0 rows (approximately)
DELETE FROM `device_service_type`;

-- Dumping structure for table qrbox_app.device_transactions
CREATE TABLE IF NOT EXISTS `device_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned DEFAULT NULL,
  `owner_id` bigint unsigned NOT NULL,
  `outlet_id` bigint unsigned NOT NULL,
  `device_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Service type: washer atau dryer',
  `activated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `bypass_activation` timestamp NULL DEFAULT NULL COMMENT 'Waktu perangkat diaktifkan secara bypass',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_transactions_transaction_id_foreign` (`transaction_id`),
  KEY `device_transactions_owner_id_foreign` (`owner_id`),
  KEY `device_transactions_outlet_id_foreign` (`outlet_id`),
  CONSTRAINT `device_transactions_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `device_transactions_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  CONSTRAINT `device_transactions_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.device_transactions: ~0 rows (approximately)
DELETE FROM `device_transactions`;

-- Dumping structure for table qrbox_app.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.failed_jobs: ~0 rows (approximately)
DELETE FROM `failed_jobs`;

-- Dumping structure for table qrbox_app.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.migrations: ~0 rows (approximately)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
	(3, '2014_10_12_100000_create_password_resets_table', 1),
	(4, '2019_08_19_000000_create_failed_jobs_table', 1),
	(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(6, '2023_10_21_155446_create_owners_table', 1),
	(7, '2023_10_21_155449_create_outlets_table', 1),
	(8, '2023_10_21_155450_create_cashiers_table', 1),
	(9, '2025_03_04_140228_add_role_and_permissions_to_users_table', 1),
	(10, '2025_03_05_161928_create_transactions_table', 1),
	(11, '2025_03_05_161929_create_payments_table', 1),
	(12, '2025_03_05_161929_create_qris_transactions_table', 1),
	(13, '2025_03_09_141554_create_service_types_table', 1),
	(14, '2025_03_09_141556_create_devices_table', 1),
	(15, '2025_03_26_142514_create_bypass_records_table', 1),
	(16, '2025_05_30_141005_create_device_service_type_table', 1),
	(17, '2025_06_10_140354_create_withdrawals_table', 1),
	(18, '2025_06_24_111832_create_device_transactions_table', 1),
	(19, '2025_07_17_131059_create_self_service_transactions_table', 1),
	(20, '2025_08_13_132658_create_notifications_table', 1);

-- Dumping structure for table qrbox_app.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.notifications: ~0 rows (approximately)
DELETE FROM `notifications`;

-- Dumping structure for table qrbox_app.outlets
CREATE TABLE IF NOT EXISTS `outlets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint unsigned DEFAULT NULL,
  `outlet_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `device_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_fee_percentage` decimal(4,3) NOT NULL DEFAULT '0.100',
  `min_monthly_service_fee` decimal(12,2) NOT NULL DEFAULT '100000.00',
  `device_deposit_price` decimal(15,2) NOT NULL DEFAULT '500000.00' COMMENT 'Harga jaminan (deposit) per unit perangkat yang dibebankan kepada outlet saat registrasi device baru.',
  `timezone` enum('Asia/Jakarta','Asia/Makassar','Asia/Jayapura') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Jakarta' COMMENT 'Indonesian Time Zones: Asia/Jakarta (Western Indonesian Time), Asia/Makassar (Central Indonesian Time), Asia/Jayapura (Eastern Indonesian Time)',
  `latlong` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `outlets_code_unique` (`code`),
  KEY `outlets_owner_id_foreign` (`owner_id`),
  CONSTRAINT `outlets_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.outlets: ~1 rows (approximately)
DELETE FROM `outlets`;
INSERT INTO `outlets` (`id`, `owner_id`, `outlet_name`, `image`, `city_name`, `code`, `address`, `status`, `device_token`, `service_fee_percentage`, `min_monthly_service_fee`, `device_deposit_price`, `timezone`, `latlong`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Tazaka - Bandung H Gofur', NULL, 'Bandung', 'OUT-GOFUR', 'Jl. H. Gofur, Bandung', 1, NULL, 0.100, 100000.00, 500000.00, 'Asia/Jakarta', '"{\\"lat\\":-6.9175,\\"lon\\":107.6191}"', '2026-02-11 08:14:48', '2026-02-11 08:14:48');

-- Dumping structure for table qrbox_app.owners
CREATE TABLE IF NOT EXISTS `owners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_description` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `contract_start` date DEFAULT NULL COMMENT 'Tanggal mulai kontrak kerjasama',
  `contract_end` date DEFAULT NULL COMMENT 'Tanggal berakhir kontrak kerjasama',
  `contract_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor dokumen kontrak fisik/digital',
  `deposit_amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Jumlah deposit/jaminan dari owner',
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama bank untuk penarikan dana',
  `bank_account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor rekening bank untuk penarikan dana',
  `bank_account_holder_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama pemilik rekening bank untuk penarikan dana',
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Saldo bersih owner yang siap dicairkan',
  `receipt_config` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `owners_code_unique` (`code`),
  UNIQUE KEY `owners_contract_number_unique` (`contract_number`),
  KEY `owners_user_id_foreign` (`user_id`),
  CONSTRAINT `owners_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.owners: ~1 rows (approximately)
DELETE FROM `owners`;
INSERT INTO `owners` (`id`, `brand_name`, `brand_logo`, `brand_phone`, `brand_description`, `user_id`, `status`, `contract_start`, `contract_end`, `contract_number`, `deposit_amount`, `bank_name`, `bank_account_number`, `bank_account_holder_name`, `balance`, `receipt_config`, `created_at`, `updated_at`, `deleted_at`, `code`) VALUES
	(1, 'Tazaka', NULL, '08123456789', 'Company Tazaka Default', 1, 1, '2026-02-11', '2027-02-11', 'CONT/2024/TAZAKA', 0.00, 'BCA', '1234567890', 'RAHMA TAZAKA', 0.00, NULL, '2026-02-11 08:14:48', '2026-02-11 08:14:48', NULL, 'BR-TZK');

-- Dumping structure for table qrbox_app.password_resets
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.password_resets: ~0 rows (approximately)
DELETE FROM `password_resets`;

-- Dumping structure for table qrbox_app.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.password_reset_tokens: ~0 rows (approximately)
DELETE FROM `password_reset_tokens`;

-- Dumping structure for table qrbox_app.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `outlet_id` bigint unsigned NOT NULL,
  `owner_id` bigint unsigned NOT NULL,
  `amount` int NOT NULL,
  `payment_time` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `timezone` enum('Asia/Jakarta','Asia/Makassar','Asia/Jayapura') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Jakarta' COMMENT 'Indonesian Time Zones: Asia/Jakarta (Western Indonesian Time), Asia/Makassar (Central Indonesian Time), Asia/Jayapura (Eastern Indonesian Time)',
  `service_fee_amount` int NOT NULL COMMENT 'Nilai potongan (gross - net)',
  `service_fee_percentage` decimal(4,3) NOT NULL COMMENT 'Persentase fee saat transaksi terjadi (misal 0.100)',
  PRIMARY KEY (`id`),
  KEY `payments_transaction_id_foreign` (`transaction_id`),
  KEY `payments_outlet_id_foreign` (`outlet_id`),
  KEY `payments_owner_id_foreign` (`owner_id`),
  CONSTRAINT `payments_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.payments: ~0 rows (approximately)
DELETE FROM `payments`;

-- Dumping structure for table qrbox_app.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.personal_access_tokens: ~0 rows (approximately)
DELETE FROM `personal_access_tokens`;

-- Dumping structure for table qrbox_app.qris_transactions
CREATE TABLE IF NOT EXISTS `qris_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transactionable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transactionable_id` bigint unsigned NOT NULL,
  `payment_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `qris_transactions_transactionable_type_transactionable_id_index` (`transactionable_type`,`transactionable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.qris_transactions: ~0 rows (approximately)
DELETE FROM `qris_transactions`;

-- Dumping structure for table qrbox_app.self_service_transactions
CREATE TABLE IF NOT EXISTS `self_service_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `outlet_id` bigint unsigned DEFAULT NULL,
  `owner_id` bigint unsigned DEFAULT NULL,
  `device_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: Belum Aktif/Gagal, 1: Berhasil Aktif',
  `last_attempt_at` timestamp NULL DEFAULT NULL COMMENT 'Waktu terakhir mencoba aktivasi ke IoT',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `self_service_transactions_transaction_id_unique` (`transaction_id`),
  KEY `self_service_transactions_owner_id_foreign` (`owner_id`),
  KEY `self_service_transactions_outlet_id_foreign` (`outlet_id`),
  CONSTRAINT `self_service_transactions_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `self_service_transactions_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE,
  CONSTRAINT `self_service_transactions_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.self_service_transactions: ~0 rows (approximately)
DELETE FROM `self_service_transactions`;

-- Dumping structure for table qrbox_app.service_types
CREATE TABLE IF NOT EXISTS `service_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `items` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_types_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.service_types: ~3 rows (approximately)
DELETE FROM `service_types`;
INSERT INTO `service_types` (`id`, `name`, `items`, `created_at`, `updated_at`) VALUES
	(1, 'Laundry', '[{"key": "washer", "label": "Washer", "has_duration": false}, {"key": "dryer_a", "label": "Dryer A", "has_duration": true}, {"key": "dryer_b", "label": "Dryer B", "has_duration": true}]', '2026-02-11 08:14:46', '2026-02-11 08:14:46'),
	(2, 'Turnstile', '[{"key": "turnstile", "label": "Turnstile Gate", "has_duration": false}]', '2026-02-11 08:14:46', '2026-02-11 08:14:46'),
	(3, 'Dispenser', '[{"key": "dispenser_a", "label": "Dispenser A", "has_duration": true}, {"key": "dispenser_b", "label": "Dispenser B", "has_duration": true}, {"key": "dispenser_c", "label": "Dispenser C", "has_duration": true}, {"key": "dispenser_d", "label": "Dispenser D", "has_duration": true}]', '2026-02-11 08:14:46', '2026-02-11 08:14:46');

-- Dumping structure for table qrbox_app.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint unsigned NOT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` int NOT NULL COMMENT 'Harga bersih yang diterima owner setelah dipotong fee',
  `type` enum('payment','withdrawal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'payment',
  `gross_amount` int NOT NULL COMMENT 'Harga kotor/asli sebelum dipotong fee',
  `service_fee_amount` int NOT NULL COMMENT 'Nilai potongan (gross - net)',
  `service_fee_percentage` decimal(4,3) NOT NULL COMMENT 'Persentase fee saat transaksi terjadi (misal 0.100)',
  `timezone` enum('Asia/Jakarta','Asia/Makassar','Asia/Jayapura') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Jakarta' COMMENT 'Indonesian Time Zones: Asia/Jakarta (Western Indonesian Time), Asia/Makassar (Central Indonesian Time), Asia/Jayapura (Eastern Indonesian Time)',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Catatan tambahan atau alasan penolakan penarikan',
  PRIMARY KEY (`id`),
  KEY `transactions_owner_id_foreign` (`owner_id`),
  CONSTRAINT `transactions_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.transactions: ~0 rows (approximately)
DELETE FROM `transactions`;

-- Dumping structure for table qrbox_app.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('owner') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'owner',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table qrbox_app.users: ~1 rows (approximately)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `name`, `image`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Rahma', NULL, 'rahma@tazaka.com', NULL, '$2y$10$YfDPPqDlpmFgT5hgSuCn5uiFjQigHy2IQrW/tvplEzjRYS/gV/auK', 'owner', NULL, '2026-02-11 08:14:48', '2026-02-11 08:14:48');

-- Dumping structure for table qrbox_app.withdrawals
CREATE TABLE IF NOT EXISTS `withdrawals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint unsigned NOT NULL,
  `amount` int NOT NULL COMMENT 'Total dengan biaya penarikan',
  `requested_amount` int NOT NULL COMMENT 'Jumlah yang diminta untuk ditarik',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama bank tujuan penarikan',
  `bank_account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor rekening tujuan',
  `bank_account_holder_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama pemilik rekening tujuan',
  `amount_before_fee` int DEFAULT NULL COMMENT 'Saldo sebelum dipotong biaya',
  `withdrawal_fee` int DEFAULT NULL COMMENT 'Biaya admin penarikan',
  `amount_after_fee` int DEFAULT NULL COMMENT 'Jumlah yang ditransfer setelah dipotong',
  PRIMARY KEY (`id`),
  KEY `withdrawals_owner_id_foreign` (`owner_id`),
  CONSTRAINT `withdrawals_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DELETE FROM `withdrawals`;
