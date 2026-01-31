-- Maintenance Mode System Migration
-- Creates settings for maintenance mode control and management

-- Add maintenance mode settings (using existing settings table)
INSERT INTO `settings` (`key`, `value`, `group`, `type`, `is_encrypted`, `description`) VALUES
('maintenance_mode_enabled', '0', 'maintenance', 'checkbox', 0, 'Enable Maintenance Mode'),
('maintenance_message', 'We\'re performing scheduled maintenance to improve performance and security. We\'ll be back shortly!', 'maintenance', 'textarea', 0, 'Maintenance Message'),
('maintenance_reason', 'Scheduled maintenance', 'maintenance', 'text', 0, 'Non-Technical Reason (shown to users)'),
('maintenance_eta', '', 'maintenance', 'text', 0, 'Expected Completion Time (e.g., "2 hours", "30 minutes")'),
('maintenance_start_time', '', 'maintenance', 'datetime', 0, 'Maintenance Start Time'),
('maintenance_end_time', '', 'maintenance', 'datetime', 0, 'Expected Maintenance End Time'),
('maintenance_auto_disable_after', '0', 'maintenance', 'number', 0, 'Auto-disable after X hours (0 = disabled)'),
('maintenance_status_page_url', '', 'maintenance', 'text', 0, 'External Status Page URL (optional)'),
('maintenance_support_email', '', 'maintenance', 'email', 0, 'Support Email for Maintenance Period (falls back to support_email)'),
('maintenance_allowed_ips', '', 'maintenance', 'text', 0, 'Comma-separated IP addresses allowed during maintenance (optional)')

ON DUPLICATE KEY UPDATE 
  `value` = VALUES(`value`),
  `description` = VALUES(`description`);

-- Note: The maintenance_mode_enabled setting controls the mode
-- When enabled:
-- - All non-admin users see the maintenance page
-- - HTTP 503 status is returned
-- - Retry-After header is set based on maintenance_end_time
-- - Admin users with valid session can still access /admin routes

