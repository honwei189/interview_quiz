drop table if exists rewards;
CREATE TABLE `rewards` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `order_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `points` SMALLINT UNSIGNED DEFAULT 0,
  `redeemed` SMALLINT UNSIGNED DEFAULT 0,
  `available` SMALLINT UNSIGNED DEFAULT 0,
  `is_redeemed` TINYINT(1) UNSIGNED DEFAULT 0,
  `status` varchar(1) NOT NULL DEFAULT 'A',
  `redeemed_at` datetime DEFAULT '0000-00-00 00:00:00',
  `expiry_at` datetime DEFAULT '0000-00-00 00:00:00',
  `created_at` datetime NOT NULL DEFAULT NOW(),
  `created_by` INT UNSIGNED NOT NULL,
  `updated_at` datetime DEFAULT '0000-00-00 00:00:00',
  `updated_by` INT UNSIGNED DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `rewards_ukey` (`id`),
  INDEX `rewards_idx` (`id`, `order_id`, `user_id`),
  INDEX `rewards_idx2` (`is_redeemed`, `expiry_at`, `available`)
) ENGINE = InnoDB;

drop table if exists redeemed_logs;
CREATE TABLE `redeemed_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `order_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `rewards_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `redeemed` SMALLINT UNSIGNED DEFAULT 0,
  `available` SMALLINT UNSIGNED DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT NOW(),
  `created_by` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `redeemed_logs_ukey` (`id`),
  INDEX `redeemed_logs_idx` (`id`, `order_id`, `rewards_id`),
  INDEX `redeemed_logs_idx2` (`user_id`)
) ENGINE = InnoDB;

drop table if exists payments;
CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `order_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `base_amount` DECIMAL(8, 2) UNSIGNED DEFAULT 0,
  `usd_amount` DECIMAL(8, 2) UNSIGNED DEFAULT 0,
  `redeemed_currency` VARCHAR(3) DEFAULT 'USD',
  `redeemed_points` SMALLINT UNSIGNED DEFAULT 0,
  `redeemed_amount` DECIMAL(8, 2) UNSIGNED DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT NOW(),
  `created_by` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `payments_ukey` (`id`),
  INDEX `payments_idx` (`id`, `user_id`, `order_id`)
) ENGINE = InnoDB;



INSERT INTO `rewards` (`id`, `user_id`, `order_id`, `points`, `redeemed`, `available`, `is_redeemed`, `status`, `redeemed_at`, `expiry_at`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
	(1, 1, 0, 30, 0, 30, 0, 'A', '0000-00-00 00:00:00', '2023-08-02 20:29:45', '2022-08-02 20:29:31', 999999, '0000-00-00 00:00:00', 0),
	(2, 1, 1, 11, 0, 11, 0, 'A', '0000-00-00 00:00:00', '2023-08-02 20:43:52', '2022-08-02 20:43:52', 999999, '0000-00-00 00:00:00', 0),
	(3, 1, 11, 31, 0, 31, 0, 'A', '0000-00-00 00:00:00', '2023-08-03 20:52:38', '2022-08-03 18:52:38', 999999, '0000-00-00 00:00:00', 0);
