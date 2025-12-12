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

// Simple routing system
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$path = str_replace(dirname($script_name), '', parse_url($request_uri, PHP_URL_PATH));
$path = trim($path, '/');

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
        
        // Try simple action first
        if (method_exists($controller, $action)) {
            // Call controller action
            call_user_func_array([$controller, $action], $params);
        } elseif (!empty($segments[2])) {
            // Try camelCase combination: segment[1] + segment[2] (e.g., product + add = productAdd)
            $combinedAction = $segments[1] . ucfirst($segments[2]);
            $remainingParams = array_slice($segments, 3);
            
            if (method_exists($controller, $combinedAction)) {
                // Call combined action with remaining params
                call_user_func_array([$controller, $combinedAction], $remainingParams);
            } else {
                // 404 - Action not found
                http_response_code(404);
                require_once VIEW_PATH . '/errors/404.php';
            }
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

