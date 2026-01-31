<?php
/**
 * Simple Settings Migration Runner
 * Creates the settings table and inserts default values
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Starting Settings Migration...\n\n";
    
    // Create table
    echo "Creating settings table...\n";
    $createTable = "
    CREATE TABLE IF NOT EXISTS `settings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `key` varchar(255) NOT NULL,
      `value` text DEFAULT NULL,
      `group` varchar(100) NOT NULL DEFAULT 'general',
      `type` varchar(50) NOT NULL DEFAULT 'text',
      `is_encrypted` tinyint(1) NOT NULL DEFAULT 0,
      `description` text DEFAULT NULL,
      `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_key` (`key`),
      KEY `idx_group` (`group`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($createTable);
    echo "✓ Settings table created\n\n";
    
    // Check if settings already exist
    $check = $db->query("SELECT COUNT(*) as count FROM settings")->fetch();
    if ($check['count'] > 0) {
        echo "⚠ Settings table already has {$check['count']} setting(s).\n";
        echo "Inserting only missing default settings...\n\n";
    } else {
        echo "Inserting default settings...\n";
    }
    
    // Insert default settings (using INSERT IGNORE to skip duplicates)
    {
        echo "Inserting default settings...\n";
        
        // Insert default settings
        $inserts = [
            // General Settings
            ['store_name', 'Kids Bazaar', 'general', 'text', 0, 'Store Name'],
            ['store_logo', '', 'general', 'file', 0, 'Store Logo'],
            ['store_favicon', '', 'general', 'file', 0, 'Store Favicon'],
            ['support_email', 'support@kidsbazaar.com', 'general', 'email', 0, 'Support Email'],
            ['contact_phone', '+1234567890', 'general', 'text', 0, 'Contact Phone'],
            ['default_language', 'en', 'general', 'select', 0, 'Default Language'],
            ['default_currency', 'USD', 'general', 'select', 0, 'Default Currency'],
            ['timezone', 'UTC', 'general', 'select', 0, 'Timezone'],
            
            // Store Settings
            ['store_status', 'live', 'store', 'select', 0, 'Store Status'],
            ['maintenance_message', 'We are currently performing maintenance. Please check back soon.', 'store', 'textarea', 0, 'Maintenance Message'],
            ['product_display_mode', 'grid', 'store', 'select', 0, 'Default Product Display Mode'],
            ['products_per_page', '24', 'store', 'number', 0, 'Products Per Page'],
            ['guest_checkout', '1', 'store', 'checkbox', 0, 'Enable Guest Checkout'],
            
            // Payment Settings
            ['payment_cod_enabled', '1', 'payment', 'checkbox', 0, 'Enable Cash on Delivery'],
            ['payment_razorpay_enabled', '0', 'payment', 'checkbox', 0, 'Enable Razorpay'],
            ['payment_razorpay_key', '', 'payment', 'text', 1, 'Razorpay Key ID'],
            ['payment_razorpay_secret', '', 'payment', 'text', 1, 'Razorpay Secret Key'],
            ['payment_razorpay_mode', 'sandbox', 'payment', 'select', 0, 'Razorpay Mode'],
            ['payment_stripe_enabled', '0', 'payment', 'checkbox', 0, 'Enable Stripe'],
            ['payment_stripe_key', '', 'payment', 'text', 1, 'Stripe Publishable Key'],
            ['payment_stripe_secret', '', 'payment', 'text', 1, 'Stripe Secret Key'],
            ['payment_stripe_mode', 'sandbox', 'payment', 'select', 0, 'Stripe Mode'],
            ['payment_paypal_enabled', '0', 'payment', 'checkbox', 0, 'Enable PayPal'],
            ['payment_paypal_client_id', '', 'payment', 'text', 1, 'PayPal Client ID'],
            ['payment_paypal_secret', '', 'payment', 'text', 1, 'PayPal Secret'],
            ['payment_paypal_mode', 'sandbox', 'payment', 'select', 0, 'PayPal Mode'],
            
            // Shipping Settings
            ['shipping_flat_rate', '5.00', 'shipping', 'number', 0, 'Flat Rate Shipping Cost'],
            ['shipping_free_threshold', '50.00', 'shipping', 'number', 0, 'Free Shipping Threshold'],
            ['shipping_estimated_days', '5-7', 'shipping', 'text', 0, 'Estimated Delivery Days'],
            
            // Tax Settings
            ['tax_enabled', '1', 'tax', 'checkbox', 0, 'Enable Tax'],
            ['tax_type', 'GST', 'tax', 'select', 0, 'Tax Type'],
            ['tax_rate', '10.00', 'tax', 'number', 0, 'Default Tax Rate (%)'],
            ['tax_inclusive', '0', 'tax', 'checkbox', 0, 'Tax Inclusive Pricing'],
            
            // Notification Settings
            ['notification_email_enabled', '1', 'notification', 'checkbox', 0, 'Enable Email Notifications'],
            ['notification_sms_enabled', '0', 'notification', 'checkbox', 0, 'Enable SMS Notifications'],
            ['notification_whatsapp_enabled', '0', 'notification', 'checkbox', 0, 'Enable WhatsApp Notifications'],
            ['notification_order_placed', '1', 'notification', 'checkbox', 0, 'Notify on Order Placed'],
            ['notification_order_shipped', '1', 'notification', 'checkbox', 0, 'Notify on Order Shipped'],
            ['notification_order_delivered', '1', 'notification', 'checkbox', 0, 'Notify on Order Delivered'],
            ['notification_payment_failed', '1', 'notification', 'checkbox', 0, 'Notify on Payment Failed'],
            ['notification_refund_processed', '1', 'notification', 'checkbox', 0, 'Notify on Refund Processed'],
            
            // SEO Settings
            ['seo_meta_title', 'Kids Bazaar - Fashion for Kids', 'seo', 'text', 0, 'Meta Title'],
            ['seo_meta_description', 'Shop the latest kids fashion and accessories', 'seo', 'textarea', 0, 'Meta Description'],
            ['seo_homepage_keywords', 'kids fashion, children clothing, kids accessories', 'seo', 'text', 0, 'Homepage Keywords'],
            ['seo_google_analytics_id', '', 'seo', 'text', 0, 'Google Analytics ID'],
            ['seo_facebook_pixel_id', '', 'seo', 'text', 0, 'Facebook Pixel ID'],
            
            // Security Settings
            ['security_password_min_length', '8', 'security', 'number', 0, 'Minimum Password Length'],
            ['security_2fa_enabled', '0', 'security', 'checkbox', 0, 'Enable Two-Factor Authentication'],
            ['security_login_attempt_limit', '5', 'security', 'number', 0, 'Login Attempt Limit'],
            ['security_session_timeout', '3600', 'security', 'number', 0, 'Session Timeout (seconds)'],
            
            // Integration Settings
            ['integration_google_maps_key', '', 'integration', 'text', 1, 'Google Maps API Key'],
            ['integration_smtp_host', '', 'integration', 'text', 0, 'SMTP Host'],
            ['integration_smtp_port', '587', 'integration', 'number', 0, 'SMTP Port'],
            ['integration_smtp_username', '', 'integration', 'text', 0, 'SMTP Username'],
            ['integration_smtp_password', '', 'integration', 'text', 1, 'SMTP Password'],
            ['integration_smtp_encryption', 'tls', 'integration', 'select', 0, 'SMTP Encryption'],
            ['integration_sms_gateway', '', 'integration', 'text', 0, 'SMS Gateway Provider'],
            ['integration_sms_api_key', '', 'integration', 'text', 1, 'SMS API Key'],
        ];
        
        $stmt = $db->prepare("
            INSERT IGNORE INTO settings (`key`, `value`, `group`, `type`, `is_encrypted`, `description`) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $inserted = 0;
        $skipped = 0;
        foreach ($inserts as $row) {
            try {
                $stmt->execute($row);
                if ($stmt->rowCount() > 0) {
                    $inserted++;
                } else {
                    $skipped++;
                }
            } catch (PDOException $e) {
                echo "✗ Error inserting {$row[0]}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✓ Inserted {$inserted} new settings\n";
        if ($skipped > 0) {
            echo "⚠ Skipped {$skipped} existing settings\n";
        }
        echo "\n";
    }
    
    echo "========================================\n";
    echo "Migration Complete!\n";
    echo "========================================\n";
    echo "\n";
    echo "You can now access the Settings page at:\n";
    echo "http://localhost/kid-bazar-ecom/public/admin/settings\n";
    
} catch (Exception $e) {
    echo "\n";
    echo "========================================\n";
    echo "Migration Failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "========================================\n";
    exit(1);
}

