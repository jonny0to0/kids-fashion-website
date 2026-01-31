-- Add Dynamic Attributes System for Products
-- This allows category-based product attributes and variants

-- Category Attributes Table
-- Defines which attributes are available for each category
CREATE TABLE IF NOT EXISTS category_attributes (
    attribute_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    attribute_name VARCHAR(100) NOT NULL,
    attribute_type ENUM('text', 'select', 'number', 'textarea', 'color') DEFAULT 'text',
    attribute_options TEXT NULL, -- JSON array for select type: ["Option1", "Option2"]
    is_required BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Attributes Table
-- Stores the actual attribute values for each product
CREATE TABLE IF NOT EXISTS product_attributes (
    product_attribute_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    attribute_id INT NOT NULL,
    attribute_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES category_attributes(attribute_id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_attribute (product_id, attribute_id),
    INDEX idx_product (product_id),
    INDEX idx_attribute (attribute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update product_variants table to support more flexible variant management
-- Add index for better performance
ALTER TABLE product_variants 
ADD INDEX idx_active (is_active),
ADD INDEX idx_size_color (size, color);

-- Insert sample attributes for Footwear category (example)
-- Note: Replace category_id with actual Footwear category ID from your database
-- INSERT INTO category_attributes (category_id, attribute_name, attribute_type, attribute_options, is_required, display_order) VALUES
-- ((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Size', 'select', '["6","7","8","9","10","11","12"]', TRUE, 1),
-- ((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Color', 'select', '["Black","White","Brown","Blue","Red"]', TRUE, 2),
-- ((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Material', 'select', '["Leather","Canvas","Synthetic","Mesh"]', FALSE, 3),
-- ((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Gender', 'select', '["Men","Women","Unisex"]', FALSE, 4),
-- ((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Sole Type', 'text', NULL, FALSE, 5),
-- ((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Occasion', 'select', '["Casual","Sports","Formal"]', FALSE, 6);

-- Insert sample attributes for Dresses category (example)
-- Note: Replace category_id with actual Dresses category ID from your database
-- INSERT INTO category_attributes (category_id, attribute_name, attribute_type, attribute_options, is_required, display_order) VALUES
-- ((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Size', 'select', '["XS","S","M","L","XL"]', TRUE, 1),
-- ((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Color', 'select', '["Red","Blue","Green","Yellow","Pink","White","Black"]', TRUE, 2),
-- ((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Fabric', 'select', '["Cotton","Silk","Polyester","Linen","Chiffon"]', FALSE, 3),
-- ((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Fit', 'select', '["Slim","Regular","Loose"]', FALSE, 4),
-- ((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Sleeve Type', 'select', '["Full","Half","Sleeveless"]', FALSE, 5),
-- ((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Length', 'select', '["Short","Knee-length","Full-length"]', FALSE, 6),
-- ((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Occasion', 'select', '["Party","Casual","Formal"]', FALSE, 7);

