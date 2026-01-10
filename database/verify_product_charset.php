<?php
/**
 * Verify and Fix Product Table Charset
 * 
 * This script verifies that the products table uses utf8mb4 charset
 * and converts it if necessary.
 * 
 * Usage:
 * php database/verify_product_charset.php
 * OR access via browser: http://your-domain/database/verify_product_charset.php
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== Product Table Charset Verification ===\n\n";
    
    // Check current charset of products table
    $sql = "SELECT 
                TABLE_COLLATION 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'products'";
    $stmt = $db->query($sql);
    $result = $stmt->fetch();
    
    if (!$result) {
        echo "ERROR: Products table not found!\n";
        exit(1);
    }
    
    $currentCollation = $result['TABLE_COLLATION'] ?? '';
    echo "Current table collation: {$currentCollation}\n";
    
    // Check description column charset
    $sql = "SELECT 
                CHARACTER_SET_NAME, 
                COLLATION_NAME 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'products' 
            AND COLUMN_NAME = 'description'";
    $stmt = $db->query($sql);
    $columnInfo = $stmt->fetch();
    
    if ($columnInfo) {
        echo "Description column charset: " . ($columnInfo['CHARACTER_SET_NAME'] ?? 'N/A') . "\n";
        echo "Description column collation: " . ($columnInfo['COLLATION_NAME'] ?? 'N/A') . "\n\n";
    }
    
    $targetCollation = 'utf8mb4_unicode_ci';
    $targetCharset = 'utf8mb4';
    
    // Check if conversion is needed
    if (strpos($currentCollation, 'utf8mb4') === 0) {
        echo "✓ Table is already using utf8mb4 charset. No conversion needed.\n";
        exit(0);
    }
    
    echo "⚠ Table is not using utf8mb4 charset. Conversion needed.\n\n";
    echo "This script will convert the products table to utf8mb4_unicode_ci.\n";
    echo "This ensures proper storage of special characters like &, @, #, %, etc.\n\n";
    
    // Convert table to utf8mb4
    echo "Converting table...\n";
    $convertSql = "ALTER TABLE products 
                   CONVERT TO CHARACTER SET {$targetCharset} 
                   COLLATE {$targetCollation}";
    
    try {
        $db->exec($convertSql);
        echo "✓ Table successfully converted to {$targetCharset} with {$targetCollation} collation.\n";
        
        // Verify conversion
        $sql = "SELECT TABLE_COLLATION 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'products'";
        $stmt = $db->query($sql);
        $newResult = $stmt->fetch();
        $newCollation = $newResult['TABLE_COLLATION'] ?? '';
        
        echo "\nVerification:\n";
        echo "New table collation: {$newCollation}\n";
        
        if (strpos($newCollation, 'utf8mb4') === 0) {
            echo "✓ Conversion successful!\n";
        } else {
            echo "⚠ Warning: Collation doesn't match expected value.\n";
        }
        
    } catch (PDOException $e) {
        echo "✗ Error converting table: " . $e->getMessage() . "\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

