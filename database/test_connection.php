<?php
/**
 * Database Connection Test Script
 * Run this file to diagnose database connection issues
 * 
 * Usage: Navigate to http://localhost/kid-bazar-ecom/database/test_connection.php
 */

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Load configuration
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .test-item { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; background: #f9f9f9; }
        .success { border-left-color: #4CAF50; background: #e8f5e9; }
        .error { border-left-color: #f44336; background: #ffebee; }
        .warning { border-left-color: #ff9800; background: #fff3e0; }
        .info { border-left-color: #2196F3; background: #e3f2fd; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        .status { font-weight: bold; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Connection Diagnostic Test</h1>
        
        <?php
        $tests = [];
        
        // Test 1: Check if Database class exists
        $tests[] = [
            'name' => 'Database Class Available',
            'status' => class_exists('Database') ? 'success' : 'error',
            'message' => class_exists('Database') ? 'Database class is loaded' : 'Database class not found'
        ];
        
        // Test 2: Check database configuration
        $tests[] = [
            'name' => 'Database Configuration',
            'status' => 'info',
            'message' => sprintf(
                "Host: %s<br>Database: %s<br>Username: %s<br>Password: %s",
                defined('DB_HOST') ? DB_HOST : 'localhost (default)',
                defined('DB_NAME') ? DB_NAME : 'kids_bazaar (default)',
                defined('DB_USER') ? DB_USER : 'root (default)',
                defined('DB_PASS') ? (strlen(DB_PASS) > 0 ? '***' : 'empty') : 'empty (default)'
            )
        ];
        
        // Test 3: Try to get database instance
        try {
            $dbInstance = Database::getInstance();
            $tests[] = [
                'name' => 'Database Instance Created',
                'status' => 'success',
                'message' => 'Database singleton instance created successfully'
            ];
            
            // Test 4: Get connection
            try {
                $connection = $dbInstance->getConnection();
                $tests[] = [
                    'name' => 'Database Connection Retrieved',
                    'status' => 'success',
                    'message' => 'Connection object retrieved successfully'
                ];
                
                // Test 5: Test connection with query
                try {
                    $stmt = $connection->query("SELECT 1 as test");
                    $result = $stmt->fetch();
                    $tests[] = [
                        'name' => 'Connection Query Test',
                        'status' => 'success',
                        'message' => 'Query executed successfully. Connection is working.'
                    ];
                    
                    // Test 6: Check if settings table exists
                    try {
                        $stmt = $connection->query("SHOW TABLES LIKE 'settings'");
                        $tableExists = $stmt->rowCount() > 0;
                        if ($tableExists) {
                            $tests[] = [
                                'name' => 'Settings Table Exists',
                                'status' => 'success',
                                'message' => 'Settings table found in database'
                            ];
                            
                            // Test 7: Count settings
                            try {
                                $stmt = $connection->query("SELECT COUNT(*) as count FROM settings");
                                $count = $stmt->fetch()['count'];
                                $tests[] = [
                                    'name' => 'Settings Table Data',
                                    'status' => 'success',
                                    'message' => "Settings table contains {$count} record(s)"
                                ];
                            } catch (PDOException $e) {
                                $tests[] = [
                                    'name' => 'Settings Table Data',
                                    'status' => 'error',
                                    'message' => 'Error querying settings table: ' . $e->getMessage()
                                ];
                            }
                        } else {
                            $tests[] = [
                                'name' => 'Settings Table Exists',
                                'status' => 'warning',
                                'message' => 'Settings table NOT found. Please run: database/add_settings_system.sql'
                            ];
                        }
                    } catch (PDOException $e) {
                        $tests[] = [
                            'name' => 'Settings Table Check',
                            'status' => 'error',
                            'message' => 'Error checking for settings table: ' . $e->getMessage()
                        ];
                    }
                    
                } catch (PDOException $e) {
                    $tests[] = [
                        'name' => 'Connection Query Test',
                        'status' => 'error',
                        'message' => 'Query failed: ' . $e->getMessage() . ' (Code: ' . $e->getCode() . ')'
                    ];
                }
                
            } catch (Exception $e) {
                $tests[] = [
                    'name' => 'Database Connection Retrieved',
                    'status' => 'error',
                    'message' => 'Failed to get connection: ' . $e->getMessage()
                ];
            }
            
        } catch (Exception $e) {
            $tests[] = [
                'name' => 'Database Instance Created',
                'status' => 'error',
                'message' => 'Failed to create database instance: ' . $e->getMessage()
            ];
        }
        
        // Display test results
        foreach ($tests as $test) {
            echo '<div class="test-item ' . $test['status'] . '">';
            echo '<div class="status">' . $test['name'] . '</div>';
            echo '<div>' . $test['message'] . '</div>';
            echo '</div>';
        }
        
        // Summary
        $successCount = count(array_filter($tests, fn($t) => $t['status'] === 'success'));
        $errorCount = count(array_filter($tests, fn($t) => $t['status'] === 'error'));
        $warningCount = count(array_filter($tests, fn($t) => $t['status'] === 'warning'));
        
        echo '<div class="test-item info">';
        echo '<div class="status">Test Summary</div>';
        echo "<div>✅ Success: {$successCount} | ❌ Errors: {$errorCount} | ⚠️ Warnings: {$warningCount}</div>";
        echo '</div>';
        
        // Recommendations
        if ($errorCount > 0) {
            echo '<div class="test-item warning">';
            echo '<div class="status">💡 Recommendations</div>';
            echo '<div>';
            echo '<ol>';
            echo '<li>Verify MySQL/MariaDB is running in XAMPP</li>';
            echo '<li>Check database credentials in <code>app/config/database.php</code></li>';
            echo '<li>Ensure database <code>kids_bazaar</code> exists</li>';
            echo '<li>If settings table is missing, run: <code>database/add_settings_system.sql</code></li>';
            echo '<li>Check PHP error logs for detailed error messages</li>';
            echo '</ol>';
            echo '</div>';
            echo '</div>';
        }
        ?>
        
        <div class="test-item info">
            <div class="status">📝 Configuration File Location</div>
            <div class="code"><?php echo APP_PATH . '/config/database.php'; ?></div>
        </div>
    </div>
</body>
</html>

