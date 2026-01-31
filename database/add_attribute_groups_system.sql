-- Add Attribute Groups System with Category Inheritance
-- This implements a scalable attribute system where:
-- 1. Attributes belong to Attribute Groups
-- 2. Categories are assigned Attribute Groups (not individual attributes)
-- 3. Child categories inherit parent's attribute groups

-- Step 1: Create Attribute Groups Table
CREATE TABLE IF NOT EXISTS attribute_groups (
    group_id INT PRIMARY KEY AUTO_INCREMENT,
    group_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Create Category-Attribute Group Mapping Table
-- This allows many-to-many relationship: Categories can have multiple groups, groups can be assigned to multiple categories
CREATE TABLE IF NOT EXISTS category_attribute_groups (
    mapping_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    group_id INT NOT NULL,
    is_inherited BOOLEAN DEFAULT FALSE, -- TRUE if inherited from parent, FALSE if directly assigned
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES attribute_groups(group_id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_group (category_id, group_id),
    INDEX idx_category (category_id),
    INDEX idx_group (group_id),
    INDEX idx_inherited (is_inherited)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 3: Modify category_attributes table to link to attribute_groups instead of categories
-- Add group_id column to category_attributes
ALTER TABLE category_attributes 
ADD COLUMN group_id INT NULL AFTER category_id,
ADD FOREIGN KEY (group_id) REFERENCES attribute_groups(group_id) ON DELETE CASCADE,
ADD INDEX idx_group (group_id);

-- Step 4: Insert Default Attribute Groups
INSERT INTO attribute_groups (group_name, description, display_order) VALUES
('Common', 'Common attributes used across all product categories (Brand, SKU, Price, Stock)', 1),
('Fashion Basic', 'Basic fashion attributes (Color, Size, Fabric)', 2),
('Footwear Specs', 'Footwear-specific attributes (Shoe Size, Sole Material)', 3),
('Electronics Specs', 'Electronics-specific attributes (RAM, Storage, Battery)', 4),
('Shipping', 'Shipping-related attributes (Weight, Dimensions)', 5)
ON DUPLICATE KEY UPDATE group_name=group_name;

-- Step 5: Migrate existing category_attributes to use groups
-- This creates a default "Common" group for existing attributes
-- You may need to manually reassign attributes to appropriate groups after migration
UPDATE category_attributes ca
LEFT JOIN attribute_groups ag ON ag.group_name = 'Common'
SET ca.group_id = ag.group_id
WHERE ca.group_id IS NULL;

-- Note: After migration, you should:
-- 1. Review existing attributes and assign them to appropriate groups
-- 2. Assign attribute groups to categories using category_attribute_groups table
-- 3. Remove the direct category_id link from category_attributes (optional, for cleaner design)

-- Example: Assign "Common" and "Fashion Basic" groups to Fashion category
-- (Replace category_id with actual Fashion category ID)
-- INSERT INTO category_attribute_groups (category_id, group_id, is_inherited) VALUES
-- ((SELECT category_id FROM categories WHERE slug = 'fashion' LIMIT 1), 
--  (SELECT group_id FROM attribute_groups WHERE group_name = 'Common' LIMIT 1), FALSE),
-- ((SELECT category_id FROM categories WHERE slug = 'fashion' LIMIT 1), 
--  (SELECT group_id FROM attribute_groups WHERE group_name = 'Fashion Basic' LIMIT 1), FALSE);

