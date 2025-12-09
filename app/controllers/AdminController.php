<?php
/**
 * Admin Controller
 * Handles admin dashboard and management features
 * Requires admin authentication
 */

class AdminController {
    private $productModel;
    private $categoryModel;
    private $orderModel;
    private $userModel;
    
    public function __construct() {
        $this->requireAdmin();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->orderModel = new Order();
        $this->userModel = new User();
    }
    
    /**
     * Admin Dashboard
     */
    public function index() {
        // Get statistics
        $stats = [
            'total_products' => $this->productModel->count(),
            'total_orders' => $this->orderModel->count(),
            'total_customers' => $this->userModel->count(['user_type' => USER_TYPE_CUSTOMER]),
            'total_categories' => $this->categoryModel->count(['is_active' => true])
        ];
        
        // Get recent orders
        $recentOrders = $this->orderModel->getRecentOrders(5);
        
        $this->render('admin/dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders
        ]);
    }
    
    /**
     * Product Management - List all products
     */
    public function products() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = Validator::sanitize($_GET['search'] ?? '');
        
        $sql = "SELECT p.*, c.name as category_name,
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id";
        
        $params = [];
        $where = [];
        
        if (!empty($search)) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        // Get total count - build a proper COUNT query
        $countSql = "SELECT COUNT(*) as total FROM products p LEFT JOIN categories c ON p.category_id = c.category_id";
        if (!empty($where)) {
            $countSql .= " WHERE " . implode(" AND ", $where);
        }
        $countStmt = $this->productModel->query($countSql, $params);
        $total = $countStmt->fetch()['total'] ?? 0;
        
        // Add pagination
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->productModel->query($sql, $params);
        $products = $stmt->fetchAll();
        
        $pagination = new Pagination($total, $perPage, $page);
        
        $this->render('admin/products', [
            'products' => $products,
            'pagination' => $pagination,
            'search' => $search
        ]);
    }
    
    /**
     * Add new product
     */
    public function productAdd() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProductSave();
        } else {
            $categories = $this->categoryModel->getAllActive();
            $this->render('admin/product_form', [
                'product' => null,
                'categories' => $categories,
                'action' => 'Add'
            ]);
        }
    }
    
    /**
     * Edit product
     */
    public function productEdit($id) {
        $product = $this->productModel->find($id);
        
        if (!$product) {
            Session::setFlash('error', 'Product not found');
            header('Location: ' . SITE_URL . '/admin/products');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProductSave($id);
        } else {
            $categories = $this->categoryModel->getAllActive();
            $images = $this->productModel->getImages($id);
            $variants = $this->productModel->getVariants($id);
            
            $this->render('admin/product_form', [
                'product' => $product,
                'categories' => $categories,
                'images' => $images,
                'variants' => $variants,
                'action' => 'Edit'
            ]);
        }
    }
    
    /**
     * Delete product
     */
    public function productDelete($id) {
        if ($this->productModel->delete($id)) {
            Session::setFlash('success', 'Product deleted successfully');
        } else {
            Session::setFlash('error', 'Failed to delete product');
        }
        
        header('Location: ' . SITE_URL . '/admin/products');
        exit;
    }
    
    /**
     * Handle product save (add/edit)
     */
    private function handleProductSave($productId = null) {
        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'name' => Validator::sanitize($_POST['name'] ?? ''),
            'slug' => $this->generateSlug($_POST['name'] ?? ''),
            'description' => Validator::sanitize($_POST['description'] ?? ''),
            'short_description' => Validator::sanitize($_POST['short_description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
            'cost_price' => !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : null,
            'sku' => Validator::sanitize($_POST['sku'] ?? ''),
            'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
            'age_group' => Validator::sanitize($_POST['age_group'] ?? ''),
            'gender' => Validator::sanitize($_POST['gender'] ?? ''),
            'brand' => Validator::sanitize($_POST['brand'] ?? ''),
            'material' => Validator::sanitize($_POST['material'] ?? ''),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_new_arrival' => isset($_POST['is_new_arrival']) ? 1 : 0,
            'is_bestseller' => isset($_POST['is_bestseller']) ? 1 : 0,
            'status' => Validator::sanitize($_POST['status'] ?? PRODUCT_STATUS_ACTIVE)
        ];
        
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Product name is required';
        }
        
        if (empty($data['category_id'])) {
            $errors[] = 'Category is required';
        }
        
        if (empty($errors)) {
            if ($productId) {
                // Update existing product
                $data['updated_at'] = date('Y-m-d H:i:s');
                if ($this->productModel->update($productId, $data)) {
                    // Handle image upload
                    $this->handleProductImages($productId);
                    
                    Session::setFlash('success', 'Product updated successfully');
                    header('Location: ' . SITE_URL . '/admin/products');
                    exit;
                } else {
                    $errors[] = 'Failed to update product';
                }
            } else {
                // Create new product
                $data['created_at'] = date('Y-m-d H:i:s');
                $newProductId = $this->productModel->create($data);
                
                if ($newProductId) {
                    // Handle image upload
                    $this->handleProductImages($newProductId);
                    
                    Session::setFlash('success', 'Product added successfully');
                    header('Location: ' . SITE_URL . '/admin/products');
                    exit;
                } else {
                    $errors[] = 'Failed to create product';
                }
            }
        }
        
        $categories = $this->categoryModel->getAllActive();
        $this->render('admin/product_form', [
            'product' => $productId ? $this->productModel->find($productId) : null,
            'categories' => $categories,
            'errors' => $errors,
            'data' => $data,
            'action' => $productId ? 'Edit' : 'Add'
        ]);
    }
    
    /**
     * Handle product image uploads
     */
    private function handleProductImages($productId) {
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $uploader = new ImageUpload();
            $uploadDir = 'products';
            
            $results = $uploader->uploadMultiple($_FILES['images'], $uploadDir, 'product_');
            
            foreach ($results as $index => $result) {
                if ($result['success']) {
                    // Ensure relative path starts with /
                    $imageUrl = $result['relative_path'];
                    if (strpos($imageUrl, '/') !== 0) {
                        $imageUrl = '/' . $imageUrl;
                    }
                    
                    $sql = "INSERT INTO product_images (product_id, image_url, is_primary, display_order, created_at) 
                            VALUES (?, ?, ?, ?, ?)";
                    $this->productModel->query($sql, [
                        $productId,
                        $imageUrl,
                        ($index === 0) ? 1 : 0,
                        $index,
                        date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }
    
    /**
     * Generate URL slug from name
     */
    private function generateSlug($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');
        return $text . '-' . time();
    }
    
    /**
     * Category Management - List all categories
     */
    public function categories() {
        $categories = $this->categoryModel->getWithProductCount();
        $this->render('admin/categories', ['categories' => $categories]);
    }
    
    /**
     * Add new category
     */
    public function categoryAdd() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCategorySave();
        } else {
            $parentCategories = $this->categoryModel->getParents();
            $this->render('admin/category_form', [
                'category' => null,
                'parentCategories' => $parentCategories,
                'action' => 'Add'
            ]);
        }
    }
    
    /**
     * Edit category
     */
    public function categoryEdit($id) {
        $category = $this->categoryModel->find($id);
        
        if (!$category) {
            Session::setFlash('error', 'Category not found');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCategorySave($id);
        } else {
            $parentCategories = $this->categoryModel->getParents();
            $this->render('admin/category_form', [
                'category' => $category,
                'parentCategories' => $parentCategories,
                'action' => 'Edit'
            ]);
        }
    }
    
    /**
     * Delete category
     */
    public function categoryDelete($id) {
        if ($this->categoryModel->delete($id)) {
            Session::setFlash('success', 'Category deleted successfully');
        } else {
            Session::setFlash('error', 'Failed to delete category');
        }
        
        header('Location: ' . SITE_URL . '/admin/categories');
        exit;
    }
    
    /**
     * Handle category save (add/edit)
     */
    private function handleCategorySave($categoryId = null) {
        $data = [
            'name' => Validator::sanitize($_POST['name'] ?? ''),
            'slug' => $this->generateSlug($_POST['name'] ?? ''),
            'description' => Validator::sanitize($_POST['description'] ?? ''),
            'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'display_order' => (int)($_POST['display_order'] ?? 0)
        ];
        
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Category name is required';
        }
        
        if (empty($errors)) {
            if ($categoryId) {
                // Update existing category
                if ($this->categoryModel->update($categoryId, $data)) {
                    Session::setFlash('success', 'Category updated successfully');
                    header('Location: ' . SITE_URL . '/admin/categories');
                    exit;
                } else {
                    $errors[] = 'Failed to update category';
                }
            } else {
                // Create new category
                if ($this->categoryModel->create($data)) {
                    Session::setFlash('success', 'Category added successfully');
                    header('Location: ' . SITE_URL . '/admin/categories');
                    exit;
                } else {
                    $errors[] = 'Failed to create category';
                }
            }
        }
        
        $parentCategories = $this->categoryModel->getParents();
        $this->render('admin/category_form', [
            'category' => $categoryId ? $this->categoryModel->find($categoryId) : null,
            'parentCategories' => $parentCategories,
            'errors' => $errors,
            'data' => $data,
            'action' => $categoryId ? 'Edit' : 'Add'
        ]);
    }
    
    /**
     * Require admin authentication
     */
    private function requireAdmin() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. Admin privileges required.');
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }
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

