
CREATE DATABASE IF NOT EXISTS `disaster_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `disaster_db`;

CREATE TABLE IF NOT EXISTS `disasters` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `type` ENUM('flood','earthquake','typhoon','landslide','accident') NOT NULL,
  `occurred_at` DATETIME NOT NULL,
  `intensity_signal` VARCHAR(100) NOT NULL,
  `latitude` DECIMAL(10,6) NOT NULL,
  `longitude` DECIMAL(10,6) NOT NULL,
  `radius_m` INT UNSIGNED NOT NULL DEFAULT 250,
  `address` VARCHAR(255) NULL,
  `description` VARCHAR(1000) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_occurred_at` (`occurred_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
