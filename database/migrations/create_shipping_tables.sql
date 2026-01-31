CREATE TABLE IF NOT EXISTS `shipping_zones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `zone_name` VARCHAR(255) NOT NULL,
  `regions` TEXT NULL COMMENT 'JSON array of regions',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `delivery_methods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `zone_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `type` ENUM('flat_rate', 'free_shipping', 'weight_based', 'price_based') NOT NULL DEFAULT 'flat_rate',
  `cost` DECIMAL(10, 2) DEFAULT 0.00,
  `condition_min` DECIMAL(10, 2) DEFAULT NULL,
  `condition_max` DECIMAL(10, 2) DEFAULT NULL,
  `estimated_days` VARCHAR(50) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`zone_id`) REFERENCES `shipping_zones`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
