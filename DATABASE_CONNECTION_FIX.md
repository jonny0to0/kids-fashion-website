# Database Connection Error - Fix Documentation

## Problem
Error message: "Database connection error. Please check your database configuration."

## Root Cause Analysis

This error occurs when:
1. MySQL/MariaDB service is not running
2. Database credentials are incorrect
3. Database `kids_bazaar` doesn't exist
4. Connection is lost after initial establishment
5. Settings table doesn't exist

## Fixes Applied

### 1. Enhanced Database Connection Class ✅
**File**: `app/config/database.php`

**Changes**:
- Added connection verification after establishing connection
- Added connection health check in `getConnection()` method
- Improved error logging with detailed information
- Added `testConnection()` method for diagnostics

**Key Improvements**:
```php
// Now verifies connection is working
$this->connection->query("SELECT 1");

// Checks connection health on each getConnection() call
public function getConnection() {
    if ($this->connection === null) {
        throw new Exception("Database connection is not available...");
    }
    // Test connection
    $this->connection->query("SELECT 1");
    return $this->connection;
}
```

### 2. Enhanced Model Base Class ✅
**File**: `app/models/Model.php`

**Changes**:
- Added exception handling in constructor
- Verifies connection is not null
- Better error messages with context

### 3. Enhanced Settings Model ✅
**File**: `app/models/Settings.php`

**Changes**:
- Improved `getByGroup()` error handling
- Better detection of connection vs table errors
- Specific error messages for different failure types

### 4. Enhanced AdminController ✅
**File**: `app/controllers/AdminController.php`

**Changes**:
- Better error message categorization
- More specific error messages based on error type
- Suggests diagnostic tool in development mode

### 5. Diagnostic Tool Created ✅
**File**: `database/test_connection.php`

A comprehensive diagnostic tool that tests:
- Database class availability
- Configuration values
- Connection establishment
- Query execution
- Settings table existence
- Settings table data

## How to Diagnose the Issue

### Step 1: Run Diagnostic Tool
Navigate to:
```
http://localhost/kid-bazar-ecom/database/test_connection.php
```

This will show you exactly where the connection is failing.

### Step 2: Check Common Issues

#### Issue 1: MySQL Not Running
**Solution**:
1. Open XAMPP Control Panel
2. Start MySQL service
3. Verify it's running (green indicator)

#### Issue 2: Database Doesn't Exist
**Solution**:
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Check if `kids_bazaar` database exists
3. If not, create it:
   ```sql
   CREATE DATABASE kids_bazaar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

#### Issue 3: Wrong Credentials
**Solution**:
1. Open `app/config/database.php`
2. Verify credentials match your MySQL setup:
   ```php
   private $host = 'localhost';      // Usually 'localhost'
   private $dbname = 'kids_bazaar';  // Your database name
   private $username = 'root';        // Your MySQL username
   private $password = '';            // Your MySQL password
   ```

#### Issue 4: Settings Table Missing
**Solution**:
1. Open phpMyAdmin
2. Select `kids_bazaar` database
3. Go to "Import" tab
4. Choose file: `database/add_settings_system.sql`
5. Click "Go"

Or run via command line:
```bash
mysql -u root -p kids_bazaar < database/add_settings_system.sql
```

### Step 3: Check PHP Error Logs

Location (XAMPP Windows):
```
C:\xampp\php\logs\php_error_log
```

Look for entries containing:
- "Database Connection Error"
- "Settings::getByGroup"
- "PDOException"

### Step 4: Test Connection Manually

Create a test file `test_db.php` in project root:

```php
<?php
require_once 'app/config/config.php';
require_once 'app/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Connection successful!";
    $stmt = $db->query("SELECT DATABASE()");
    $result = $stmt->fetch();
    echo "<br>Current database: " . $result['DATABASE()'];
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
```

## Error Message Guide

| Error Message | Meaning | Solution |
|--------------|---------|----------|
| "Database connection error" | Connection failed | Check MySQL is running, verify credentials |
| "Settings table not found" | Table missing | Run `database/add_settings_system.sql` |
| "Access denied" | Wrong username/password | Check credentials in `database.php` |
| "Unknown database" | Database doesn't exist | Create `kids_bazaar` database |
| "Connection lost" | Connection dropped | Check MySQL service stability |

## Quick Fix Checklist

- [ ] MySQL/MariaDB service is running in XAMPP
- [ ] Database `kids_bazaar` exists
- [ ] Credentials in `app/config/database.php` are correct
- [ ] Settings table exists (check via phpMyAdmin)
- [ ] Run diagnostic tool: `database/test_connection.php`
- [ ] Check PHP error logs for detailed errors
- [ ] Verify file permissions on config files

## Testing After Fix

1. Navigate to admin settings page
2. Try to save any setting
3. Check browser console (F12) for errors
4. Check PHP error logs
5. If still failing, run diagnostic tool

## Files Modified

1. `app/config/database.php` - Enhanced connection handling
2. `app/models/Model.php` - Better error handling
3. `app/models/Settings.php` - Improved error detection
4. `app/controllers/AdminController.php` - Better error messages
5. `database/test_connection.php` - NEW diagnostic tool

## Next Steps

1. **Run the diagnostic tool** to identify the exact issue
2. **Fix the identified problem** (MySQL not running, wrong credentials, etc.)
3. **Test the settings save** functionality again
4. **Check error logs** if issues persist

---

**Status**: ✅ All fixes implemented
**Diagnostic Tool**: Available at `database/test_connection.php`
**Support**: Check PHP error logs for detailed error information

