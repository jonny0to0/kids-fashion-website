# Hero Banner System - Quick Start Guide

## Installation Steps

1. **Run the Database Migration**:
   ```bash
   mysql -u root -p kids_bazaar < database/add_hero_banners_system.sql
   ```
   Or import via phpMyAdmin.

2. **Verify File Structure**:
   - ✅ `app/models/HeroBanner.php` exists
   - ✅ `app/controllers/AdminController.php` has hero banner methods
   - ✅ `app/controllers/ApiController.php` exists
   - ✅ `app/views/admin/hero_banners/` directory exists
   - ✅ `public/assets/js/hero-banner-slider.js` exists

3. **Create Upload Directory** (if not exists):
   ```bash
   mkdir -p public/assets/uploads/banners
   chmod 755 public/assets/uploads/banners
   ```

## First Banner Setup

1. **Login to Admin Panel**: `/admin`
2. **Navigate to Hero Banners**: `/admin/hero-banners`
3. **Click "Add New Banner"**
4. **Fill Required Fields**:
   - Title: "Welcome to Kids Bazaar"
   - Desktop Image: Upload 1920x600px image
   - Mobile Image: Upload 768x400px image
   - Priority: 10
   - Status: Active
   - Target Type: Homepage
   - Device Visibility: Both

5. **Optional Settings**:
   - CTA Text: "Shop Now"
   - CTA URL: "/product"
   - Auto-Slide: Enabled
   - Slide Duration: 5000ms

6. **Click "Create Banner"**

## Testing

1. **Visit Homepage**: `/`
2. **Verify Banner Appears**: Should see your banner
3. **Test Navigation**: Click arrows and dots
4. **Test Auto-Slide**: Wait 5 seconds (if enabled)
5. **Test Mobile**: Resize browser or use mobile device
6. **Test Swipe**: On mobile, swipe left/right

## Common Issues

### Banner Not Showing
- Check banner status is "Active"
- Verify no date restrictions (start/end dates)
- Check device visibility matches your device
- Ensure target_type is "homepage"

### Images Not Loading
- Check file permissions on `public/assets/uploads/banners/`
- Verify image paths in database
- Check browser console for 404 errors

### JavaScript Not Working
- Verify `hero-banner-slider.js` is loaded
- Check browser console for errors
- Ensure JavaScript is enabled

## Next Steps

1. Add 2-3 more banners for slider effect
2. Set different priorities to control order
3. Schedule banners with start/end dates
4. Test on different devices
5. Optimize images (WebP format, compress)

## Support

See `HERO_BANNER_SYSTEM.md` for detailed documentation.

