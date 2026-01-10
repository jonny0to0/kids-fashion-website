<?php
/**
 * Home Controller
 * Handles homepage and general site pages
 */

class HomeController {
    private $productModel;
    private $categoryModel;
    private $heroBannerModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->heroBannerModel = new HeroBanner();
    }
    
    /**
     * Homepage
     */
    public function index() {
        // Get active hero banners for homepage
        $deviceType = $this->detectDeviceType();
        $heroBanners = $this->heroBannerModel->getActiveBanners('homepage', null, $deviceType, 5);
        
        // Ensure heroBanners is always an array
        if (!is_array($heroBanners)) {
            $heroBanners = [];
        }
        
        // Log banner data in development mode for debugging
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            error_log("HomeController::index() - Found " . count($heroBanners) . " hero banners. Device type: {$deviceType}");
            if (empty($heroBanners)) {
                error_log("HomeController::index() - No banners found. This could indicate a query issue or no active banners match the criteria.");
            }
        }
        
        $topProducts = $this->productModel->getBestsellers(6); // For slider
        $newProducts = $this->productModel->getNewArrivals(6); // For slider
        $bestDeals = $this->productModel->getBestDeals(8);
        $topSelling = $this->productModel->getTopSelling(8);
        $featuredProducts = $this->productModel->getFeatured(8);
        $categories = $this->categoryModel->getParentCategories(); // For category slider
        
        $this->render('home/index', [
            'heroBanners' => $heroBanners,
            'topProducts' => $topProducts,
            'newProducts' => $newProducts,
            'bestDeals' => $bestDeals,
            'topSelling' => $topSelling,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories
        ]);
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
    
    /**
     * Render view
     */
    private function render($view, $data = []) {
        extract($data);
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/' . $view . '.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }
}

