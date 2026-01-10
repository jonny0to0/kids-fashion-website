<?php
/**
 * Main Entry Point for Kids Fashion E-commerce Platform
 * Handles routing and initializes the application
 */

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/uploads');

// Load configuration first (before session start)
require_once APP_PATH . '/config/config.php';
require_once APP_PATH . '/config/constants.php';

// Start session (after configuration is loaded)
session_start();

// Autoloader
spl_autoload_register(function ($class) {
    // Ensure Model base class is loaded before any model that extends it
    $modelPath = APP_PATH . '/models/' . $class . '.php';
    if (file_exists($modelPath) && $class !== 'Model') {
        // Load Model base class first if it exists and hasn't been loaded
        $baseModelPath = APP_PATH . '/models/Model.php';
        if (file_exists($baseModelPath) && !class_exists('Model', false)) {
            require_once $baseModelPath;
        }
    }
    
    $paths = [
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/helpers/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Initialize database connection
require_once APP_PATH . '/config/database.php';

// Load Session helper first (needed by MaintenanceMode)
require_once APP_PATH . '/helpers/Session.php';

// Load MaintenanceMode helper (needed early for maintenance check)
require_once APP_PATH . '/helpers/MaintenanceMode.php';

// Simple routing system - prepare path early
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$script_name = $_SERVER['SCRIPT_NAME'];
$path = str_replace(dirname($script_name), '', parse_url($request_uri, PHP_URL_PATH));
$path = trim($path, '/');

// Maintenance Mode Check (Early - Before Routing)
// This is a production-grade maintenance gateway implementation
try {
    $maintenanceInfo = MaintenanceMode::isEnabled();
    
    // Debug logging in development
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        error_log("Maintenance check - enabled: " . ($maintenanceInfo['enabled'] ? 'true' : 'false'));
        if (isset($maintenanceInfo['error'])) {
            error_log("Maintenance check - error: " . $maintenanceInfo['error']);
        }
    }
} catch (Exception $e) {
    // If maintenance check fails, log error but continue (fail-open for safety)
    error_log("Maintenance check exception: " . $e->getMessage());
    $maintenanceInfo = ['enabled' => false, 'error' => $e->getMessage()];
}

if (isset($maintenanceInfo['enabled']) && $maintenanceInfo['enabled']) {
    // Debug logging in development
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        error_log("Maintenance Mode ACTIVE - Path: " . $path);
    }
    
    // CRITICAL: Check if access should be allowed
    // This method checks:
    // 1. Whitelisted routes (login, logout, status, health) - ALWAYS allowed
    // 2. Admin routes - ALWAYS allowed (so admins can login)
    // 3. Logged-in admin users - ALWAYS allowed
    // 4. IP whitelist - ALLOWED if configured
    $shouldAllow = MaintenanceMode::shouldAllowAccess($path);
    
    // Debug logging in development
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        $isAdminRoute = MaintenanceMode::isAdminRoute($path);
        $isWhitelisted = MaintenanceMode::isWhitelistedRoute($path);
        error_log("Maintenance Mode - Path: " . $path);
        error_log("Maintenance Mode - Is Admin Route: " . ($isAdminRoute ? 'yes' : 'no'));
        error_log("Maintenance Mode - Is Whitelisted Route: " . ($isWhitelisted ? 'yes' : 'no'));
        error_log("Maintenance Mode - Should Allow Access: " . ($shouldAllow ? 'yes' : 'no'));
    }
    
    if ($shouldAllow) {
        // Access is allowed - continue to normal routing
        // This includes:
        // - Whitelisted routes (login, logout, status, etc.)
        // - Admin routes (so admins can login and access dashboard)
        // - Logged-in admin users
        // - IP whitelisted users
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            error_log("Maintenance Mode - Allowing access (whitelisted route, admin route, or admin user)");
        }
        // Continue to normal routing below
    } else {
        // Maintenance mode is ON and user is not allowed
        // Show maintenance page with proper HTTP headers
        
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            error_log("Maintenance Mode - Showing maintenance page to user");
        }
        
        // Set proper HTTP headers for SEO and browser behavior
        MaintenanceMode::setMaintenanceHeaders($maintenanceInfo);
        
        // Load and display maintenance page
        require_once VIEW_PATH . '/errors/maintenance.php';
        exit;
    }
}

// Split path into segments
$segments = $path ? explode('/', $path) : [];

// Helper function to convert kebab-case to camelCase
function kebabToCamel($string) {
    return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $string))));
}

// Get controller and action
$controller_name = !empty($segments[0]) ? ucfirst($segments[0]) . 'Controller' : 'HomeController';
$action = !empty($segments[1]) ? kebabToCamel($segments[1]) : 'index';
$params = array_slice($segments, 2);

// Check if controller exists
$controller_file = APP_PATH . '/controllers/' . $controller_name . '.php';

if (file_exists($controller_file)) {
    require_once $controller_file;
    
    if (class_exists($controller_name)) {
        $controller = new $controller_name();
        
        // If there are 3+ segments, try combined action first (e.g., products + inventory = productsInventory)
        // This ensures specific routes like /admin/products/inventory work correctly
        if (!empty($segments[2])) {
            $combinedAction = kebabToCamel($segments[1]) . ucfirst(kebabToCamel($segments[2]));
            $remainingParams = array_slice($segments, 3);
            
            if (method_exists($controller, $combinedAction)) {
                // Call combined action with remaining params
                call_user_func_array([$controller, $combinedAction], $remainingParams);
            } elseif (method_exists($controller, $action)) {
                // Fallback to simple action if combined doesn't exist
                call_user_func_array([$controller, $action], $params);
            } else {
                // 404 - Action not found
                http_response_code(404);
                require_once VIEW_PATH . '/errors/404.php';
            }
        } elseif (method_exists($controller, $action)) {
            // Try simple action for 2-segment routes (e.g., /admin/products)
            call_user_func_array([$controller, $action], $params);
        } else {
            // 404 - Action not found
            http_response_code(404);
            require_once VIEW_PATH . '/errors/404.php';
        }
    } else {
        // 404 - Controller class not found
        http_response_code(404);
        require_once VIEW_PATH . '/errors/404.php';
    }
} else {
    // 404 - Controller file not found
    http_response_code(404);
    require_once VIEW_PATH . '/errors/404.php';
}

