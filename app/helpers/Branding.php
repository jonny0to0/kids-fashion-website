<?php
/**
 * Branding Helper Class
 * Centralized logo and branding management
 * Ensures single source of truth for branding state
 */

class Branding {
    
    /**
     * Get logo configuration for rendering
     * This is the SINGLE SOURCE OF TRUTH for branding state
     * Reads fresh from database every time (no caching)
     * 
     * @param string $context 'admin' or 'frontend' - determines which logo styling to use
     * @return array Logo configuration with keys: type, imageUrl, text, textStyle, maxHeight, maxWidth
     */
    public static function getLogo($context = 'frontend') {
        try {
            require_once APP_PATH . '/models/Settings.php';
            $settingsModel = new Settings();
            
            // STEP 1: Get logo_type - this is the PRIMARY decision gate
            // CRITICAL: Read fresh from database (no caching) to ensure we get the saved value
            $logoType = $settingsModel->get('logo_type', 'text');
            
            // CRITICAL FIX: Normalize logo_type value (ensure it's either 'image' or 'text')
            // Trim whitespace and ensure valid value
            $logoType = trim($logoType);
            if (empty($logoType) || !in_array($logoType, ['image', 'text'])) {
                $logoType = 'text'; // Default to text only if invalid or missing
            }
            
            // STEP 2: Get logo configuration based on type
            $config = [
                'type' => $logoType,
                'imageUrl' => '',
                'text' => '',
                'textStyle' => '',
                'maxHeight' => 60,
                'maxWidth' => 200,
                'hasImage' => false
            ];
            
            if ($logoType === 'image') {
                // IMAGE LOGO MODE: Try to load image logo
                // Try logo_image first, then legacy fallbacks
                $logoImagePath = $settingsModel->get('logo_image', '');
                if (empty($logoImagePath)) {
                    $logoImagePath = $settingsModel->get('store_logo', '');
                }
                if (empty($logoImagePath)) {
                    $logoImagePath = $settingsModel->get('dashboard_logo', '');
                }
                
                // If we have an image path, build the URL and verify file exists
                if (!empty($logoImagePath)) {
                    $normalizedPath = '/' . ltrim($logoImagePath, '/');
                    $fullPath = PUBLIC_PATH . $normalizedPath;
                    
                    // Verify file exists on filesystem
                    if (file_exists($fullPath)) {
                        $imageUrl = SITE_URL . $normalizedPath;
                        
                        // Add cache-busting version parameter based on file modification time
                        $version = filemtime($fullPath);
                        $imageUrl .= '?v=' . $version;
                        
                        // Get dimension constraints
                        $config['maxHeight'] = (int)$settingsModel->get('logo_image_max_height', 60);
                        $config['maxWidth'] = (int)$settingsModel->get('logo_image_max_width', 200);
                        
                        $config['imageUrl'] = $imageUrl;
                        $config['hasImage'] = true;
                    } else {
                        // File doesn't exist - log warning but keep logo_type as 'image'
                        // This allows admin to see that image mode is selected but file is missing
                        error_log("Branding::getLogo - Image logo selected but file not found: {$fullPath}");
                        $config['hasImage'] = false;
                    }
                } else {
                    // No image path configured - log warning
                    error_log("Branding::getLogo - Image logo selected but no image path configured");
                    $config['hasImage'] = false;
                }
            }
            
            // STEP 3: Prepare text logo as fallback (or primary if logo_type is 'text')
            // Show text logo if:
            //   - logo_type is 'text', OR
            //   - logo_type is 'image' but image is not available (for graceful fallback)
            if ($logoType === 'text' || ($logoType === 'image' && !$config['hasImage'])) {
                // Get text logo content
                $logoText = $settingsModel->get('logo_text', '');
                if (empty($logoText)) {
                    $logoText = $settingsModel->get('store_name', SITE_NAME);
                }
                
                // Get text styling based on context
                if ($context === 'admin') {
                    // Admin sidebar styling (dark background, light text)
                    $fontSize = (int)$settingsModel->get('logo_text_font_size_sidebar', 20);
                    $fontWeight = (int)$settingsModel->get('logo_text_font_weight', 600);
                    $textColor = $settingsModel->get('logo_text_color', '#ffffff');
                } else {
                    // Frontend header styling (light background, darker text)
                    $fontSize = (int)$settingsModel->get('logo_text_font_size_header', 18);
                    $fontWeight = (int)$settingsModel->get('logo_text_font_weight', 600);
                    $textColor = $settingsModel->get('logo_text_color', '#ec4899');
                    
                    // If text color is white/light (designed for dark backgrounds), use brand color for nav
                    if (strtolower($textColor) === '#ffffff' || strtolower($textColor) === '#fff') {
                        $textColor = '#ec4899';
                    }
                }
                
                $maxWidth = (int)$settingsModel->get('logo_text_max_width', 200);
                
                $config['text'] = $logoText;
                $config['textStyle'] = sprintf(
                    'font-size: %dpx; font-weight: %d; color: %s; max-width: %dpx;',
                    $fontSize,
                    $fontWeight,
                    htmlspecialchars($textColor),
                    $maxWidth
                );
            }
            
            return $config;
            
        } catch (Exception $e) {
            // Fallback configuration on error
            error_log("Branding::getLogo - Error loading logo settings: " . $e->getMessage());
            return [
                'type' => 'text',
                'imageUrl' => '',
                'text' => SITE_NAME,
                'textStyle' => $context === 'admin' 
                    ? 'font-size: 20px; font-weight: 600; color: #ffffff;'
                    : 'font-size: 20px; font-weight: 600; color: #ec4899;',
                'maxHeight' => 60,
                'maxWidth' => 200,
                'hasImage' => false
            ];
        }
    }
    
    /**
     * Render logo HTML based on configuration
     * Returns just the logo content (image or text), NOT wrapped in anchor tag
     * The calling code should wrap it in an anchor tag if needed
     * 
     * @param array $config Logo configuration from getLogo()
     * @param array $imgAttributes Additional attributes for img tag (e.g., ['class' => 'custom-class'])
     * @param array $textAttributes Additional attributes for text span (e.g., ['class' => 'custom-class'])
     * @return string HTML for logo (image or text span, without anchor tag)
     */
    public static function renderLogo($config, $linkUrl = null, $imgAttributes = [], $textAttributes = []) {
        // Default attributes
        $defaultImgAttrs = [
            'alt' => SITE_NAME . ' Logo',
            'class' => 'w-auto object-contain'
        ];
        $defaultTextAttrs = [
            'class' => 'text-xl font-semibold'
        ];
        
        // Merge with provided attributes
        $imgAttrs = array_merge($defaultImgAttrs, $imgAttributes);
        $textAttrs = array_merge($defaultTextAttrs, $textAttributes);
        
        // Build attribute strings
        $imgAttrString = '';
        foreach ($imgAttrs as $key => $value) {
            $imgAttrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        
        $textAttrString = '';
        foreach ($textAttrs as $key => $value) {
            $textAttrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        
        $html = '';
        
        // Wrap in anchor if linkUrl is provided
        if ($linkUrl !== null) {
            $html .= '<a href="' . htmlspecialchars($linkUrl) . '" class="flex items-center">';
        }
        
        if ($config['type'] === 'image' && $config['hasImage']) {
            // Render image logo
            $style = sprintf(
                'max-height: %dpx; max-width: %dpx; height: auto;',
                $config['maxHeight'],
                $config['maxWidth']
            );
            $html .= '<img src="' . htmlspecialchars($config['imageUrl']) . '" ' . $imgAttrString;
            $html .= ' style="' . htmlspecialchars($style) . '">';
        } else {
            // Render text logo (either because logo_type is 'text', or image is unavailable)
            $html .= '<span ' . $textAttrString . ' style="' . htmlspecialchars($config['textStyle']) . '">';
            $html .= htmlspecialchars($config['text']);
            $html .= '</span>';
            
            // If image was selected but unavailable, add helpful title attribute
            if ($config['type'] === 'image' && !$config['hasImage']) {
                $html = str_replace('<span ', '<span title="Image logo selected but file not found. Please upload a logo image." ', $html);
            }
        }
        
        // Close anchor if opened
        if ($linkUrl !== null) {
            $html .= '</a>';
        }
        
        return $html;
    }
}

