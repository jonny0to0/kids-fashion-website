<?php
/**
 * Home Controller
 * Handles homepage and general site pages
 */

class HomeController {
    private $productModel;
    private $categoryModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    /**
     * Homepage
     */
    public function index() {
        $topProducts = $this->productModel->getBestsellers(6); // For slider
        $newProducts = $this->productModel->getNewArrivals(6); // For slider
        $bestDeals = $this->productModel->getBestDeals(8);
        $topSelling = $this->productModel->getTopSelling(8);
        $featuredProducts = $this->productModel->getFeatured(8);
        $categories = $this->categoryModel->getWithProductCount();
        
        $this->render('home/index', [
            'topProducts' => $topProducts,
            'newProducts' => $newProducts,
            'bestDeals' => $bestDeals,
            'topSelling' => $topSelling,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories
        ]);
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

