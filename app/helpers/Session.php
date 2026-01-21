<?php
/**
 * Session Helper Class
 * Manages session operations securely
 */

class Session {
    
    /**
     * Start session if not already started
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Set session value
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public static function get($key, $default = null) {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    /**
     * Check if session key exists
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session value
     */
    public static function remove($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Destroy entire session
     */
    public static function destroy() {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
    
    /**
     * Generate and store CSRF token
     */
    public static function generateCSRFToken() {
        self::start();
        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);
        return $token;
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        self::start();
        $stored_token = self::get('csrf_token');
        return $stored_token && hash_equals($stored_token, $token);
    }
    
    /**
     * Set flash message
     */
    public static function setFlash($key, $message) {
        self::set('flash_' . $key, $message);
    }
    
    /**
     * Get and remove flash message
     */
    public static function getFlash($key) {
        $message = self::get('flash_' . $key);
        self::remove('flash_' . $key);
        return $message;
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return self::has('user_id');
    }
    
    /**
     * Get logged in user ID
     */
    public static function getUserId() {
        return self::get('user_id');
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::get('user_type') === USER_TYPE_ADMIN;
    }

    /**
     * Ensure user is active (not deactivated)
     * To be called on every page load for logged-in users
     */
    /**
     * Ensure user is active (not deactivated)
     * To be called on every page load for logged-in users
     * Returns true if active/suspended (allowed to proceed mostly), 
     * false or 'deactivated' if should be logged out.
     */
    public static function ensureUserActive() {
        if (!self::isLoggedIn()) {
            return true;
        }

        // Skip check for admins
        if (self::isAdmin()) {
            return true;
        }

        $userId = self::getUserId();
        
        $userModel = new User();
        $status = $userModel->checkStatus($userId);
        
        if ($status === USER_STATUS_DEACTIVATED || $status === USER_STATUS_DELETED) {
            // Check if it's an AJAX request
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                      (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isAjax) {
                // For AJAX, return JSON and exit
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode([
                    'success' => false, 
                    'forceLogout' => true,
                    'message' => 'Your account has been deactivated. Please contact support.'
                ]);
                exit;
            }
            
            // For standard requests, we return 'deactivated' status
            // The view (header.php) will handle the UI (SweetAlert) and then redirect
            return 'deactivated';
        }
        
        // Update status in session if changed
        if ($status === USER_STATUS_SUSPENDED) {
            self::set('user_status', USER_STATUS_SUSPENDED);
        } else if ($status === USER_STATUS_ACTIVE) {
            self::set('user_status', USER_STATUS_ACTIVE);
        }
        
        return true;
    }
}

