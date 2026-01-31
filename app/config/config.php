<?php
/**
 * Application Configuration File
 * Contains site-wide settings and configuration
 */

// Site Configuration
define('SITE_NAME', 'Kids Bazaar');
define('SITE_URL', 'https://ecom.dev.syntrex.io');
define('ADMIN_EMAIL', 'admin@kidsbazaar.com');

// Environment
define('ENVIRONMENT', 'development'); // development, production

// Error Reporting
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Session Configuration (must be set before session_start())
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1); // Secure cookies for HTTPS
}

// File Upload Settings
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Cart Settings
define('CART_SESSION_NAME', 'cart_session_id');

// Order Settings
define('ORDER_PREFIX', 'KB');
define('MIN_ORDER_AMOUNT', 299); // Minimum order amount in INR

// Shipping Settings
define('FREE_SHIPPING_THRESHOLD', 999); // Free shipping above this amount
define('SHIPPING_COST', 49); // Standard shipping cost

// Email Settings (Update with actual SMTP credentials)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@kidsbazaar.com');
define('SMTP_FROM_NAME', 'Kids Bazaar');

// Payment Gateway Settings (Razorpay example)
define('RAZORPAY_KEY_ID', '');
define('RAZORPAY_KEY_SECRET', '');

// Image Paths
define('PRODUCT_IMAGE_PATH', '/assets/uploads/products/');
define('CATEGORY_IMAGE_PATH', '/assets/images/categories/');
define('BANNER_IMAGE_PATH', '/assets/images/banners/');
define('AVATAR_IMAGE_PATH', '/assets/images/avatars/');

// Encryption Key for Settings (Change in production!)
define('ENCRYPTION_KEY', 'change_this_to_a_secure_random_key_in_production_32_chars_min');

