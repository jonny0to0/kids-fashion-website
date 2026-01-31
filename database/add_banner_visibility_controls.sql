-- Add visibility control fields to hero_banners table
-- Allows admin to enable/disable banner content and images independently
-- Note: Run this migration only once. If columns already exist, you'll get an error which you can safely ignore.

ALTER TABLE hero_banners 
ADD COLUMN content_enabled BOOLEAN DEFAULT TRUE COMMENT 'Enable/disable banner content (title, description, CTA)',
ADD COLUMN image_enabled BOOLEAN DEFAULT TRUE COMMENT 'Enable/disable banner images';

-- Update existing banners to have both enabled by default
UPDATE hero_banners SET content_enabled = TRUE WHERE content_enabled IS NULL;
UPDATE hero_banners SET image_enabled = TRUE WHERE image_enabled IS NULL;

