<?php
define('APP_PATH', __DIR__ . '/../app');
require_once APP_PATH . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS product_sales_summary (
        product_id INT PRIMARY KEY,
        total_quantity INT DEFAULT 0,
        total_revenue DECIMAL(10, 2) DEFAULT 0.00,
        last_sale_date DATETIME NULL,
        calculation_period ENUM('30_days', 'all_time') DEFAULT '30_days',
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_excluded BOOLEAN DEFAULT 0,
        is_pinned BOOLEAN DEFAULT 0,
        manual_rank INT DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'product_sales_summary' created successfully (or already exists).\n";
    
    // Check if table exists to be sure
    $tables = $db->query("SHOW TABLES LIKE 'product_sales_summary'")->fetchAll();
    if (count($tables) > 0) {
        echo "Verification: Table exists.\n";
    } else {
        echo "Verification: Table NOT found!\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
