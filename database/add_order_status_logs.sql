-- Order Status Logs Table
-- Tracks all order status changes for audit purposes

CREATE TABLE IF NOT EXISTS order_status_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    changed_by INT NULL COMMENT 'User ID of admin who made the change (NULL for system)',
    old_status VARCHAR(50) NULL COMMENT 'Previous order status',
    new_status VARCHAR(50) NOT NULL COMMENT 'New order status',
    old_payment_status VARCHAR(50) NULL COMMENT 'Previous payment status',
    new_payment_status VARCHAR(50) NULL COMMENT 'New payment status',
    notes TEXT NULL COMMENT 'Optional notes about the change',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

