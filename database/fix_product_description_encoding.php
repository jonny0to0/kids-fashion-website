<?php
/**
 * Fix Product Description Encoding
 * 
 * This script fixes existing product descriptions that have been HTML-escaped multiple times.
 * It decodes HTML entities to restore the original raw text content.
 * 
 * IMPORTANT: 
 * - Run this script ONCE to fix existing data
 * - After running, product descriptions will be stored as raw text
 * - HTML escaping will only happen at output time using htmlspecialchars()
 * 
 * Usage:
 * php database/fix_product_description_encoding.php
 * OR access via browser: http://your-domain/database/fix_product_description_encoding.php
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

// Get database connection
try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== Product Description Encoding Fix Script ===\n\n";
    echo "This script will fix existing product descriptions that have been HTML-escaped multiple times.\n";
    echo "It will decode HTML entities to restore the original raw text content.\n\n";
    
    // Count products with descriptions
    $countSql = "SELECT COUNT(*) as total FROM products WHERE description IS NOT NULL AND description != ''";
    $countStmt = $db->query($countSql);
    $totalProducts = $countStmt->fetch()['total'] ?? 0;
    
    echo "Total products with descriptions: {$totalProducts}\n\n";
    
    if ($totalProducts == 0) {
        echo "No products with descriptions found. Nothing to fix.\n";
        exit(0);
    }
    
    // Fetch all products with descriptions
    $sql = "SELECT product_id, name, description, short_description FROM products WHERE description IS NOT NULL AND description != ''";
    $stmt = $db->query($sql);
    $products = $stmt->fetchAll();
    
    $fixedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;
    
    echo "Processing products...\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($products as $product) {
        $productId = $product['product_id'];
        $productName = $product['name'];
        $originalDescription = $product['description'];
        $originalShortDescription = $product['short_description'] ?? '';
        
        // Decode HTML entities multiple times to handle multiple escaping
        // We'll decode until the string no longer changes (fixes any level of double-encoding)
        $decodedDescription = $originalDescription;
        $decodedShortDescription = $originalShortDescription;
        
        // Decode description
        $previousDescription = '';
        while ($decodedDescription !== $previousDescription) {
            $previousDescription = $decodedDescription;
            $decodedDescription = html_entity_decode($decodedDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        // Decode short description
        if (!empty($decodedShortDescription)) {
            $previousShortDescription = '';
            while ($decodedShortDescription !== $previousShortDescription) {
                $previousShortDescription = $decodedShortDescription;
                $decodedShortDescription = html_entity_decode($decodedShortDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        
        // Only update if something changed
        if ($decodedDescription !== $originalDescription || $decodedShortDescription !== $originalShortDescription) {
            try {
                $updateSql = "UPDATE products SET description = :description, short_description = :short_description WHERE product_id = :product_id";
                $updateStmt = $db->prepare($updateSql);
                $updateStmt->execute([
                    ':description' => $decodedDescription,
                    ':short_description' => !empty($decodedShortDescription) ? $decodedShortDescription : null,
                    ':product_id' => $productId
                ]);
                
                $fixedCount++;
                echo "✓ Fixed: [ID: {$productId}] {$productName}\n";
                
                // Show before/after for first few products
                if ($fixedCount <= 3) {
                    echo "  Before: " . substr($originalDescription, 0, 100) . (strlen($originalDescription) > 100 ? '...' : '') . "\n";
                    echo "  After:  " . substr($decodedDescription, 0, 100) . (strlen($decodedDescription) > 100 ? '...' : '') . "\n\n";
                }
            } catch (PDOException $e) {
                $errorCount++;
                echo "✗ Error fixing product ID {$productId}: " . $e->getMessage() . "\n";
            }
        } else {
            $skippedCount++;
            // Only show skipped products if they're in first few
            if ($skippedCount <= 3) {
                echo "○ Skipped: [ID: {$productId}] {$productName} (already correct)\n";
            }
        }
    }
    
    echo str_repeat("-", 80) . "\n";
    echo "\n=== Summary ===\n";
    echo "Total products processed: {$totalProducts}\n";
    echo "Products fixed: {$fixedCount}\n";
    echo "Products skipped (already correct): {$skippedCount}\n";
    if ($errorCount > 0) {
        echo "Errors encountered: {$errorCount}\n";
    }
    echo "\n✓ Cleanup completed successfully!\n";
    echo "\nIMPORTANT NOTES:\n";
    echo "- Product descriptions are now stored as RAW text in the database\n";
    echo "- HTML escaping happens only at output time using htmlspecialchars()\n";
    echo "- This prevents double-encoding issues like &amp;amp; appearing\n";
    echo "- Future product saves will automatically store raw text\n";
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

