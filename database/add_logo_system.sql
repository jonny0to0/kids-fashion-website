-- Logo System Migration
-- Adds dual logo type support (Image & Text) with dimension controls

-- Add logo type and related settings
INSERT INTO `settings` (`key`, `value`, `group`, `type`, `is_encrypted`, `description`) VALUES
-- Logo Type Selection
('logo_type', 'text', 'general', 'select', 0, 'Logo Type (image or text)'),

-- Image Logo Settings
('logo_image', '', 'general', 'file', 0, 'Logo Image File'),
('logo_image_max_height', '60', 'general', 'number', 0, 'Logo Maximum Height (px)'),
('logo_image_max_width', '200', 'general', 'number', 0, 'Logo Maximum Width (px)'),

-- Text Logo Settings
('logo_text', '', 'general', 'text', 0, 'Logo Text (Store Name)'),
('logo_text_font_size_sidebar', '20', 'general', 'number', 0, 'Logo Text Font Size - Sidebar (px)'),
('logo_text_font_size_header', '18', 'general', 'number', 0, 'Logo Text Font Size - Header (px)'),
('logo_text_font_weight', '600', 'general', 'number', 0, 'Logo Text Font Weight (100-900)'),
('logo_text_color', '#ffffff', 'general', 'text', 0, 'Logo Text Color (hex)'),
('logo_text_max_width', '200', 'general', 'number', 0, 'Logo Text Maximum Width (px)')

ON DUPLICATE KEY UPDATE `key`=`key`;

-- Migrate existing dashboard_logo to logo_image if it exists
-- Note: This migration is handled in PHP code for better compatibility
-- The settings model will check dashboard_logo as fallback if logo_image is empty

-- Update existing dashboard_logo description (keep it for backward compatibility)
UPDATE `settings` SET `description` = 'Dashboard Logo (Legacy - use logo_image instead)' WHERE `key` = 'dashboard_logo';

