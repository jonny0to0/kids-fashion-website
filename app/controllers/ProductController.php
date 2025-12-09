<?php
/**
 * Product Controller
 * Handles product listing, detail, and search
 */

class ProductController {
    private $productModel;
    private $categoryModel;
    private $reviewModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->reviewModel = new Review();
    }
    
    /**
     * Product listing
     */
    public function index() {
        $filters = [
            'category_id' => $_GET['category'] ?? null,
            'age_group' => $_GET['age'] ?? null,
            'gender' => $_GET['gender'] ?? null,
            'brand' => $_GET['brand'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'search' => $_GET['search'] ?? null,
            'sort' => $_GET['sort'] ?? null
        ];
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $products = $this->productModel->getProducts($filters, $page);
        $totalProducts = $this->getTotalProducts($filters);
        
        $pagination = new Pagination($totalProducts, ITEMS_PER_PAGE, $page);
        $categories = $this->categoryModel->getAllActive();
        
        $this->render('products/list', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'pagination' => $pagination
        ]);
    }
    
    /**
     * Product detail
     */
    public function detail($slug) {
        $product = $this->productModel->findBySlug($slug);
        
        if (!$product) {
            http_response_code(404);
            require_once VIEW_PATH . '/errors/404.php';
            return;
        }
        
        $images = $this->productModel->getImages($product['product_id']);
        $variants = $this->productModel->getVariants($product['product_id']);
        $reviews = $this->reviewModel->getProductReviews($product['product_id']);
        $rating = $this->reviewModel->getProductRating($product['product_id']);
        $relatedProducts = $this->productModel->getRelated(
            $product['product_id'],
            $product['category_id'],
            4
        );
        
        $this->render('products/detail', [
            'product' => $product,
            'images' => $images,
            'variants' => $variants,
            'reviews' => $reviews,
            'rating' => $rating,
            'relatedProducts' => $relatedProducts
        ]);
    }
    
    /**
     * Search products
     */
    public function search() {
        $query = Validator::sanitize($_GET['q'] ?? '');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        $filters = ['search' => $query];
        $products = $this->productModel->getProducts($filters, $page);
        $totalProducts = $this->getTotalProducts($filters);
        
        $pagination = new Pagination($totalProducts, ITEMS_PER_PAGE, $page);
        
        $this->render('products/search', [
            'products' => $products,
            'query' => $query,
            'pagination' => $pagination
        ]);
    }
    
    /**
     * Get total products count for filters
     */
    private function getTotalProducts($filters) {
        $sql = "SELECT COUNT(*) as total FROM products WHERE status = ?";
        $params = [PRODUCT_STATUS_ACTIVE];
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['age_group'])) {
            $sql .= " AND age_group = ?";
            $params[] = $filters['age_group'];
        }
        
        if (!empty($filters['gender'])) {
            $sql .= " AND gender = ?";
            $params[] = $filters['gender'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->productModel->query($sql, $params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
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

