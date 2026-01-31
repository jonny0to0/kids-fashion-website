<?php
/**
 * Maintenance Mode Helper
 * Handles maintenance mode checking, admin bypass, and HTTP headers
 * 
 * Production-grade implementation following PM/Architect specifications:
 * - Global feature flag via Settings
 * - Role-based admin bypass
 * - Proper HTTP 503 status and Retry-After headers
 * - SEO-safe implementation
 * - Auto-disable after max duration (fail-safe)
 */

class MaintenanceMode {
    private static $settingsModel = null;
    
    /**
     * Get Settings model instance
     */
    private static function getSettingsModel() {
        if (self::$settingsModel === null) {
            self::$settingsModel = new Settings();
        }
        return self::$settingsModel;
    }
    
    /**
     * Check if maintenance mode is enabled
     * Returns array with status and details
     */
    public static function isEnabled() {
        try {
            $settings = self::getSettingsModel();
            
            // Get raw value first to debug
            $rawValue = $settings->get('maintenance_mode_enabled', false);
            
            // Debug logging in development
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                error_log("MaintenanceMode::isEnabled() - Raw value: " . var_export($rawValue, true) . " (type: " . gettype($rawValue) . ")");
            }
            
            // Fallback: Direct database check if Settings model returns unexpected value
            if ($rawValue === null || $rawValue === '' || (!is_bool($rawValue) && !is_numeric($rawValue) && $rawValue !== '1' && $rawValue !== '0')) {
                // Try direct database query as fallback
                try {
                    $db = $settings->db ?? null;
                    if ($db) {
                        $stmt = $db->prepare("SELECT `value` FROM settings WHERE `key` = 'maintenance_mode_enabled' LIMIT 1");
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($row && isset($row['value'])) {
                            $rawValue = $row['value'];
                            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                                error_log("MaintenanceMode::isEnabled() - Fallback DB check: " . var_export($rawValue, true));
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("MaintenanceMode::isEnabled() - Fallback DB check failed: " . $e->getMessage());
                }
            }
            
            // Normalize to boolean - handle string '1', '0', boolean true/false, int 1/0
            $enabled = false;
            if ($rawValue === true || $rawValue === 1 || $rawValue === '1' || $rawValue === 'true' || $rawValue === 'on') {
                $enabled = true;
            }
            
            // Fail-safe: Check auto-disable timeout
            if ($enabled) {
                $autoDisableHours = (int)$settings->get('maintenance_auto_disable_after', 0);
                
                if ($autoDisableHours > 0) {
                    $startTime = $settings->get('maintenance_start_time', '');
                    
                    if (!empty($startTime)) {
                        $startTimestamp = strtotime($startTime);
                        $maxTimestamp = $startTimestamp + ($autoDisableHours * 3600);
                        
                        if (time() > $maxTimestamp) {
                            // Auto-disable after max duration (fail-safe)
                            $settings->set('maintenance_mode_enabled', false, 'maintenance', 'checkbox');
                            error_log("Maintenance Mode: Auto-disabled after {$autoDisableHours} hours (fail-safe)");
                            return [
                                'enabled' => false,
                                'auto_disabled' => true
                            ];
                        }
                    }
                }
            }
            
            $result = [
                'enabled' => $enabled,
                'message' => $settings->get('maintenance_message', 'We\'re performing scheduled maintenance. Please check back soon.'),
                'reason' => $settings->get('maintenance_reason', 'Scheduled maintenance'),
                'eta' => $settings->get('maintenance_eta', ''),
                'start_time' => $settings->get('maintenance_start_time', ''),
                'end_time' => $settings->get('maintenance_end_time', ''),
                'status_page_url' => $settings->get('maintenance_status_page_url', ''),
                'support_email' => $settings->get('maintenance_support_email', $settings->get('support_email', defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'support@kidsbazaar.com')),
                'allowed_ips' => $settings->get('maintenance_allowed_ips', '')
            ];
            
            // Debug logging in development
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                error_log("MaintenanceMode::isEnabled() - Result: enabled=" . ($enabled ? 'true' : 'false'));
            }
            
            return $result;
        } catch (Exception $e) {
            // If settings table doesn't exist or error occurs, maintenance is off
            error_log("MaintenanceMode::isEnabled() error: " . $e->getMessage());
            error_log("MaintenanceMode::isEnabled() stack trace: " . $e->getTraceAsString());
            return [
                'enabled' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if current user should be allowed (admin bypass)
     * Admin users can access /admin routes even during maintenance
     * 
     * @param string|null $path Optional path to check (for route-based bypass)
     * @return bool True if access should be allowed
     */
    public static function shouldAllowAccess($path = null) {
        // CRITICAL: Always allow whitelisted routes (login, logout, status, etc.)
        // These routes must NEVER be blocked, even during maintenance
        if (self::isWhitelistedRoute($path)) {
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                error_log("MaintenanceMode::shouldAllowAccess() - Whitelisted route detected, allowing access");
            }
            return true;
        }
        
        // CRITICAL: Always allow admin routes
        // Admin routes must be accessible so admins can login and manage the system
        // Even if user is not logged in yet, they might be trying to login
        if (self::isAdminRoute($path)) {
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                error_log("MaintenanceMode::shouldAllowAccess() - Admin route detected, allowing access");
            }
            return true;
        }
        
        // Ensure Session class is loaded
        if (!class_exists('Session')) {
            // Try to load it via autoloader or direct require
            $sessionPath = APP_PATH . '/helpers/Session.php';
            if (file_exists($sessionPath)) {
                require_once $sessionPath;
            } else {
                // If Session can't be loaded, deny access (fail-safe)
                error_log("MaintenanceMode::shouldAllowAccess() - Session class not found");
                return false;
            }
        }
        
        // Check if user is admin (logged in admin users can access everything)
        try {
            if (Session::isAdmin()) {
                if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                    error_log("MaintenanceMode::shouldAllowAccess() - Admin user detected, allowing access");
                }
                return true;
            }
        } catch (Exception $e) {
            error_log("MaintenanceMode::shouldAllowAccess() - Error checking admin status: " . $e->getMessage());
            // If we can't check admin status, deny access (fail-safe)
            return false;
        }
        
        // Check IP whitelist
        try {
            $maintenanceInfo = self::isEnabled();
            if ($maintenanceInfo['enabled'] && !empty($maintenanceInfo['allowed_ips'])) {
                $allowedIPs = array_map('trim', explode(',', $maintenanceInfo['allowed_ips']));
                $currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
                
                if (in_array($currentIP, $allowedIPs)) {
                    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                        error_log("MaintenanceMode::shouldAllowAccess() - IP whitelisted: " . $currentIP);
                    }
                    return true;
                }
            }
        } catch (Exception $e) {
            error_log("MaintenanceMode::shouldAllowAccess() - Error checking IP whitelist: " . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Check if current route is admin route
     */
    public static function isAdminRoute($path = null) {
        if ($path === null) {
            $path = $_SERVER['REQUEST_URI'] ?? '';
        }
        
        // Check if path starts with /admin
        $path = trim(parse_url($path, PHP_URL_PATH), '/');
        $segments = $path ? explode('/', $path) : [];
        
        return !empty($segments) && strtolower($segments[0]) === 'admin';
    }
    
    /**
     * Check if route is whitelisted (always accessible during maintenance)
     * These routes must NEVER be blocked, even during maintenance
     * 
     * @param string|null $path The route path to check
     * @return bool True if route is whitelisted
     */
    public static function isWhitelistedRoute($path = null) {
        if ($path === null) {
            $path = $_SERVER['REQUEST_URI'] ?? '';
        }
        
        // Normalize path
        $path = trim(parse_url($path, PHP_URL_PATH), '/');
        $pathLower = strtolower($path);
        
        // Critical routes that must ALWAYS be accessible
        // These routes are essential for system operation and admin access
        $whitelistedRoutes = [
            'user/login',           // User login page
            'user/logout',          // User logout
            'admin/login',          // Admin login page (if exists)
            'logout',               // Generic logout
            'login',                // Generic login
            'status',               // System status endpoint
            'health',               // Health check endpoint
            'api',                  // API routes (if API should work during maintenance)
        ];
        
        // Check exact matches
        if (in_array($pathLower, $whitelistedRoutes)) {
            return true;
        }
        
        // Check if path starts with any whitelisted route
        foreach ($whitelistedRoutes as $whitelisted) {
            if (strpos($pathLower, $whitelisted) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Set HTTP headers for maintenance mode (SEO-safe)
     */
    public static function setMaintenanceHeaders($maintenanceInfo) {
        // Set 503 Service Unavailable status
        http_response_code(503);
        
        // Set Retry-After header based on end_time or default to 3600 seconds (1 hour)
        $retryAfter = 3600; // Default: 1 hour
        
        if (!empty($maintenanceInfo['end_time'])) {
            $endTimestamp = strtotime($maintenanceInfo['end_time']);
            $now = time();
            
            if ($endTimestamp > $now) {
                $retryAfter = $endTimestamp - $now;
            }
        } elseif (!empty($maintenanceInfo['eta'])) {
            // Try to parse ETA (e.g., "2 hours", "30 minutes")
            $etaLower = strtolower($maintenanceInfo['eta']);
            if (preg_match('/(\d+)\s*(hour|hr|h|minute|min|m|second|sec|s)/', $etaLower, $matches)) {
                $value = (int)$matches[1];
                $unit = strtolower($matches[2]);
                
                switch ($unit) {
                    case 'h':
                    case 'hr':
                    case 'hour':
                        $retryAfter = $value * 3600;
                        break;
                    case 'm':
                    case 'min':
                    case 'minute':
                        $retryAfter = $value * 60;
                        break;
                    case 's':
                    case 'sec':
                    case 'second':
                        $retryAfter = $value;
                        break;
                }
            }
        }
        
        // Ensure minimum retry-after of 60 seconds
        $retryAfter = max(60, $retryAfter);
        
        // Set headers
        header('Retry-After: ' . $retryAfter);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Maintenance-Mode: enabled');
    }
    
    /**
     * Enable maintenance mode
     */
    public static function enable($message = null, $reason = null, $eta = null, $endTime = null, $autoDisableHours = 0) {
        try {
            $settings = self::getSettingsModel();
            
            // Set enabled
            $settings->set('maintenance_mode_enabled', true, 'maintenance', 'checkbox');
            
            // Set start time if not already set
            $currentStartTime = $settings->get('maintenance_start_time', '');
            if (empty($currentStartTime)) {
                $settings->set('maintenance_start_time', date('Y-m-d H:i:s'), 'maintenance', 'datetime');
            }
            
            // Update message if provided
            if ($message !== null) {
                $settings->set('maintenance_message', $message, 'maintenance', 'textarea');
            }
            
            // Update reason if provided
            if ($reason !== null) {
                $settings->set('maintenance_reason', $reason, 'maintenance', 'text');
            }
            
            // Update ETA if provided
            if ($eta !== null) {
                $settings->set('maintenance_eta', $eta, 'maintenance', 'text');
            }
            
            // Update end time if provided
            if ($endTime !== null) {
                $settings->set('maintenance_end_time', $endTime, 'maintenance', 'datetime');
            } else if ($eta !== null && empty($settings->get('maintenance_end_time', ''))) {
                // Try to calculate end time from ETA
                $estimatedSeconds = 3600; // Default 1 hour
                $etaLower = strtolower($eta);
                if (preg_match('/(\d+)\s*(hour|hr|h|minute|min|m)/', $etaLower, $matches)) {
                    $value = (int)$matches[1];
                    $unit = strtolower($matches[2]);
                    
                    if (in_array($unit, ['h', 'hr', 'hour'])) {
                        $estimatedSeconds = $value * 3600;
                    } elseif (in_array($unit, ['m', 'min', 'minute'])) {
                        $estimatedSeconds = $value * 60;
                    }
                }
                
                $endTime = date('Y-m-d H:i:s', time() + $estimatedSeconds);
                $settings->set('maintenance_end_time', $endTime, 'maintenance', 'datetime');
            }
            
            // Set auto-disable hours
            if ($autoDisableHours > 0) {
                $settings->set('maintenance_auto_disable_after', $autoDisableHours, 'maintenance', 'number');
            }
            
            return true;
        } catch (Exception $e) {
            error_log("MaintenanceMode::enable() error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Disable maintenance mode
     */
    public static function disable() {
        try {
            $settings = self::getSettingsModel();
            $settings->set('maintenance_mode_enabled', false, 'maintenance', 'checkbox');
            
            // Clear timestamps
            $settings->set('maintenance_start_time', '', 'maintenance', 'datetime');
            $settings->set('maintenance_end_time', '', 'maintenance', 'datetime');
            
            return true;
        } catch (Exception $e) {
            error_log("MaintenanceMode::disable() error: " . $e->getMessage());
            return false;
        }
    }
}

