-- SQL Script to Convert Products Table to utf8mb4
-- 
-- This ensures proper storage of special characters like &, @, #, %, and emojis.
-- 
-- Usage:
-- mysql -u your_username -p your_database < database/convert_products_to_utf8mb4.sql

USE kids_bazaar;

-- Convert products table to utf8mb4
ALTER TABLE products 
CONVERT TO CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Verify the conversion
SELECT 
    TABLE_COLLATION,
    TABLE_CHARSET
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'products';

-- Check description column specifically
SELECT 
    COLUMN_NAME,
    CHARACTER_SET_NAME,
    COLLATION_NAME
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'products' 
AND COLUMN_NAME IN ('description', 'short_description', 'name');

