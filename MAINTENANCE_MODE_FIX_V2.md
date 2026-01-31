# Maintenance Mode Fix V2 - Users Can Still Access Site

## Problem
After enabling maintenance mode, users can still access the website and the maintenance page is not showing.

## Root Causes

1. **Session Class Not Loaded**: `MaintenanceMode::shouldAllowAccess()` was calling `Session::isAdmin()` but Session class might not have been loaded yet via autoloader
2. **Silent Failures**: Errors in maintenance check were being caught but not properly handled
3. **No Fallback Database Check**: If Settings model had issues, there was no fallback to check database directly
4. **Insufficient Debugging**: Hard to diagnose why maintenance mode wasn't working

## Fixes Applied

### 1. Explicit Session Loading (`public/index.php`)

**Before**: Session was loaded via autoloader (unreliable timing)

**After**: Explicitly load Session before MaintenanceMode:
```php
// Load Session helper first (needed by MaintenanceMode)
require_once APP_PATH . '/helpers/Session.php';

// Load MaintenanceMode helper (needed early for maintenance check)
require_once APP_PATH . '/helpers/MaintenanceMode.php';
```

### 2. Improved shouldAllowAccess() (`app/helpers/MaintenanceMode.php`)

**Added**:
- Explicit Session class loading check
- Better error handling with try-catch
- Debug logging in development mode
- Fail-safe behavior (deny access if Session can't be loaded)

```php
public static function shouldAllowAccess() {
    // Ensure Session class is loaded
    if (!class_exists('Session')) {
        $sessionPath = APP_PATH . '/helpers/Session.php';
        if (file_exists($sessionPath)) {
            require_once $sessionPath;
        } else {
            error_log("MaintenanceMode::shouldAllowAccess() - Session class not found");
            return false; // Fail-safe: deny access
        }
    }
    
    // Check if user is admin with error handling
    try {
        if (Session::isAdmin()) {
            return true;
        }
    } catch (Exception $e) {
        error_log("MaintenanceMode::shouldAllowAccess() - Error checking admin status: " . $e->getMessage());
        return false; // Fail-safe: deny access
    }
    
    // ... IP whitelist check ...
}
```

### 3. Fallback Database Check (`app/helpers/MaintenanceMode.php`)

**Added**: Direct database query as fallback if Settings model returns unexpected values:

```php
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
            }
        }
    } catch (Exception $e) {
        error_log("MaintenanceMode::isEnabled() - Fallback DB check failed: " . $e->getMessage());
    }
}
```

### 4. Enhanced Debug Logging (`public/index.php`)

**Added**: Comprehensive debug logging in development mode:

```php
if (isset($maintenanceInfo['enabled']) && $maintenanceInfo['enabled']) {
    // Debug logging
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        error_log("Maintenance Mode ACTIVE - Path: " . $path);
        error_log("Maintenance Mode - Is Admin Route: " . ($isAdminRoute ? 'yes' : 'no'));
        error_log("Maintenance Mode - Should Allow Access: " . ($shouldAllow ? 'yes' : 'no'));
    }
    
    // ... maintenance check logic ...
}
```

### 5. Debug Page Created (`debug_maintenance.php`)

**Created**: A visual debug page to check maintenance mode status:
- Access at: `http://localhost/kid-bazar-ecom/debug_maintenance.php`
- Shows:
  - Current maintenance status
  - Database values
  - Current user info
  - Access check results
  - Expected behavior

## Testing Steps

### Step 1: Check Current Status

Visit the debug page:
```
http://localhost/kid-bazar-ecom/debug_maintenance.php
```

This will show:
- Current maintenance mode status
- Database values
- User information
- Access check results

### Step 2: Enable Maintenance Mode

1. Go to `/admin/settings?section=maintenance`
2. Click "Enable Maintenance" button
3. Fill in:
   - Message: "We're performing scheduled maintenance"
   - ETA: "2 hours"
4. Click "Save Changes"

### Step 3: Test as Non-Admin User

1. Open incognito/private browser window (or logout if logged in)
2. Visit the site root: `http://localhost/kid-bazar-ecom/public/`
3. **Expected**: Should see maintenance page with:
   - HTTP 503 status code
   - Maintenance message
   - ETA information
   - Support email

### Step 4: Test as Admin User

1. Log in as admin
2. Visit `/admin` or any admin route
3. **Expected**: Should work normally (admin bypass)

### Step 5: Check Error Logs

If maintenance mode still doesn't work, check error logs:
- XAMPP: `C:\xampp\apache\logs\error.log`
- Look for messages starting with "MaintenanceMode" or "Maintenance check"

## Verification Checklist

- [ ] Session class is loaded before MaintenanceMode
- [ ] Maintenance mode status shows correctly in debug page
- [ ] Database value is '1' when enabled
- [ ] Non-admin users see maintenance page
- [ ] Admin users can access /admin routes
- [ ] HTTP 503 status code is returned
- [ ] No errors in error logs
- [ ] Debug logging works in development mode

## Common Issues & Solutions

### Issue: Maintenance page still not showing

**Check**:
1. Visit `debug_maintenance.php` to see current status
2. Check database directly:
   ```sql
   SELECT `key`, `value` FROM settings WHERE `key` = 'maintenance_mode_enabled';
   ```
   Should be `'1'` when enabled
3. Check error logs for MaintenanceMode errors
4. Verify Session class is loaded (check debug page)

**Solution**:
- If database value is not '1', update it:
  ```sql
  UPDATE settings SET `value` = '1' WHERE `key` = 'maintenance_mode_enabled';
  ```
- Clear PHP opcode cache if using OPcache
- Restart Apache/XAMPP

### Issue: Admin can't access admin panel

**Check**:
1. Verify admin is logged in (check debug page)
2. Check `Session::isAdmin()` returns true
3. Verify route starts with `/admin`

**Solution**:
- Ensure admin session is active
- Check user_type in session is 'admin'
- Verify constants.php has USER_TYPE_ADMIN defined

### Issue: All users see maintenance page (including admin)

**Check**:
1. Check debug page - is "Should Allow Access?" showing NO for admin?
2. Check error logs for Session errors

**Solution**:
- Verify Session class is loaded
- Check that admin user_type is set correctly in session
- Verify USER_TYPE_ADMIN constant is defined

## Files Modified

1. `public/index.php` - Added explicit Session loading, enhanced debug logging
2. `app/helpers/MaintenanceMode.php` - Improved shouldAllowAccess(), added fallback DB check
3. `debug_maintenance.php` - Created debug page (NEW)

## Debug Tools

### 1. Debug Page
Access: `http://localhost/kid-bazar-ecom/debug_maintenance.php`

Shows:
- Maintenance status
- Database values
- User information
- Access checks
- Expected behavior

### 2. Test Script
Run: `php test_maintenance_mode.php`

Tests:
- Settings model
- Maintenance status
- Enable/disable functionality

### 3. Error Logs
Location: `C:\xampp\apache\logs\error.log`

Look for:
- "MaintenanceMode" messages
- "Maintenance check" messages
- Session errors

## Next Steps

1. **Test the fixes**: Follow the testing steps above
2. **Check debug page**: Visit `debug_maintenance.php` to verify status
3. **Monitor error logs**: Check for any MaintenanceMode errors
4. **Test both scenarios**: As admin and as non-admin user

## Expected Behavior

### When Maintenance is ENABLED:

✅ **Non-admin users**:
- See maintenance page
- HTTP 503 status
- Cannot access any routes (except admin routes if they somehow get there)

✅ **Admin users**:
- Can access `/admin` routes normally
- See maintenance page on non-admin routes (optional - can be changed)

✅ **Whitelisted IPs**:
- Can access site normally

### When Maintenance is DISABLED:

✅ **All users**:
- Access site normally
- No restrictions

---

**Fix Applied**: <?php echo date('Y-m-d H:i:s'); ?>
**Status**: Ready for Testing ✅

**Key Changes**:
- Explicit Session loading
- Fallback database check
- Enhanced error handling
- Comprehensive debug logging
- Debug page for diagnostics

