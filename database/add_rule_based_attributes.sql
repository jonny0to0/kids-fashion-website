-- Add Rule-Based Attributes System
-- This allows attributes to show/hide based on parent attribute values (like Flipkart)
-- Example: "Grip Type" shows only when "Shoe Type" = "Sports"

-- Add new columns to category_attributes table
ALTER TABLE category_attributes
ADD COLUMN IF NOT EXISTS depends_on INT NULL COMMENT 'Parent attribute ID that this attribute depends on',
ADD COLUMN IF NOT EXISTS show_when TEXT NULL COMMENT 'JSON condition: {"value": "Sports"} or {"operator": "in", "values": ["Sports", "Formal"]}',
ADD COLUMN IF NOT EXISTS is_filterable BOOLEAN DEFAULT FALSE COMMENT 'Whether this attribute can be used for filtering products',
ADD COLUMN IF NOT EXISTS is_variant BOOLEAN DEFAULT FALSE COMMENT 'Whether this attribute is used for product variants',
ADD INDEX idx_depends_on (depends_on),
ADD FOREIGN KEY (depends_on) REFERENCES category_attributes(attribute_id) ON DELETE SET NULL;

-- Update existing attributes to have default values
UPDATE category_attributes 
SET is_filterable = 0, is_variant = 0 
WHERE is_filterable IS NULL OR is_variant IS NULL;


