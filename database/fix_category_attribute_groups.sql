-- Fix category_attribute_groups system
-- This script fixes the database schema and ensures proper functionality

-- Step 1: Make category_id nullable in category_attributes table
-- This allows attributes to belong to groups only (without requiring category_id)
ALTER TABLE category_attributes 
MODIFY COLUMN category_id INT NULL;

-- Step 2: Ensure category_attribute_groups table exists with correct structure
-- (This should already exist, but we'll verify the structure is correct)
-- The table should have:
-- - mapping_id (PK)
-- - category_id (FK to categories)
-- - group_id (FK to attribute_groups)
-- - is_inherited (BOOLEAN)
-- - UNIQUE constraint on (category_id, group_id)

-- Step 3: Add index for better performance on inheritance queries
-- Note: If the index already exists, this will show an error but won't break the migration
-- You can safely ignore the error if the index already exists
CREATE INDEX idx_category_inherited ON category_attribute_groups(category_id, is_inherited);

-- Note: After running this migration:
-- 1. Attributes can now be created with only group_id (category_id can be NULL)
-- 2. The inheritance system will work properly
-- 3. Child categories will automatically inherit parent's groups when reading

