# Maintenance Mode System - Implementation Guide

## Overview

A production-grade maintenance mode system has been implemented following PM/Architect specifications. This system ensures user experience, trust, and business continuity during maintenance windows.

## Features Implemented

### ✅ 1. Database Migration
- **File**: `database/add_maintenance_mode_system.sql`
- Creates maintenance mode settings in the existing `settings` table
- Includes: enabled flag, message, reason, ETA, timestamps, auto-disable, support email, status page URL, IP whitelist

### ✅ 2. MaintenanceMode Helper Class
- **File**: `app/helpers/MaintenanceMode.php`
- **Key Methods**:
  - `isEnabled()` - Check maintenance status with fail-safe auto-disable
  - `shouldAllowAccess()` - Admin bypass and IP whitelist checking
  - `isAdminRoute()` - Detect admin routes
  - `setMaintenanceHeaders()` - Set HTTP 503, Retry-After headers (SEO-safe)
  - `enable()` - Enable maintenance mode
  - `disable()` - Disable maintenance mode

### ✅ 3. Early Maintenance Check (Entry Point)
- **File**: `public/index.php`
- Maintenance check happens **before routing** (early gateway)
- Admin users can always access `/admin` routes during maintenance
- IP whitelist support for additional bypass
- Proper HTTP headers set before displaying maintenance page

### ✅ 4. Beautiful Maintenance Page
- **File**: `app/views/errors/maintenance.php`
- **Features**:
  - Modern, minimal design with soft gradients
  - Brand logo integration
  - Clear "We'll Be Right Back" messaging
  - ETA display with auto-calculation from end time
  - Support email with clickable mailto link
  - Optional status page link
  - Footer with copyright
  - Mobile responsive
  - WCAG-compliant accessibility
  - Auto-refresh every 60 seconds (respects reduced motion preference)

### ✅ 5. Admin Settings Interface
- **File**: `app/views/admin/settings/sections/maintenance.php`
- **Features**:
  - Visual status indicator (enabled/disabled)
  - Quick enable/disable toggle button
  - Maintenance message editor
  - Reason field (non-technical)
  - ETA input with auto-calculation to end time
  - Expected end time (datetime picker)
  - Auto-disable timeout (fail-safe, recommended: 6-12 hours)
  - Support email configuration
  - Status page URL
  - IP whitelist (comma-separated)
  - Best practices info box

### ✅ 6. HTTP Headers (SEO-Safe)
- **Status Code**: 503 Service Unavailable
- **Retry-After**: Calculated from end time or ETA
- **Cache-Control**: no-cache, no-store, must-revalidate
- **X-Maintenance-Mode**: enabled header
- **Meta Robots**: noindex, nofollow (prevents SEO indexing during maintenance)

## Installation Steps

### Step 1: Run Database Migration

```sql
-- Run this SQL file to create maintenance mode settings
SOURCE database/add_maintenance_mode_system.sql;
```

Or manually import `database/add_maintenance_mode_system.sql` via phpMyAdmin or your MySQL client.

### Step 2: Verify Files Are in Place

Ensure these files exist:
- ✅ `app/helpers/MaintenanceMode.php`
- ✅ `app/views/errors/maintenance.php`
- ✅ `app/views/admin/settings/sections/maintenance.php`
- ✅ `public/index.php` (updated with maintenance check)
- ✅ `app/views/admin/settings.php` (updated with maintenance section)

### Step 3: Test Maintenance Mode

1. **Access Admin Panel**: `/admin/settings?section=maintenance`
2. **Enable Maintenance Mode**: Click "Enable Maintenance" button
3. **Set Message & ETA**: Fill in maintenance details
4. **Save Settings**: Click "Save Changes"
5. **Test as Non-Admin**: Open incognito/private window and visit site
6. **Verify Admin Access**: As admin, you should still access `/admin` routes
7. **Disable Maintenance**: Return to admin panel and disable

## Architecture

```
User Request
   ↓
public/index.php
   ↓
Maintenance Check (Early Gateway)
   ↓
┌─────────────────────────────────┐
│ Maintenance Enabled?            │
└─────────────────────────────────┘
         │
    ┌────┴────┐
    │         │
  YES       NO
    │         │
    │         └──→ Normal App Flow
    │
    ↓
Check Admin/Whitelist
    │
    ┌────┴────┐
    │         │
  ALLOW    BLOCK
    │         │
    │         └──→ Show Maintenance Page (503)
    │              Set Retry-After Header
    │
    └──→ Normal App Flow (Admin routes)
```

## Key Design Decisions

### 1. Admin Bypass (Critical)
- Admin users **always** have access to `/admin` routes during maintenance
- This ensures admins can disable maintenance mode if needed
- Uses `Session::isAdmin()` check

### 2. Fail-Safe Auto-Disable
- Optional auto-disable after X hours (recommended: 6-12 hours)
- Prevents maintenance mode from being accidentally left on indefinitely
- Can be disabled by setting to 0

### 3. Early Check (Before Routing)
- Maintenance check happens in `public/index.php` before any routing
- Ensures maintenance page is shown even if routing fails
- Minimal overhead (single database query per request when enabled)

### 4. SEO-Safe Implementation
- HTTP 503 status (not 500, not 200)
- Retry-After header set appropriately
- Meta robots: noindex, nofollow
- Search engines understand temporary maintenance

### 5. User Experience
- Friendly, non-technical messaging
- Clear ETA when available
- Support contact information
- Brand continuity (logo, colors, design)
- Mobile-responsive design

## Usage Examples

### Enable Maintenance Mode (Via Admin Panel)

1. Navigate to: `/admin/settings?section=maintenance`
2. Fill in:
   - **Message**: "We're upgrading our systems to serve you better. We'll be back shortly!"
   - **Reason**: "Scheduled upgrade"
   - **ETA**: "2 hours"
   - **Auto-Disable After**: 6 (hours)
   - **Support Email**: "support@kidsbazaar.com"
3. Click "Enable Maintenance" button
4. Click "Save Changes"

### Enable Maintenance Mode (Via Code)

```php
require_once APP_PATH . '/helpers/MaintenanceMode.php';

MaintenanceMode::enable(
    message: 'We\'re performing scheduled maintenance.',
    reason: 'Scheduled upgrade',
    eta: '2 hours',
    endTime: date('Y-m-d H:i:s', strtotime('+2 hours')),
    autoDisableHours: 6
);
```

### Disable Maintenance Mode (Via Code)

```php
require_once APP_PATH . '/helpers/MaintenanceMode.php';

MaintenanceMode::disable();
```

## Settings Configuration

All maintenance settings are stored in the `settings` table with group `'maintenance'`:

| Key | Type | Description |
|-----|------|-------------|
| `maintenance_mode_enabled` | checkbox | Enable/disable flag |
| `maintenance_message` | textarea | User-facing message |
| `maintenance_reason` | text | Non-technical reason |
| `maintenance_eta` | text | Human-readable ETA (e.g., "2 hours") |
| `maintenance_start_time` | datetime | When maintenance started |
| `maintenance_end_time` | datetime | Expected end time |
| `maintenance_auto_disable_after` | number | Auto-disable after X hours (0 = disabled) |
| `maintenance_status_page_url` | text | External status page URL (optional) |
| `maintenance_support_email` | email | Support email for maintenance period |
| `maintenance_allowed_ips` | text | Comma-separated IP addresses (optional) |

## Best Practices

### ✅ DO:
- Announce maintenance 24-72 hours in advance
- Choose lowest traffic hours for maintenance
- Provide clear ETA and update if delayed
- Set auto-disable timeout (6-12 hours recommended)
- Keep messages user-friendly and non-technical
- Test maintenance mode before production use
- Verify admin access works during maintenance

### ❌ DON'T:
- Enable maintenance without announcement
- Leave technical error messages visible
- Forget to set auto-disable timeout
- Block admin access (system prevents this)
- Use maintenance mode for minor updates

## Troubleshooting

### Issue: Maintenance mode not showing
- **Check**: Database migration ran successfully
- **Check**: `maintenance_mode_enabled` setting is `'1'` in database
- **Check**: Admin session is not active (use incognito window)
- **Check**: Error logs for MaintenanceMode errors

### Issue: Admin can't access admin panel
- **Check**: Admin is logged in (`Session::isAdmin()` returns true)
- **Check**: Route starts with `/admin`
- **Check**: Session is active

### Issue: Maintenance page shows errors
- **Check**: `MaintenanceMode` helper is loaded
- **Check**: `Settings` model exists and is accessible
- **Check**: Error logs for specific errors

### Issue: Auto-disable not working
- **Check**: `maintenance_auto_disable_after` is > 0
- **Check**: `maintenance_start_time` is set correctly
- **Check**: Server time is correct

## Security Considerations

1. **Admin Bypass**: Admin access is critical - never block admin routes
2. **IP Whitelist**: Use sparingly and only for trusted IPs
3. **Settings Access**: Only admins can access settings panel
4. **SQL Injection**: All settings use PDO prepared statements (via Settings model)
5. **XSS Protection**: All output is escaped with `htmlspecialchars()`

## Performance

- **Minimal Overhead**: Single database query when maintenance is disabled
- **Caching Consideration**: Settings are queried once per request (no caching layer currently)
- **Future Enhancement**: Could add Redis/Memcached caching for settings

## Future Enhancements

- [ ] Maintenance mode scheduler (future maintenance windows)
- [ ] Maintenance history log
- [ ] Email notifications to admins when maintenance starts/ends
- [ ] Advanced IP whitelist (CIDR notation support)
- [ ] Maintenance mode templates (pre-defined messages)
- [ ] Partial maintenance (specific routes/pages only)

## Support

For issues or questions:
1. Check error logs: `error_log()` messages
2. Verify database settings exist
3. Test in development environment first
4. Ensure all files are in place and permissions are correct

---

**Implementation Date**: <?php echo date('Y-m-d'); ?>
**Version**: 1.0.0
**Status**: Production Ready ✅

