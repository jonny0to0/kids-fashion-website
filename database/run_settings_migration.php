<?php
/**
 * Settings Migration Runner
 * Run this file to create the settings table and insert default values
 * 
 * Usage: Navigate to http://localhost/kid-bazar-ecom/database/run_settings_migration.php
 * Or run via command line: php database/run_settings_migration.php
 */

// Load database configuration
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Starting Settings Migration...\n\n";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/add_settings_system.sql';
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Split by semicolon, but keep multi-line statements together
    $statements = [];
    $currentStatement = '';
    
    foreach (explode("\n", $sql) as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $currentStatement .= $line . "\n";
        
        // If line ends with semicolon, it's a complete statement
        if (substr(rtrim($line), -1) === ';') {
            $stmt = trim($currentStatement);
            if (!empty($stmt) && strlen($stmt) > 10) { // Minimum meaningful statement
                $statements[] = $stmt;
            }
            $currentStatement = '';
        }
    }
    
    // Add any remaining statement
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strlen($statement) < 10) {
            continue;
        }
        
        try {
            // Remove trailing semicolon if present
            $statement = rtrim($statement, ';');
            
            $db->exec($statement);
            $successCount++;
            
            // Show progress
            if (stripos($statement, 'INSERT') !== false) {
                preg_match('/INSERT INTO `?(\w+)`?/i', $statement, $matches);
                $tableName = $matches[1] ?? 'table';
                echo "✓ Inserted into {$tableName}\n";
            } elseif (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches);
                $tableName = $matches[1] ?? 'table';
                echo "✓ Created table: {$tableName}\n";
            }
        } catch (PDOException $e) {
            $errorCount++;
            // Check if it's a "table already exists" error (which is OK)
            if (strpos($e->getMessage(), 'already exists') !== false || 
                strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "⚠ " . $e->getMessage() . " (skipping)\n";
                $errorCount--; // Don't count as error
            } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "⚠ Duplicate entry (skipping)\n";
                $errorCount--; // Don't count as error
            } else {
                echo "✗ Error: " . $e->getMessage() . "\n";
                echo "  Statement: " . substr($statement, 0, 150) . "...\n";
            }
        }
    }
    
    echo "\n";
    echo "========================================\n";
    echo "Migration Complete!\n";
    echo "Successful statements: {$successCount}\n";
    if ($errorCount > 0) {
        echo "Errors: {$errorCount}\n";
    }
    echo "========================================\n";
    echo "\n";
    echo "You can now access the Settings page at:\n";
    echo "http://localhost/kid-bazar-ecom/public/admin/settings\n";
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "\n";
    echo "========================================\n";
    echo "Migration Failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "========================================\n";
    exit(1);
}

