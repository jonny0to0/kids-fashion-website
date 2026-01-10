# Product Description Encoding & Security Fix

## Overview

This document describes the fix for product description encoding issues where special characters like `&`, `@`, `#`, `%` were being double-escaped, resulting in displays like `Toddlers &amp;amp; School Wear` instead of `Toddlers & School Wear`.

## Problem

The application was applying HTML escaping (`htmlspecialchars()`) **before** saving data to the database, and then escaping again when displaying. This caused multiple levels of encoding:
- `&` → `&amp;` → `&amp;amp;` → `&amp;amp;amp;` (etc.)

## Solution

Following industry-standard practices (Amazon-style architecture):

1. **Store RAW data in database** - No HTML escaping before saving
2. **Escape only at output time** - Use `htmlspecialchars()` only when rendering HTML
3. **Use PDO prepared statements** - Protection against SQL injection (already implemented)
4. **UTF-8 charset** - Proper character encoding support

## Changes Made

### 1. Fixed Validator::sanitize() Method

**File:** `app/helpers/Validator.php`

**Before:**
```php
public static function sanitize($string, $stripTags = true) {
    if ($stripTags) {
        $string = strip_tags($string);
    }
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8'); // ❌ Wrong: Escaping before save
}
```

**After:**
```php
public static function sanitize($string, $stripTags = true) {
    $string = trim($string);
    if ($stripTags) {
        $string = strip_tags($string);
    }
    // ✅ Correct: Only trim and strip tags - NO HTML escaping
    // HTML escaping must be done at output time only
    return $string;
}

// New method for HTML output escaping
public static function escapeHtml($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}
```

### 2. AdminController (Already Correct)

**File:** `app/controllers/AdminController.php`

The `handleProductSave()` method uses `Validator::sanitize()` which now stores raw data. No changes needed - the fix in Validator automatically applies here.

### 3. View Files (Already Correct)

**Files:**
- `app/views/products/detail.php` (line 486)
- `app/views/admin/product_form.php` (lines 101, 107)
- All other product views

All views correctly use `htmlspecialchars()` when outputting:
```php
<?php echo htmlspecialchars($product['description']); ?>
```

### 4. Database Cleanup Scripts

Created scripts to fix existing double-escaped data:

#### PHP Script (Recommended)
**File:** `database/fix_product_description_encoding.php`

**Usage:**
```bash
php database/fix_product_description_encoding.php
```

OR access via browser:
```
http://your-domain/database/fix_product_description_encoding.php
```

This script:
- Detects products with HTML-encoded descriptions
- Decodes multiple levels of encoding automatically
- Updates the database with raw text
- Provides a summary report

#### SQL Script (Alternative)
**File:** `database/fix_product_description_encoding.sql`

**Usage:**
```bash
mysql -u username -p database_name < database/fix_product_description_encoding.sql
```

**Note:** The PHP script is recommended as it handles multiple encoding levels more reliably.

### 5. Database Charset Verification

#### PHP Script
**File:** `database/verify_product_charset.php`

**Usage:**
```bash
php database/verify_product_charset.php
```

Verifies and converts the products table to `utf8mb4` charset if needed.

#### SQL Script
**File:** `database/convert_products_to_utf8mb4.sql`

**Usage:**
```bash
mysql -u username -p database_name < database/convert_products_to_utf8mb4.sql
```

## Data Flow Architecture

### ✅ Correct Flow (Current Implementation)

```
User Input: "Toddlers & School Wear"
    ↓
Validation & Sanitization (strip tags, trim only)
    ↓
Store RAW in Database (using PDO prepared statements)
    Database: "Toddlers & School Wear"
    ↓
Fetch RAW from Database
    ↓
Escape ONCE at Output Time (htmlspecialchars)
    ↓
HTML Output: "Toddlers & School Wear" ✅
```

### ❌ Wrong Flow (Previous Bug)

```
User Input: "Toddlers & School Wear"
    ↓
Escape BEFORE Save (htmlspecialchars) ❌
    ↓
Store Encoded in Database
    Database: "Toddlers &amp; School Wear" ❌
    ↓
Fetch Encoded from Database
    ↓
Escape AGAIN at Output (htmlspecialchars) ❌
    ↓
HTML Output: "Toddlers &amp;amp; School Wear" ❌
```

## Security Features

### SQL Injection Protection
- ✅ **PDO Prepared Statements** - All queries use prepared statements
- ✅ **Parameter Binding** - User input is bound as parameters, never concatenated

### XSS Protection
- ✅ **HTML Escaping at Output** - All user-generated content is escaped using `htmlspecialchars()`
- ✅ **No Inline Scripts** - Escaped content prevents JavaScript injection

### Input Validation
- ✅ **Sanitization** - Tags are stripped if needed (configurable)
- ✅ **Length Validation** - Text length limits enforced
- ✅ **Type Validation** - Proper data types enforced

## Implementation Steps

### For New Installations

1. **Database Setup**
   ```sql
   -- Products table should use utf8mb4 charset
   ALTER TABLE products 
   CONVERT TO CHARACTER SET utf8mb4 
   COLLATE utf8mb4_unicode_ci;
   ```

2. **Code is Already Fixed**
   - Validator class fixed
   - Views already escape correctly
   - Controllers already use PDO prepared statements

### For Existing Installations

1. **Run Cleanup Script**
   ```bash
   php database/fix_product_description_encoding.php
   ```
   OR
   ```bash
   mysql -u username -p database_name < database/fix_product_description_encoding.sql
   ```

2. **Verify Charset**
   ```bash
   php database/verify_product_charset.php
   ```

3. **Test**
   - Create a new product with description: "Toddlers & School Wear"
   - Verify it displays correctly as: "Toddlers & School Wear"
   - Check database stores it as: "Toddlers & School Wear" (raw)

## Testing Checklist

- [x] Product descriptions with `&` display correctly
- [x] Product descriptions with `@`, `#`, `%` display correctly
- [x] New products save raw text to database
- [x] Existing products cleaned up (after running cleanup script)
- [x] SQL injection protection maintained (PDO prepared statements)
- [x] XSS protection maintained (htmlspecialchars on output)
- [x] UTF-8 special characters work correctly
- [x] Product form displays existing descriptions correctly

## Golden Rules (Memorize)

| Layer | Rule |
|-------|------|
| **Database** | Store RAW text |
| **PDO** | Use prepared statements only |
| **Backend** | NO htmlspecialchars before save |
| **Frontend** | Escape ONCE at output time |
| **Security** | XSS + SQL injection protected |
| **Cleanup** | Decode old data once |

## Files Modified

1. `app/helpers/Validator.php` - Fixed `sanitize()` method, added `escapeHtml()` method

## Files Created

1. `database/fix_product_description_encoding.php` - Cleanup script (recommended)
2. `database/fix_product_description_encoding.sql` - SQL cleanup script (alternative)
3. `database/verify_product_charset.php` - Charset verification script
4. `database/convert_products_to_utf8mb4.sql` - Charset conversion SQL

## Files Verified (No Changes Needed)

1. `app/controllers/AdminController.php` - Already uses PDO prepared statements
2. `app/views/products/detail.php` - Already escapes on output
3. `app/views/admin/product_form.php` - Already escapes on output
4. `app/models/Product.php` - Already uses PDO prepared statements
5. `app/config/database.php` - Already configured with utf8mb4

## Notes

- **Never** use `htmlspecialchars()` before saving to database
- **Always** use `htmlspecialchars()` when outputting to HTML
- PDO prepared statements provide SQL injection protection automatically
- UTF-8 charset ensures proper character encoding
- This architecture matches industry standards (Amazon, etc.)

## Support

If you encounter issues:
1. Verify database charset is `utf8mb4`
2. Run the cleanup script for existing data
3. Check that views use `htmlspecialchars()` on output
4. Verify PDO prepared statements are used for all queries
5. Check browser encoding is UTF-8

