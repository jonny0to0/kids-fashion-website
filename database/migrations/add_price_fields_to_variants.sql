-- Add price (MRP) and sale_price (Selling Price) to product_variants table
ALTER TABLE product_variants
ADD COLUMN price DECIMAL(10, 2) DEFAULT NULL AFTER color_code,
ADD COLUMN sale_price DECIMAL(10, 2) DEFAULT NULL AFTER price;
