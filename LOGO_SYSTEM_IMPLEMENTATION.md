# Store Logo System - Dual Type Implementation

## Overview

The Store Logo Configuration system supports two configurable logo types to provide flexibility across branding needs:

1. **Image-Based Logo** - Upload a visual brand asset (PNG, JPG, SVG, WEBP)
2. **Content-Based (Text) Logo** - Display store name as styled text

## Installation

### Step 1: Run Database Migration

Execute the SQL migration file to add logo settings:

```sql
-- Run this file in phpMyAdmin or via command line
source database/add_logo_system.sql
```

Or import `database/add_logo_system.sql` directly in phpMyAdmin.

### Step 2: Access Settings

Navigate to **Admin Dashboard → Settings → General Settings**

## Features

### Image-Based Logo

- **Supported Formats**: PNG, JPG, SVG, WEBP
- **Recommended Dimensions**:
  - Dashboard Sidebar: 160 × 40px
  - Dashboard Header: 140 × 36px  
  - Login Screen: 200 × 60px
- **Size Constraints**: Configurable maximum height and width
- **Auto-scaling**: Images are automatically constrained to fit within limits while maintaining aspect ratio
- **SVG Support**: SVG files are preferred for scalability (no pixelation at any size)

### Text-Based Logo

- **Configurable Properties**:
  - Store name / logo text
  - Font size (separate for sidebar and header)
  - Font weight (400-700)
  - Text color (hex code) - Automatically adapts for navigation bar on white background
  - Maximum width for text wrapping
- **Responsive**: Automatically adapts to all screen sizes
- **Performance**: Faster load time (no image download)
- **Smart Color Adaptation**: 
  - Navigation bar: Uses brand pink (#ec4899) if text color is white/light
  - Admin sidebar/header: Uses configured text color (typically white for dark backgrounds)

### Logo Type Selection

- Radio button toggle between Image and Text logo types
- Only one logo type is active at a time
- Changes take effect immediately after saving

## Configuration Guide

### Setting Up Image Logo

1. Select **"Image-Based Logo"** radio button
2. Click **"Upload Logo Image"** and select your logo file
3. Adjust **Maximum Height** and **Maximum Width** as needed:
   - Recommended: 60px height, 200px width for sidebar
4. Preview appears immediately after upload
5. Click **"Save Changes"**

**Best Practices:**
- Use PNG with transparent background for best results
- SVG files are ideal for scalability
- Keep file size under 2MB
- Use 2× resolution (retina-friendly) if possible

### Setting Up Text Logo

1. Select **"Text-Based Logo"** radio button
2. Enter your store name (or leave empty to use default store name)
3. Configure styling:
   - **Font Size - Sidebar**: 18-22px recommended
   - **Font Size - Header**: 16-18px recommended
   - **Font Weight**: 600-700 (semi-bold to bold) recommended
   - **Text Color**: Use hex code (e.g., #ffffff for white)
   - **Maximum Width**: 200px recommended
4. Preview updates in real-time
5. Click **"Save Changes"**

## Where Logo Appears

The configured logo (image or text) appears in:

1. **Frontend Navigation Bar** - Main website navigation header
2. **Dashboard Sidebar** - Admin left navigation panel
3. **Dashboard Header** - Admin top header area (if applicable)
4. **Login Screen** - Admin login page

### Frontend Navigation Bar

The navigation bar logo uses the same logo configuration but with smart color adaptation:
- **Image Logo**: Uses the same image with appropriate sizing for navigation (typically 40-60px height)
- **Text Logo**: Automatically adapts color for white background:
  - If text color is set to white/light (for dark admin backgrounds), it automatically uses brand pink (#ec4899) for the navigation bar
  - Otherwise, uses the configured text color

## Fallback Logic

The system includes intelligent fallback behavior:

1. **If image logo fails to load** → Falls back to text logo
2. **If both are missing** → Shows default system logo (store name)
3. **Legacy support** → Existing `dashboard_logo` setting is automatically used as fallback if `logo_image` is empty

## Dimension Controls

### Image Logo Dimensions

- **Maximum Height**: Controls the maximum vertical size in pixels
- **Maximum Width**: Controls the maximum horizontal size in pixels
- **Aspect Ratio**: Automatically preserved during scaling
- **Auto-constraint**: System automatically resizes uploaded images to fit constraints

### Text Logo Dimensions

- **Font Size**: Separate controls for sidebar and header
- **Maximum Width**: Text will wrap if it exceeds this width
- **Responsive**: Automatically adapts to container size

## Security & Permissions

- **Admin-only Access**: Only authorized admin roles can change branding
- **File Validation**: 
  - File type checking (PNG, JPG, SVG, WEBP only)
  - File size limits (2MB maximum)
  - Dimension validation
- **Safe Uploads**: Files are stored in `public/assets/uploads/settings/` directory
- **Malware Protection**: Files are validated before upload

## Technical Details

### Database Schema

New settings added:
- `logo_type` - 'image' or 'text'
- `logo_image` - Path to uploaded logo image
- `logo_image_max_height` - Maximum height in pixels
- `logo_image_max_width` - Maximum width in pixels
- `logo_text` - Text content for logo
- `logo_text_font_size_sidebar` - Font size for sidebar
- `logo_text_font_size_header` - Font size for header
- `logo_text_font_weight` - Font weight (400-700)
- `logo_text_color` - Hex color code
- `logo_text_max_width` - Maximum width for text

### File Locations

- **Upload Directory**: `public/assets/uploads/settings/`
- **Image Helper**: `app/helpers/ImageUpload.php`
- **Settings Model**: `app/models/Settings.php`
- **View Files**:
  - Settings form: `app/views/admin/settings/sections/general.php`
  - Admin header: `app/views/layouts/admin_header.php`
  - Login page: `app/views/user/login.php`

### Code Usage

Get logo settings programmatically:

```php
$settingsModel = new Settings();

// Get logo type
$logoType = $settingsModel->get('logo_type', 'text');

// Get image logo path
if ($logoType === 'image') {
    $logoImage = $settingsModel->get('logo_image', '');
    if (empty($logoImage)) {
        // Fallback to legacy dashboard_logo
        $logoImage = $settingsModel->get('dashboard_logo', '');
    }
}

// Get text logo settings
if ($logoType === 'text') {
    $logoText = $settingsModel->get('logo_text', '');
    if (empty($logoText)) {
        $logoText = $settingsModel->get('store_name', 'Store Name');
    }
    $fontSize = $settingsModel->get('logo_text_font_size_sidebar', 20);
    $fontWeight = $settingsModel->get('logo_text_font_weight', 600);
    $textColor = $settingsModel->get('logo_text_color', '#ffffff');
}
```

## Troubleshooting

### Logo Not Appearing

1. **Check file permissions**: Ensure `public/assets/uploads/settings/` is writable
2. **Verify file path**: Check that the logo file exists at the stored path
3. **Clear browser cache**: Logo URLs include cache-busting parameters
4. **Check logo type**: Ensure correct logo type (image/text) is selected

### Image Upload Fails

1. **File size**: Ensure file is under 2MB
2. **File type**: Only PNG, JPG, SVG, WEBP are allowed
3. **File permissions**: Check upload directory permissions
4. **PHP settings**: Verify `upload_max_filesize` and `post_max_size` in php.ini

### Text Logo Not Styling Correctly

1. **Font size**: Ensure values are within valid range (12-32px)
2. **Color format**: Use hex format (e.g., #ffffff)
3. **Cache**: Clear browser cache to see changes

## Performance Considerations

- **Image Logo**: File size impacts load time - optimize images before upload
- **Text Logo**: Minimal performance impact - renders instantly
- **Caching**: Logo URLs include version parameters for cache busting
- **SVG**: Best choice for scalability without performance penalty

## Future Enhancements

Potential future improvements:
- Logo variations for light/dark themes
- Multiple logo sizes for different contexts
- Logo animation options
- Advanced typography controls for text logos
- Logo preview in multiple contexts (sidebar, header, login)

