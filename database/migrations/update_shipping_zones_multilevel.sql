ALTER TABLE `shipping_zones`
ADD COLUMN `zone_type` ENUM('pin', 'pin_range', 'district', 'state', 'country') NOT NULL DEFAULT 'country' AFTER `zone_name`,
ADD COLUMN `locations` TEXT NULL COMMENT 'JSON array or comma-separated list of locations' AFTER `zone_type`,
ADD COLUMN `priority` INT NOT NULL DEFAULT 0 AFTER `locations`,
ADD COLUMN `country_code` CHAR(2) NOT NULL DEFAULT 'IN' AFTER `priority`;

-- Optional: Index on priority and type for faster matching
CREATE INDEX `idx_zone_priority_type` ON `shipping_zones` (`priority` DESC, `zone_type`);
