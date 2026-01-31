-- Add Shipping Fields to Orders Table
-- Adds tracking_id, courier_partner, estimated_delivery, and delivery_type fields

ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS tracking_id VARCHAR(100) NULL COMMENT 'Courier tracking ID',
ADD COLUMN IF NOT EXISTS courier_partner VARCHAR(100) NULL COMMENT 'Courier/Shipping partner name',
ADD COLUMN IF NOT EXISTS estimated_delivery DATE NULL COMMENT 'Estimated delivery date',
ADD COLUMN IF NOT EXISTS delivery_type ENUM('standard', 'express') DEFAULT 'standard' COMMENT 'Delivery type: standard or express',
ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(255) NULL COMMENT 'Payment transaction ID';

-- Add indexes for better query performance
CREATE INDEX IF NOT EXISTS idx_tracking_id ON orders(tracking_id);
CREATE INDEX IF NOT EXISTS idx_delivery_type ON orders(delivery_type);
CREATE INDEX IF NOT EXISTS idx_created_at ON orders(created_at);

