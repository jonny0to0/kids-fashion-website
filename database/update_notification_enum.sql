ALTER TABLE notifications MODIFY COLUMN type ENUM('order', 'promotion', 'system', 'product', 'support', 'account') DEFAULT 'system';
