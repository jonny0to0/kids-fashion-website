# Maintenance Mode Fix - Issue Resolution

## Problem
Maintenance mode was not working - enabling it from the admin dashboard had no effect on users or admin.

## Root Causes Identified

1. **Checkbox Value Normalization Issue**: Checkbox values weren't being properly normalized to '1' or '0' strings when saving
2. **Value Type Handling**: The `isEnabled()` method wasn't handling all possible value types (string '1', int 1, boolean true, etc.)
3. **Missing Error Handling**: Silent failures made it difficult to diagnose issues
4. **Start Time Not Auto-Set**: When enabling maintenance, start_time wasn't automatically set

## Fixes Applied

### 1. Fixed Checkbox Value Normalization (`app/controllers/AdminController.php`)

**Before**: Checkbox values were only normalized at the end of the loop, after other processing.

**After**: Added early normalization for checkbox values:
```php
// CRITICAL FIX: Handle checkbox values properly
if ($existing && isset($existing['type']) && $existing['type'] === 'checkbox') {
    // Normalize checkbox value: true/1/'1'/'true' -> '1', everything else -> '0'
    if ($value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'on') {
        $value = '1';
    } else {
        $value = '0';
    }
}
```

Also improved the existing checkbox conversion:
```php
// Convert checkbox values - ensure it's always '1' or '0'
if ($type === 'checkbox') {
    // Normalize checkbox: true/1/'1'/'true'/'on' -> '1', everything else -> '0'
    if ($value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'on') {
        $value = '1';
    } else {
        $value = '0';
    }
}
```

### 2. Improved Value Type Handling (`app/helpers/MaintenanceMode.php`)

**Before**: Simple boolean cast that might fail with string values.

**After**: Comprehensive value normalization:
```php
// Normalize to boolean - handle string '1', '0', boolean true/false, int 1/0
$enabled = false;
if ($rawValue === true || $rawValue === 1 || $rawValue === '1' || $rawValue === 'true' || $rawValue === 'on') {
    $enabled = true;
}
```

### 3. Added Error Handling & Debugging

**Added to `MaintenanceMode::isEnabled()`**:
- Debug logging in development mode
- Better error messages
- Stack trace logging

**Added to `public/index.php`**:
- Try-catch around maintenance check
- Debug logging
- Fail-open behavior (if check fails, site continues normally)

### 4. Auto-Set Start Time (`app/controllers/AdminController.php`)

**Added**: Automatic start_time setting when maintenance is enabled:
```php
// CRITICAL: Auto-set maintenance_start_time when maintenance_mode_enabled is being enabled
if ($group === 'maintenance' && isset($settingsToSave['maintenance_mode_enabled'])) {
    $isEnabling = ($settingsToSave['maintenance_mode_enabled']['value'] === '1');
    $existingMaintenance = $existingSettings['maintenance_mode_enabled'] ?? null;
    $wasEnabled = $existingMaintenance && ($existingMaintenance['value'] === true || $existingMaintenance['value'] === '1' || $existingMaintenance['value'] === 1);
    
    // If enabling maintenance and start_time is not set, set it now
    if ($isEnabling && !$wasEnabled) {
        $settingsToSave['maintenance_start_time'] = [
            'value' => date('Y-m-d H:i:s'),
            'group' => 'maintenance',
            'type' => 'datetime',
            'is_encrypted' => false
        ];
    }
}
```

### 5. Improved JavaScript (`app/views/admin/settings/sections/maintenance.php`)

**Added**: Ensures hidden input is always properly set:
```javascript
// Ensure the hidden input is always in the form and has the correct value
const hiddenInput = document.getElementById('maintenance_mode_enabled');
if (hiddenInput) {
    hiddenInput.value = newStatus ? '1' : '0';
    // Force the input to be included in form submission
    hiddenInput.setAttribute('name', 'settings[maintenance_mode_enabled]');
}
```

## Testing

### Step 1: Run Test Script

```bash
php test_maintenance_mode.php
```

This will:
- Check if Settings model works
- Check current maintenance status
- Verify database values
- Test enable/disable functionality
- Provide diagnostic information

### Step 2: Manual Testing

1. **Enable Maintenance Mode**:
   - Go to `/admin/settings?section=maintenance`
   - Click "Enable Maintenance" button
   - Fill in message: "We're performing scheduled maintenance"
   - Fill in ETA: "2 hours"
   - Click "Save Changes"

2. **Test as Non-Admin**:
   - Open incognito/private browser window
   - Visit the site root
   - **Expected**: Should see maintenance page with 503 status

3. **Test as Admin**:
   - Log in as admin
   - Visit `/admin` or any admin route
   - **Expected**: Should work normally (admin bypass)

4. **Disable Maintenance**:
   - Go back to `/admin/settings?section=maintenance`
   - Click "Disable Maintenance"
   - Click "Save Changes"
   - **Expected**: Site should be accessible to all users

## Verification Checklist

- [ ] Database migration run: `database/add_maintenance_mode_system.sql`
- [ ] `maintenance_mode_enabled` setting exists in database
- [ ] Value is '1' (string) when enabled, '0' when disabled
- [ ] No errors in error logs
- [ ] Maintenance page displays correctly
- [ ] Admin can still access `/admin` routes
- [ ] Non-admin users see maintenance page
- [ ] HTTP 503 status code is returned
- [ ] Retry-After header is set

## Common Issues & Solutions

### Issue: Maintenance mode still not working after fixes

**Solution**:
1. Check database directly:
   ```sql
   SELECT `key`, `value`, `type` FROM settings WHERE `key` = 'maintenance_mode_enabled';
   ```
2. If value is not '1' or '0', update it:
   ```sql
   UPDATE settings SET `value` = '1' WHERE `key` = 'maintenance_mode_enabled';
   ```
3. Clear any PHP opcode cache (if using OPcache)
4. Check error logs for MaintenanceMode errors

### Issue: Maintenance page shows errors

**Solution**:
1. Check that `app/views/errors/maintenance.php` exists
2. Check that `MaintenanceMode` helper is loaded
3. Check that `Settings` model is accessible
4. Verify database connection is working

### Issue: Admin can't access admin panel during maintenance

**Solution**:
1. Verify admin is logged in (`Session::isAdmin()` returns true)
2. Check that route starts with `/admin`
3. Verify session is active
4. Check error logs for session issues

### Issue: Checkbox value not saving

**Solution**:
1. Check browser console for JavaScript errors
2. Verify form is submitting correctly
3. Check network tab - verify `settings[maintenance_mode_enabled]` is in POST data
4. Check server error logs for save errors

## Files Modified

1. `app/helpers/MaintenanceMode.php` - Improved value handling and error logging
2. `app/controllers/AdminController.php` - Fixed checkbox normalization, added auto start_time
3. `public/index.php` - Added error handling around maintenance check
4. `app/views/admin/settings/sections/maintenance.php` - Improved JavaScript

## Files Created

1. `test_maintenance_mode.php` - Diagnostic test script
2. `MAINTENANCE_MODE_FIX.md` - This document

## Next Steps

1. Run the test script to verify everything works
2. Test enable/disable functionality
3. Test as both admin and non-admin users
4. Monitor error logs for any issues
5. If issues persist, check database values directly

## Support

If maintenance mode still doesn't work after applying these fixes:

1. Run `php test_maintenance_mode.php` and share the output
2. Check error logs and share relevant entries
3. Verify database migration was run successfully
4. Check that all files are in place and have correct permissions

---

**Fix Applied**: <?php echo date('Y-m-d H:i:s'); ?>
**Status**: Ready for Testing ✅

