-- SQL Script to Fix Product Description Encoding
-- 
-- This script can be run directly in MySQL/MariaDB to fix double-escaped product descriptions.
-- It uses MySQL's built-in functions to decode HTML entities.
-- 
-- IMPORTANT: This is a fallback method. The PHP script (fix_product_description_encoding.php)
-- is recommended as it handles multiple levels of encoding more reliably.
--
-- Usage:
-- mysql -u your_username -p your_database < database/fix_product_description_encoding.sql

USE kids_bazaar;

-- Create a function to decode HTML entities (MySQL doesn't have this built-in)
-- We'll use REPLACE for common entities
-- Note: This is a simplified version. The PHP script handles this better.

-- Fix description field
UPDATE products 
SET description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
    description,
    '&amp;', '&'
), '&lt;', '<'
), '&gt;', '>'
), '&quot;', '"'
), '&#039;', "'"
), '&amp;amp;', '&'
), '&amp;amp;amp;', '&')
WHERE description LIKE '%&amp;%' 
   OR description LIKE '%&lt;%' 
   OR description LIKE '%&gt;%';

-- Fix short_description field
UPDATE products 
SET short_description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
    short_description,
    '&amp;', '&'
), '&lt;', '<'
), '&gt;', '>'
), '&quot;', '"'
), '&#039;', "'"
), '&amp;amp;', '&'
), '&amp;amp;amp;', '&')
WHERE short_description LIKE '%&amp;%' 
   OR short_description LIKE '%&lt;%' 
   OR short_description LIKE '%&gt;%';

-- Verify the changes
SELECT 
    product_id,
    name,
    LEFT(description, 100) as description_preview,
    LEFT(short_description, 100) as short_description_preview
FROM products 
WHERE description LIKE '%&amp;%' 
   OR short_description LIKE '%&amp;%'
LIMIT 10;

-- Note: This SQL approach has limitations:
-- 1. It only handles basic HTML entities
-- 2. It doesn't handle multiple levels of encoding well
-- 3. The PHP script is more comprehensive and recommended

