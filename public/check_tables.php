<?php
define('APP_PATH', __DIR__ . '/../app');
require_once APP_PATH . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found:\n";
    foreach ($tables as $table) {
        echo "- " . $table . "\n";
    }
    
    // Check if product_sales_summary exists and describe it
    if (in_array('product_sales_summary', $tables)) {
        echo "\nStructure of product_sales_summary:\n";
        $columns = $db->query("DESCRIBE product_sales_summary")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo $col['Field'] . " " . $col['Type'] . "\n";
        }
    } else {
        echo "\nproduct_sales_summary table does NOT exist.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
