<?php
/**
 * Quick Maintenance Mode Debug Script
 * 
 * Access this file directly in browser to check maintenance mode status
 * Usage: http://localhost/kid-bazar-ecom/debug_maintenance.php
 */

// Define base paths
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load configuration
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/config/constants.php';
require_once APP_PATH . '/config/database.php';

// Start session
session_start();

// Load required classes
require_once APP_PATH . '/helpers/Session.php';
require_once APP_PATH . '/models/Model.php'; // Load base Model class first
require_once APP_PATH . '/models/Settings.php';
require_once APP_PATH . '/helpers/MaintenanceMode.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Mode Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .enabled { background: #fee; border-left: 4px solid #f00; }
        .disabled { background: #efe; border-left: 4px solid #0a0; }
        .info { background: #eef; border-left: 4px solid #00a; margin: 10px 0; padding: 10px; }
        .error { background: #fee; border-left: 4px solid #f00; margin: 10px 0; padding: 10px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Maintenance Mode Debug</h1>
        
        <?php
        try {
            $settings = new Settings();
            $maintenanceInfo = MaintenanceMode::isEnabled();
            
            // Check database directly
            $db = $settings->db ?? null;
            $dbValue = null;
            if ($db) {
                try {
                    $stmt = $db->prepare("SELECT `key`, `value`, `type` FROM settings WHERE `key` = 'maintenance_mode_enabled' LIMIT 1");
                    $stmt->execute();
                    $dbRow = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($dbRow) {
                        $dbValue = $dbRow['value'];
                    }
                } catch (Exception $e) {
                    echo '<div class="error">Database query error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            
            // Display status
            $isEnabled = $maintenanceInfo['enabled'] ?? false;
            ?>
            
            <div class="status <?php echo $isEnabled ? 'enabled' : 'disabled'; ?>">
                <h2>Status: <?php echo $isEnabled ? '🟢 ENABLED' : '🔴 DISABLED'; ?></h2>
                <p><strong>Maintenance mode is currently <?php echo $isEnabled ? 'ACTIVE' : 'INACTIVE'; ?></strong></p>
            </div>
            
            <div class="info">
                <h3>📊 Current Settings</h3>
                <table>
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                    </tr>
                    <tr>
                        <td>Maintenance Enabled (via Settings Model)</td>
                        <td><?php echo htmlspecialchars(var_export($maintenanceInfo['enabled'] ?? false, true)); ?></td>
                    </tr>
                    <tr>
                        <td>Maintenance Enabled (Direct DB)</td>
                        <td><?php echo htmlspecialchars(var_export($dbValue, true)); ?></td>
                    </tr>
                    <tr>
                        <td>Message</td>
                        <td><?php echo htmlspecialchars($maintenanceInfo['message'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td>ETA</td>
                        <td><?php echo htmlspecialchars($maintenanceInfo['eta'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td>Start Time</td>
                        <td><?php echo htmlspecialchars($maintenanceInfo['start_time'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td>End Time</td>
                        <td><?php echo htmlspecialchars($maintenanceInfo['end_time'] ?? 'N/A'); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="info">
                <h3>👤 Current User</h3>
                <table>
                    <tr>
                        <th>Property</th>
                        <th>Value</th>
                    </tr>
                    <tr>
                        <td>Is Logged In</td>
                        <td><?php echo Session::isLoggedIn() ? 'Yes' : 'No'; ?></td>
                    </tr>
                    <tr>
                        <td>Is Admin</td>
                        <td><?php echo Session::isAdmin() ? 'Yes' : 'No'; ?></td>
                    </tr>
                    <tr>
                        <td>User Type</td>
                        <td><?php echo htmlspecialchars(Session::get('user_type', 'Not set')); ?></td>
                    </tr>
                    <tr>
                        <td>User ID</td>
                        <td><?php echo htmlspecialchars(Session::getUserId() ?? 'Not set'); ?></td>
                    </tr>
                    <tr>
                        <td>IP Address</td>
                        <td><?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Unknown'); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="info">
                <h3>🔍 Access Check</h3>
                <table>
                    <tr>
                        <th>Check</th>
                        <th>Result</th>
                    </tr>
                    <tr>
                        <td>Should Allow Access?</td>
                        <td><?php echo MaintenanceMode::shouldAllowAccess() ? '✅ YES' : '❌ NO'; ?></td>
                    </tr>
                    <tr>
                        <td>Is Admin Route? (current path)</td>
                        <td><?php 
                            $currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
                            echo MaintenanceMode::isAdminRoute($currentPath) ? '✅ YES' : '❌ NO';
                            echo ' (' . htmlspecialchars($currentPath) . ')';
                        ?></td>
                    </tr>
                </table>
            </div>
            
            <?php if (isset($maintenanceInfo['error'])): ?>
                <div class="error">
                    <h3>⚠️ Error</h3>
                    <p><?php echo htmlspecialchars($maintenanceInfo['error']); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="info">
                <h3>💡 Expected Behavior</h3>
                <ul>
                    <li><strong>If Maintenance is ENABLED:</strong>
                        <ul>
                            <li>Non-admin users should see maintenance page</li>
                            <li>Admin users should still access /admin routes</li>
                            <li>HTTP status should be 503</li>
                        </ul>
                    </li>
                    <li><strong>If Maintenance is DISABLED:</strong>
                        <ul>
                            <li>All users should access site normally</li>
                        </ul>
                    </li>
                </ul>
            </div>
            
            <div class="info">
                <h3>🔧 Quick Actions</h3>
                <p>
                    <a href="<?php echo SITE_URL; ?>/admin/settings?section=maintenance" style="background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Go to Maintenance Settings
                    </a>
                </p>
            </div>
            
            <?php
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<h3>❌ Error</h3>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>

