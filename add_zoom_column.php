<?php
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM product_images LIKE 'zoom_image_url'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $sql = "ALTER TABLE product_images ADD COLUMN zoom_image_url VARCHAR(255) DEFAULT NULL";
        $db->exec($sql);
        echo "Successfully added 'zoom_image_url' column to 'product_images' table.\n";
    } else {
        echo "Column 'zoom_image_url' already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
