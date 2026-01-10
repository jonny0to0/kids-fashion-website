<?php
/**
 * Product Controller
 * Handles product listing, detail, and search
 */

class ProductController {
    private $productModel;
    private $reviewModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->reviewModel = new Review();
    }
    
    /**
     * Product listing
     */
    public function index() {
        $filters = [
            'category' => $_GET['category'] ?? null,
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
        
        $this->render('products/list', [
            'products' => $products,
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
        $relatedProducts = [];
        
        // Fetch product attributes and organize by groups
        require_once APP_PATH . '/models/Attribute.php';
        $attributeModel = new CategoryAttribute();
        $allAttributes = $attributeModel->getProductAttributes($product['product_id']);
        
        // Organize attributes by groups
        $attributesByGroup = [];
        $groups = [];
        
        foreach ($allAttributes as $attr) {
            $groupId = $attr['group_id'] ?? 0;
            $groupName = $attr['group_name'] ?? 'Other';
            $groupDisplayOrder = $attr['group_display_order'] ?? 999;
            
            // Store group metadata
            if (!isset($groups[$groupId])) {
                $groups[$groupId] = [
                    'group_id' => $groupId,
                    'group_name' => $groupName,
                    'display_order' => $groupDisplayOrder
                ];
            }
            
            // Only include attributes that have values (non-empty attribute_value)
            if (!empty($attr['attribute_value'])) {
                if (!isset($attributesByGroup[$groupId])) {
                    $attributesByGroup[$groupId] = [];
                }
                $attributesByGroup[$groupId][] = $attr;
            }
        }
        
        // Sort groups by display_order
        uasort($groups, function($a, $b) {
            if ($a['display_order'] == $b['display_order']) {
                return strcmp($a['group_name'], $b['group_name']);
            }
            return $a['display_order'] <=> $b['display_order'];
        });
        
        // Sort attributes within each group by display_order
        foreach ($attributesByGroup as $groupId => &$attrs) {
            usort($attrs, function($a, $b) {
                return ($a['display_order'] ?? 0) <=> ($b['display_order'] ?? 0);
            });
        }
        unset($attrs);
        
        $this->render('products/detail', [
            'product' => $product,
            'images' => $images,
            'variants' => $variants,
            'reviews' => $reviews,
            'rating' => $rating,
            'relatedProducts' => $relatedProducts,
            'productAttributes' => $allAttributes, // Keep flat list for backward compatibility
            'attributesByGroup' => $attributesByGroup, // New: organized by groups
            'attributeGroups' => $groups // Group metadata
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
        $sql = "SELECT COUNT(*) as total FROM products p";
        $params = [];
        
        if (!empty($filters['category'])) {
            $sql .= " INNER JOIN categories c ON p.category_id = c.category_id WHERE p.status = ? AND c.slug = ? AND c.is_active = 1";
            $params[] = PRODUCT_STATUS_ACTIVE;
            $params[] = $filters['category'];
        } else {
            $sql .= " WHERE p.status = ?";
            $params[] = PRODUCT_STATUS_ACTIVE;
        }
        
        if (!empty($filters['age_group'])) {
            $sql .= " AND p.age_group = ?";
            $params[] = $filters['age_group'];
        }
        
        if (!empty($filters['gender'])) {
            $sql .= " AND p.gender = ?";
            $params[] = $filters['gender'];
        }
        
        if (!empty($filters['brand'])) {
            $sql .= " AND p.brand = ?";
            $params[] = $filters['brand'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND (COALESCE(p.sale_price, p.price) >= ?)";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND (COALESCE(p.sale_price, p.price) <= ?)";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
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

