<?php
require_once '/var/www/ecom.dev.syntrex.io/app/config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
    $tableExists = $stmt->rowCount() > 0;
    echo $tableExists ? "Table 'notifications' exists." : "Table 'notifications' DOES NOT exist.";
    
    if ($tableExists) {
        echo "\nColumns:\n";
        $stmt = $db->query("DESCRIBE notifications");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
