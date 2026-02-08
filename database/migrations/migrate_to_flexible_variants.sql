-- Migration to Flexible Variant Architecture "Option A"

-- 1. Create variant_attributes table
CREATE TABLE IF NOT EXISTS variant_attributes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    variant_id INT NOT NULL,
    attribute_name VARCHAR(50) NOT NULL, -- e.g., "Size", "Color"
    attribute_value VARCHAR(100) NOT NULL, -- e.g., "M", "Red"
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id) ON DELETE CASCADE,
    UNIQUE KEY idx_variant_attribute (variant_id, attribute_name),
    INDEX idx_variant (variant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrate existing Size data
INSERT IGNORE INTO variant_attributes (variant_id, attribute_name, attribute_value)
SELECT variant_id, 'Size', size
FROM product_variants
WHERE size IS NOT NULL AND size != '';

-- 3. Migrate existing Color data
INSERT IGNORE INTO variant_attributes (variant_id, attribute_name, attribute_value)
SELECT variant_id, 'Color', color
FROM product_variants
WHERE color IS NOT NULL AND color != '';

-- 4. Alter product_variants table
-- First, ensure new columns exist (if running idempotently)
ALTER TABLE product_variants
ADD COLUMN is_default BOOLEAN DEFAULT FALSE;

-- Drop old columns (SAFE MODE: Commented out for now, manually run this if migration is verified)
-- ALTER TABLE product_variants
-- DROP COLUMN size,
-- DROP COLUMN color,
-- DROP COLUMN color_code,
-- DROP COLUMN additional_price;

-- 5. Add attributes_snapshot to order_items
ALTER TABLE order_items
ADD COLUMN attributes_snapshot TEXT COMMENT 'JSON snapshot of variant attributes at purchase time';

-- 6. Helper: Ensure products with no variants have a default variant?
-- This is complex in SQL only. Better handled by PHP script or manual migration.
-- For now, we assume existing products without variants are "simple products" and logic handles them,
-- OR we create a default variant for them.
-- Strategy: If product has NO rows in product_variants, create one with 'Default' attribute?
-- Skipped for now to avoid accidental data bloat. Logic will handle "no variants" as "use base product".
