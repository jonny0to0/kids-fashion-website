# E-commerce Settings Page - Implementation Complete

## Overview

A comprehensive, secure, and scalable Settings Page has been implemented for the e-commerce platform. This system allows administrators to manage global system behavior, business rules, and integrations without modifying backend code.

## What Was Implemented

### 1. Database Structure
- **File**: `database/add_settings_system.sql`
- Settings table with support for:
  - Key-value storage
  - Grouping by category
  - Type system (text, number, checkbox, file, etc.)
  - Encryption support for sensitive data
  - Description fields

### 2. Settings Model
- **File**: `app/models/Settings.php`
- Features:
  - Get/Set individual settings
  - Get settings by group
  - Batch updates
  - Automatic encryption/decryption
  - Type conversion (checkbox, number, etc.)
  - Masked values for encrypted fields

### 3. Admin Controller Methods
- **File**: `app/controllers/AdminController.php`
- Methods added:
  - `settings()` - Display settings page
  - `settingsSave()` - AJAX endpoint for saving settings
  - `settingsTestConnection()` - Test integration connections
  - `settingsBackupDatabase()` - Database backup (Super Admin only)

### 4. Settings View
- **Main View**: `app/views/admin/settings.php`
- **Section Views**: `app/views/admin/settings/sections/`
  - `general.php` - General Settings
  - `store.php` - Store Settings
  - `payment.php` - Payment Settings
  - `shipping.php` - Shipping & Delivery
  - `tax.php` - Tax Configuration
  - `notification.php` - Notification Settings
  - `seo.php` - SEO & Analytics
  - `security.php` - Security Settings
  - `integration.php` - Integrations
  - `backup.php` - Backup & Maintenance

### 5. JavaScript Handler
- **File**: `public/assets/js/admin-settings.js`
- Features:
  - AJAX form submissions
  - Form validation
  - Connection testing
  - Loading states
  - Success/Error notifications

### 6. Configuration
- **File**: `app/config/config.php`
- Added encryption key constant

## Installation Steps

1. **Run Database Migration**
   ```sql
   -- Execute the SQL file
   source database/add_settings_system.sql
   ```
   Or import `database/add_settings_system.sql` via phpMyAdmin

2. **Update Encryption Key** (Important!)
   - Edit `app/config/config.php`
   - Change `ENCRYPTION_KEY` to a secure random string (32+ characters)
   - Example: `openssl_random_pseudo_bytes(32)`

3. **Access Settings Page**
   - Navigate to: `/admin/settings`
   - Or: `/admin/settings?section=general`

## Settings Modules

### General Settings
- Store Name
- Logo Upload
- Favicon Upload
- Support Email
- Contact Phone
- Default Language
- Default Currency
- Timezone

### Store Settings
- Store Status (Live/Maintenance)
- Maintenance Message
- Product Display Mode
- Products Per Page
- Guest Checkout Toggle

### Payment Settings
- Cash on Delivery
- Razorpay (with encrypted keys)
- Stripe (with encrypted keys)
- PayPal (with encrypted keys)
- Payment Mode (Sandbox/Live)

### Shipping & Delivery
- Flat Rate Shipping Cost
- Free Shipping Threshold
- Estimated Delivery Time

### Tax Configuration
- Enable/Disable Tax
- Tax Type (GST/VAT/Custom)
- Default Tax Rate
- Tax Inclusive Pricing

### Notification Settings
- Email/SMS/WhatsApp Channels
- Event-based notifications (Order Placed, Shipped, etc.)

### SEO & Analytics
- Meta Title & Description
- Homepage Keywords
- Google Analytics ID
- Facebook Pixel ID

### Security Settings
- Password Policy
- Two-Factor Authentication
- Login Attempt Limit
- Session Timeout

### Integrations
- Google Maps API (with connection test)
- SMTP Configuration (with connection test)
- SMS Gateway

### Backup & Maintenance
- Manual Database Backup
- System Health Status

## Security Features

1. **Encryption**
   - Sensitive fields (API keys, passwords) are encrypted using AES-256-CBC
   - Encrypted values are masked in the UI (shows as ••••••••••••)
   - Only updates when new value is provided

2. **Access Control**
   - Admin-only access (checked via `requireAdmin()`)
   - Backup operations restricted to Super Admin

3. **Validation**
   - Frontend validation (JavaScript)
   - Backend validation (PHP)
   - Type checking and sanitization

4. **CSRF Protection**
   - Can be added using existing Session CSRF token system

## Usage Examples

### Getting Settings in Code
```php
$settingsModel = new Settings();

// Get a single setting
$storeName = $settingsModel->get('store_name', 'Default Store');

// Get all settings in a group
$paymentSettings = $settingsModel->getByGroup('payment');

// Get all settings
$allSettings = $settingsModel->getAll();
```

### Setting Values Programmatically
```php
$settingsModel = new Settings();

// Set a regular setting
$settingsModel->set('store_name', 'My Store', 'general', 'text');

// Set an encrypted setting
$settingsModel->set('payment_razorpay_secret', 'secret_key', 'payment', 'text', true);
```

### Checking Maintenance Mode
```php
$settingsModel = new Settings();
$storeStatus = $settingsModel->get('store_status');

if ($storeStatus === 'maintenance' && !Session::isAdmin()) {
    // Show maintenance page
    die('Store is under maintenance');
}
```

## File Upload Handling

- Logo and Favicon uploads are handled automatically
- Files are stored in `public/assets/uploads/settings/`
- Old files are not automatically deleted (can be added in future)

## Connection Testing

- SMTP: Tests basic connection to SMTP server
- Google Maps: Validates API key with a test request
- Other integrations can be added similarly

## Future Enhancements

1. **Caching**
   - Implement Redis/Memcached for settings
   - Cache invalidation on update

2. **Audit Log**
   - Log all setting changes
   - Track who changed what and when

3. **Advanced Features**
   - Scheduled backups
   - State-wise tax rules
   - Shipping zones
   - Custom notification templates
   - Role-based permissions for settings

4. **UI Improvements**
   - Real-time preview for logo/favicon
   - Color picker for theme settings
   - Rich text editor for maintenance message

## Troubleshooting

### Settings Not Saving
- Check file permissions on uploads directory
- Verify database connection
- Check browser console for JavaScript errors

### Encrypted Fields Not Working
- Ensure `ENCRYPTION_KEY` is set in config
- Key must be at least 32 characters
- Don't change key after data is encrypted (will break decryption)

### File Uploads Failing
- Check `MAX_UPLOAD_SIZE` in config
- Verify uploads directory exists and is writable
- Check PHP `upload_max_filesize` and `post_max_size`

## Notes

- Settings are stored in the database, not in config files
- Changes take effect immediately (no cache clearing needed for most settings)
- Encrypted fields show masked values - enter new value to update
- All settings have default values in the migration SQL

## Support

For issues or questions, refer to the main project documentation or contact the development team.

