# Enterprise-Level Hero Banner System

## Overview

This is a comprehensive, enterprise-grade hero banner management system designed for e-commerce platforms. It follows industry standards used by major platforms like Amazon and Flipkart, providing full admin control, scheduling, priority management, and performance optimization.

## Features

### Core Functionality
- ✅ **Data-Driven**: All banners are stored in the database, no hard-coded content
- ✅ **Admin Dashboard**: Full CRUD operations for banner management
- ✅ **Priority-Based Rendering**: Higher priority banners appear first
- ✅ **Date Scheduling**: Start and end dates for campaign management
- ✅ **Status Control**: Active/Inactive toggle for each banner
- ✅ **Overlap Prevention**: Validation prevents conflicting campaigns
- ✅ **Device-Specific Images**: Separate desktop and mobile images
- ✅ **Targeting**: Homepage, category, or campaign-specific banners
- ✅ **Drag & Drop Reordering**: Visual reordering in admin panel

### Frontend Features
- ✅ **Responsive Design**: Fully responsive across all devices
- ✅ **Auto-Slide**: Configurable auto-slide (4-6 seconds)
- ✅ **Pause on Hover**: Auto-slide pauses on user interaction
- ✅ **Swipe Gestures**: Touch/swipe support for mobile devices
- ✅ **Manual Navigation**: Arrow buttons and pagination dots
- ✅ **Lazy Loading**: Images load efficiently for better performance
- ✅ **WebP Support**: Optimized image formats for fast loading
- ✅ **LCP Optimization**: Optimized for Largest Contentful Paint

### Performance & SEO
- ✅ **Lazy Loading**: Non-critical images load on demand
- ✅ **CDN Ready**: Image paths support CDN integration
- ✅ **LCP Optimization**: First banner image prioritized for fast loading
- ✅ **Minimal JavaScript**: Lightweight, performant slider code
- ✅ **SEO Friendly**: Proper alt tags and semantic HTML

## Database Schema

The system uses the `hero_banners` table with the following key fields:

- `banner_id`: Primary key
- `title`: Banner title
- `description`: Banner description text
- `desktop_image`: Desktop banner image path
- `mobile_image`: Mobile banner image path
- `cta_text`: Call-to-action button text
- `cta_url`: Call-to-action redirect URL
- `priority`: Display priority (0-100, higher = first)
- `status`: Active/Inactive status
- `device_visibility`: Desktop/Mobile/Both
- `target_type`: Homepage/Category/Campaign
- `target_id`: Category or Campaign ID
- `start_date`: Campaign start date/time
- `end_date`: Campaign end date/time
- `display_order`: Manual ordering for drag-and-drop
- `auto_slide_enabled`: Enable/disable auto-slide
- `slide_duration`: Auto-slide duration in milliseconds

## Installation

1. **Run Database Migration**:
   ```sql
   source database/add_hero_banners_system.sql
   ```

2. **Verify Files**:
   - `app/models/HeroBanner.php` - Model class
   - `app/controllers/AdminController.php` - Admin methods added
   - `app/controllers/ApiController.php` - API endpoint
   - `app/views/admin/hero_banners/` - Admin views
   - `public/assets/js/hero-banner-slider.js` - Frontend slider

3. **Access Admin Panel**:
   - Navigate to: `/admin/hero-banners`
   - Add, edit, delete, and manage banners

## Usage

### Admin Panel

#### Adding a Banner
1. Go to `/admin/hero-banners`
2. Click "Add New Banner"
3. Fill in the form:
   - **Title**: Banner headline
   - **Description**: Supporting text
   - **Desktop Image**: Upload 1920x600px image (WebP recommended)
   - **Mobile Image**: Upload 768x400px image (WebP recommended)
   - **CTA Text**: Button text (e.g., "Shop Now")
   - **CTA URL**: Redirect URL (e.g., "/product?category=summer")
   - **Priority**: 0-100 (higher = appears first)
   - **Target Type**: Homepage, Category, or Campaign
   - **Device Visibility**: Desktop, Mobile, or Both
   - **Start/End Date**: Optional scheduling
   - **Auto-Slide**: Enable/disable and set duration

#### Reordering Banners
- Use drag-and-drop in the admin list view
- Banners are ordered by: Priority → Display Order → Created Date

#### Managing Banners
- **Edit**: Click edit icon
- **Toggle Status**: Click activate/deactivate icon
- **Delete**: Click delete icon (with confirmation)

### Frontend

The hero banner section automatically displays on the homepage:
- Fetches active banners from database
- Filters by date range and device type
- Orders by priority and display order
- Limits to maximum 5 banners

#### API Endpoint

Banners can be fetched via API:
```
GET /api/hero-banners?target_type=homepage&device_type=desktop
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Summer Sale 2024",
      "description": "Get up to 50% off",
      "desktop_image": "http://site.com/path/to/image.webp",
      "mobile_image": "http://site.com/path/to/mobile.webp",
      "cta_text": "Shop Now",
      "cta_url": "/product?category=summer",
      "priority": 10,
      "auto_slide_enabled": true,
      "slide_duration": 5000
    }
  ],
  "count": 1
}
```

## Best Practices

### Image Optimization
1. **Format**: Use WebP format for best compression
2. **Desktop Size**: 1920x600px recommended
3. **Mobile Size**: 768x400px recommended
4. **File Size**: Keep under 200KB per image
5. **Quality**: Balance quality vs. file size (80-85% quality)

### Banner Management
1. **Limit Active Banners**: Maximum 3-5 active banners for best UX
2. **Priority Strategy**: Use priority 90-100 for urgent campaigns
3. **Scheduling**: Set end dates to automatically disable expired campaigns
4. **Testing**: Test banners on both desktop and mobile before going live

### Performance
1. **Lazy Loading**: First banner loads immediately, others lazy load
2. **CDN**: Serve images from CDN for faster global delivery
3. **Caching**: Enable browser caching for banner images
4. **Compression**: Compress images before upload

## Technical Details

### Model Methods
- `getActiveBanners()`: Fetch active banners with filtering
- `getAllBanners()`: Get all banners for admin
- `checkOverlap()`: Validate date range overlaps
- `validate()`: Comprehensive data validation
- `toggleStatus()`: Toggle active/inactive
- `bulkUpdateDisplayOrders()`: Update drag-and-drop order

### Controller Methods
- `heroBanners()`: List all banners (admin)
- `heroBannerAdd()`: Add new banner
- `heroBannerEdit()`: Edit existing banner
- `heroBannerDelete()`: Delete banner
- `heroBannerToggleStatus()`: Toggle status
- `heroBannerUpdateOrder()`: Update display order (AJAX)

### JavaScript Features
- Auto-slide with configurable duration
- Pause on hover/interaction
- Swipe gesture support
- Keyboard navigation (arrow keys)
- Visibility API integration (pause when tab hidden)
- Smooth transitions
- Lazy loading for non-active slides

## Troubleshooting

### Banners Not Showing
1. Check banner status is "Active"
2. Verify date range (start/end dates)
3. Check device visibility settings
4. Ensure target_type matches page context
5. Check priority and display_order

### Images Not Loading
1. Verify image paths are correct
2. Check file permissions
3. Ensure images are uploaded to correct directory
4. Verify WebP support in browser

### Auto-Slide Not Working
1. Check `auto_slide_enabled` is true
2. Verify `slide_duration` is between 4000-6000ms
3. Ensure JavaScript is loaded correctly
4. Check browser console for errors

## Future Enhancements

The system is designed for scalability and can support:
- **A/B Testing**: Track banner performance
- **Personalization**: User-specific banners
- **Analytics**: Click tracking and conversion metrics
- **Multi-language**: Support for multiple languages
- **Video Banners**: Support for video content
- **Animation**: Custom CSS animations per banner

## Support

For issues or questions:
1. Check this documentation
2. Review code comments
3. Check browser console for JavaScript errors
4. Verify database records directly

## License

This system is part of the Kids Bazaar E-commerce Platform.

