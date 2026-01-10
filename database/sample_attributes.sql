-- Sample Category Attributes
-- Run this after running add_attributes_system.sql
-- Replace category IDs with your actual category IDs

-- Example: Add attributes for Footwear category
-- First, find your Footwear category ID:
-- SELECT category_id, name, slug FROM categories WHERE slug LIKE '%footwear%' OR name LIKE '%footwear%';

-- Then replace the category_id in the INSERT statements below

-- Example for Footwear (replace CATEGORY_ID with actual ID):
/*
INSERT INTO category_attributes (category_id, attribute_name, attribute_type, attribute_options, is_required, display_order) VALUES
((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Size', 'select', '["6","7","8","9","10","11","12"]', TRUE, 1),
((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Color', 'select', '["Black","White","Brown","Blue","Red","Pink"]', TRUE, 2),
((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Material', 'select', '["Leather","Canvas","Synthetic","Mesh"]', FALSE, 3),
((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Gender', 'select', '["Men","Women","Unisex"]', FALSE, 4),
((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Sole Type', 'text', NULL, FALSE, 5),
((SELECT category_id FROM categories WHERE slug = 'footwear' LIMIT 1), 'Occasion', 'select', '["Casual","Sports","Formal"]', FALSE, 6);
*/

-- Example for Dresses (replace CATEGORY_ID with actual ID):
/*
INSERT INTO category_attributes (category_id, attribute_name, attribute_type, attribute_options, is_required, display_order) VALUES
((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Size', 'select', '["XS","S","M","L","XL"]', TRUE, 1),
((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Color', 'select', '["Red","Blue","Green","Yellow","Pink","White","Black"]', TRUE, 2),
((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Fabric', 'select', '["Cotton","Silk","Polyester","Linen","Chiffon"]', FALSE, 3),
((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Fit', 'select', '["Slim","Regular","Loose"]', FALSE, 4),
((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Sleeve Type', 'select', '["Full","Half","Sleeveless"]', FALSE, 5),
((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Length', 'select', '["Short","Knee-length","Full-length"]', FALSE, 6),
((SELECT category_id FROM categories WHERE slug = 'dresses' LIMIT 1), 'Occasion', 'select', '["Party","Casual","Formal"]', FALSE, 7);
*/

-- To add attributes for any category, use this template:
/*
INSERT INTO category_attributes (category_id, attribute_name, attribute_type, attribute_options, is_required, display_order) VALUES
(YOUR_CATEGORY_ID, 'Attribute Name', 'select', '["Option1","Option2","Option3"]', TRUE, 1);
*/

-- Attribute Types:
-- 'text' - Simple text input
-- 'select' - Dropdown with options (requires attribute_options as JSON array)
-- 'number' - Number input
-- 'textarea' - Multi-line text input
-- 'color' - Color picker with text input

