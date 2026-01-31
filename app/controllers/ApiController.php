<?php
/**
 * API Controller
 * Handles API endpoints for frontend data fetching
 */

class ApiController {
    private $heroBannerModel;
    
    public function __construct() {
        $this->heroBannerModel = new HeroBanner();
    }
    
    /**
     * Get active hero banners
     * Endpoint: /api/hero-banners
     * Query params: target_type, target_id, device_type
     */
    public function heroBanners() {
        header('Content-Type: application/json');
        
        // Get parameters
        $targetType = Validator::sanitize($_GET['target_type'] ?? 'homepage');
        $targetId = !empty($_GET['target_id']) ? (int)$_GET['target_id'] : null;
        
        // Detect device type
        $deviceType = $this->detectDeviceType();
        if (!empty($_GET['device_type'])) {
            $deviceType = Validator::sanitize($_GET['device_type']);
        }
        
        // Get active banners
        $banners = $this->heroBannerModel->getActiveBanners($targetType, $targetId, $deviceType, 5);
        
        // Format response
        $response = [
            'success' => true,
            'data' => array_map(function($banner) {
                return [
                    'id' => $banner['banner_id'],
                    'title' => $banner['title'],
                    'description' => $banner['description'],
                    'desktop_image' => SITE_URL . $banner['desktop_image'],
                    'mobile_image' => SITE_URL . $banner['mobile_image'],
                    'cta_text' => $banner['cta_text'],
                    'cta_url' => $banner['cta_url'],
                    'priority' => (int)$banner['priority'],
                    'auto_slide_enabled' => (bool)$banner['auto_slide_enabled'],
                    'slide_duration' => (int)$banner['slide_duration']
                ];
            }, $banners),
            'count' => count($banners)
        ];
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Detect device type from user agent
     */
    private function detectDeviceType() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Simple device detection
        if (preg_match('/(android|iphone|ipad|ipod|mobile)/i', $userAgent)) {
            return 'mobile';
        }
        
        return 'desktop';
    }
}

