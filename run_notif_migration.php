<?php
// Define app path
define('APP_PATH', __DIR__ . '/app');
define('ENVIRONMENT', 'development'); // Assuming dev

// Require necessary files
require_once APP_PATH . '/config/config.php';
// We need to load database config. Model.php includes it, so if I include Model.php or just Database.php...
// Let's assume database.php defines DB constants or Database class uses constants from config.php?
// Wait, Model.php has `require_once APP_PATH . '/config/database.php';`
// Check where Database class is. It's usually in `app/core/Database.php` or `app/config/database.php` might be just config array? 
// Let's look for Database class file. I'll search for it if I don't find it. 
// But based on Model.php: `require_once APP_PATH . '/config/database.php';` and `Database::getInstance()`.
// So `app/config/database.php` likely contains the class definition or includes it. 

// Let's verify location of Database class first to be sure.
if (file_exists(APP_PATH . '/core/Database.php')) {
    require_once APP_PATH . '/core/Database.php';
} elseif (file_exists(APP_PATH . '/config/database.php')) {
    require_once APP_PATH . '/config/database.php';
}

if (!class_exists('Database')) {
    die("Database class not found.");
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Read SQL file
    $sqlFile = __DIR__ . '/database/migrations/update_notifications_event_driven.sql';
    if (!file_exists($sqlFile)) {
        die("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon usually, but if simple statements we can run one by one if preferred, or just run query if driver supports multiple queries.
    // PDO might not support multiple queries in one go depending on config.
    // Let's try splitting by `;` followed by newline to be safe-ish, or just simple split. 
    // The migration has comments and newlines.
    
    // Basic SQL splitter
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            echo "Executing: " . substr($stmt, 0, 50) . "...\n";
            try {
                $db->exec($stmt);
            } catch (PDOException $e) {
                // Ignore "Duplicate column name" error if we re-run
                if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                   echo " - Skipped (Column already exists)\n";
                } elseif (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                    echo " - Skipped (Index already exists)\n";
                } else {
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "Migration completed successfully.\n";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
