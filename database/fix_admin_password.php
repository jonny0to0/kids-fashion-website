<?php
/**
 * Admin Password Fix Script
 * Run this script to fix the admin password in the database
 * 
 * Usage: php database/fix_admin_password.php
 */

// Load configuration files
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/constants.php';
require_once __DIR__ . '/../app/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Correct password hash for 'admin123'
    $correctHash = '$2y$10$QuRsy8diRPO3ziCrUmlRnuxV4PNEY7VAtxCmcsYmzHl0EFx5i415C';
    
    // Update admin password
    $stmt = $db->prepare("
        UPDATE users 
        SET password = ?, 
            status = ?, 
            email_verified = ?,
            updated_at = NOW()
        WHERE email = ? AND user_type = ?
    ");
    
    $result = $stmt->execute([
        $correctHash,
        USER_STATUS_ACTIVE,
        1, // TRUE
        'admin@kidsbazaar.com',
        USER_TYPE_ADMIN
    ]);
    
    if ($result) {
        $affectedRows = $stmt->rowCount();
        
        if ($affectedRows > 0) {
            echo "✓ Admin password updated successfully!\n";
            echo "  Email: admin@kidsbazaar.com\n";
            echo "  Password: admin123\n";
            echo "  Status: Active\n\n";
            
            // Verify the update
            $verifyStmt = $db->prepare("
                SELECT user_id, email, user_type, status, email_verified 
                FROM users 
                WHERE email = ? AND user_type = ?
            ");
            $verifyStmt->execute(['admin@kidsbazaar.com', USER_TYPE_ADMIN]);
            $user = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo "Verification:\n";
                echo "  User ID: {$user['user_id']}\n";
                echo "  Email: {$user['email']}\n";
                echo "  Type: {$user['user_type']}\n";
                echo "  Status: {$user['status']}\n";
                echo "  Email Verified: " . ($user['email_verified'] ? 'Yes' : 'No') . "\n";
                
                // Test password verification
                $testStmt = $db->prepare("SELECT password FROM users WHERE email = ?");
                $testStmt->execute(['admin@kidsbazaar.com']);
                $userData = $testStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userData && password_verify('admin123', $userData['password'])) {
                    echo "\n✓ Password verification test: PASSED\n";
                } else {
                    echo "\n✗ Password verification test: FAILED\n";
                }
            }
        } else {
            echo "⚠ No admin user found to update.\n";
            echo "  Make sure the admin user exists in the database.\n";
        }
    } else {
        echo "✗ Failed to update admin password.\n";
        $error = $stmt->errorInfo();
        echo "  Error: " . $error[2] . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

