<?php
/**
 * Maintenance Mode Test Script
 * 
 * Run this script to test if maintenance mode is working correctly
 * Usage: php test_maintenance_mode.php
 */

// Define base paths
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load configuration
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/config/constants.php';
require_once APP_PATH . '/config/database.php';

// Load required classes
require_once APP_PATH . '/models/Settings.php';
require_once APP_PATH . '/helpers/MaintenanceMode.php';

echo "=== Maintenance Mode Test ===\n\n";

// Test 1: Check if Settings model works
echo "1. Testing Settings Model...\n";
try {
    $settings = new Settings();
    echo "   ✓ Settings model loaded successfully\n";
} catch (Exception $e) {
    echo "   ✗ Settings model failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check current maintenance mode status
echo "\n2. Checking current maintenance mode status...\n";
try {
    $maintenanceInfo = MaintenanceMode::isEnabled();
    echo "   Current status: " . ($maintenanceInfo['enabled'] ? 'ENABLED' : 'DISABLED') . "\n";
    if (isset($maintenanceInfo['error'])) {
        echo "   ⚠ Error: " . $maintenanceInfo['error'] . "\n";
    }
    echo "   Message: " . ($maintenanceInfo['message'] ?? 'N/A') . "\n";
    echo "   ETA: " . ($maintenanceInfo['eta'] ?? 'N/A') . "\n";
} catch (Exception $e) {
    echo "   ✗ Failed to check maintenance mode: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Check database value directly
echo "\n3. Checking database value directly...\n";
try {
    $rawValue = $settings->get('maintenance_mode_enabled', false);
    echo "   Raw value from database: " . var_export($rawValue, true) . "\n";
    echo "   Value type: " . gettype($rawValue) . "\n";
    
    // Check what's actually in the database
    $db = $settings->db ?? null;
    if ($db) {
        $stmt = $db->prepare("SELECT `key`, `value`, `type` FROM settings WHERE `key` = 'maintenance_mode_enabled'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "   Database row: " . json_encode($row) . "\n";
        } else {
            echo "   ⚠ Setting not found in database! Run the migration: database/add_maintenance_mode_system.sql\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Failed to check database: " . $e->getMessage() . "\n";
}

// Test 4: Test enabling maintenance mode
echo "\n4. Testing maintenance mode enable/disable...\n";
try {
    // Get current status
    $wasEnabled = $maintenanceInfo['enabled'];
    
    // Toggle it
    if ($wasEnabled) {
        echo "   Maintenance is currently enabled. Testing disable...\n";
        $result = MaintenanceMode::disable();
        echo "   Disable result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
    } else {
        echo "   Maintenance is currently disabled. Testing enable...\n";
        $result = MaintenanceMode::enable(
            message: 'Test maintenance mode',
            reason: 'Testing',
            eta: '5 minutes',
            autoDisableHours: 1
        );
        echo "   Enable result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
    }
    
    // Check new status
    sleep(1); // Small delay to ensure database is updated
    $newInfo = MaintenanceMode::isEnabled();
    echo "   New status: " . ($newInfo['enabled'] ? 'ENABLED' : 'DISABLED') . "\n";
    
    // Restore original state
    if ($wasEnabled) {
        MaintenanceMode::enable(
            message: $maintenanceInfo['message'],
            reason: $maintenanceInfo['reason'],
            eta: $maintenanceInfo['eta']
        );
    } else {
        MaintenanceMode::disable();
    }
    echo "   ✓ Original state restored\n";
    
} catch (Exception $e) {
    echo "   ✗ Failed to test enable/disable: " . $e->getMessage() . "\n";
}

// Test 5: Check if maintenance page would be shown
echo "\n5. Testing maintenance page display logic...\n";
try {
    $testInfo = MaintenanceMode::isEnabled();
    if ($testInfo['enabled']) {
        echo "   ✓ Maintenance mode is enabled - maintenance page SHOULD be shown to non-admin users\n";
        echo "   ✓ Admin users SHOULD still have access to /admin routes\n";
    } else {
        echo "   ✓ Maintenance mode is disabled - normal site access\n";
    }
} catch (Exception $e) {
    echo "   ✗ Failed to test display logic: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nNext steps:\n";
echo "1. If maintenance mode is not working, check:\n";
echo "   - Database migration was run: database/add_maintenance_mode_system.sql\n";
echo "   - maintenance_mode_enabled setting exists in database\n";
echo "   - Value is '1' (string) or 1 (int) when enabled\n";
echo "   - Check error logs for MaintenanceMode errors\n";
echo "\n2. To enable maintenance mode:\n";
echo "   - Go to /admin/settings?section=maintenance\n";
echo "   - Click 'Enable Maintenance' button\n";
echo "   - Fill in message and ETA\n";
echo "   - Click 'Save Changes'\n";
echo "\n3. To test as non-admin user:\n";
echo "   - Open incognito/private browser window\n";
echo "   - Visit the site (should see maintenance page)\n";
echo "\n4. To test as admin:\n";
echo "   - Log in as admin\n";
echo "   - Visit /admin routes (should work normally)\n";

