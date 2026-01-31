<?php
// Define app path
define('APP_PATH', __DIR__ . '/../app');
define('ENVIRONMENT', 'development'); // Assuming dev environment for migration

// Require database config
require_once APP_PATH . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Create user_status_history table
    $sql = "CREATE TABLE IF NOT EXISTS user_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        old_status VARCHAR(50),
        new_status VARCHAR(50) NOT NULL,
        reason_code VARCHAR(50),
        reason_text TEXT,
        suspended_by_admin_id INT,
        evidence_reference TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql);
    echo "Successfully created user_status_history table.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
