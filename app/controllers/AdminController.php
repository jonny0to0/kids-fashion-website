<?php
/**
 * Admin Controller
 * Handles admin dashboard and management features
 * Requires admin authentication
 */

class AdminController
{
    private $productModel;
    private $orderModel;
    private $userModel;
    private $categoryModel;
    private $attributeModel;
    private $heroBannerModel;

    public function __construct()
    {
        $this->requireAdmin();
        // Models are loaded via autoloader when instantiated
        // The autoloader will automatically load model files when new ModelName() is called
        require_once APP_PATH . '/models/Attribute.php';

        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->userModel = new User();
        $this->categoryModel = new Category();
        $this->attributeModel = new CategoryAttribute();
        $this->heroBannerModel = new HeroBanner();
    }

    /**
     * Admin Dashboard
     */
    public function index()
    {
        // Get statistics
        $oneWeekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

        // Calculate weekly growth
        $productsThisWeek = $this->productModel->query("SELECT COUNT(*) as total FROM products WHERE created_at >= ?", [$oneWeekAgo])->fetch()['total'] ?? 0;
        $customersThisWeek = $this->userModel->query("SELECT COUNT(*) as total FROM users WHERE user_type = ? AND created_at >= ?", [USER_TYPE_CUSTOMER, $oneWeekAgo])->fetch()['total'] ?? 0;
        $categoriesThisWeek = $this->categoryModel->query("SELECT COUNT(*) as total FROM categories WHERE created_at >= ?", [$oneWeekAgo])->fetch()['total'] ?? 0;

        // Get hero banners with status
        $allBanners = $this->heroBannerModel->getAllBanners();
        $activeBanners = array_filter($allBanners, function ($banner) {
            return ($banner['status'] ?? 'inactive') === 'active';
        });

        $stats = [
            'total_products' => $this->productModel->count(),
            'products_this_week' => $productsThisWeek,
            'total_orders' => $this->orderModel->count(),
            'total_customers' => $this->userModel->count(['user_type' => USER_TYPE_CUSTOMER]),
            'customers_this_week' => $customersThisWeek,
            'total_categories' => $this->categoryModel->count(),
            'categories_this_week' => $categoriesThisWeek,
            'total_hero_banners' => count($allBanners),
            'active_hero_banners' => count($activeBanners),
            'all_banners' => $allBanners
        ];

        // Get recent orders
        $recentOrders = $this->orderModel->getRecentOrders(5);

        // Get orders and revenue data for last 7 days for charts
        $ordersPerDay = $this->getOrdersPerDay(7);
        $revenuePerDay = $this->getRevenuePerDay(7);

        $this->render('admin/dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'ordersPerDay' => $ordersPerDay,
            'revenuePerDay' => $revenuePerDay
        ]);
    }

    /**
     * Generate Order Invoice
     */
    public function ordersInvoice($orderNumber)
    {
        $order = $this->orderModel->findByOrderNumber($orderNumber);

        if (!$order) {
            Session::setFlash('error', 'Order not found');
            header('Location: ' . SITE_URL . '/admin/orders');
            exit;
        }

        $items = $this->orderModel->getOrderItems($order['order_id']);
        
        // Get full address details
        require_once APP_PATH . '/models/Address.php';
        $addressModel = new Address();
        $shippingAddress = $addressModel->find($order['shipping_address_id']);
        $billingAddress = $addressModel->find($order['billing_address_id']);

        $this->render('admin/orders/invoice', [
            'pageTitle' => 'Invoice #' . $order['order_number'],
            'order' => $order,
            'items' => $items,
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress
        ]);
    }

    /**
     * Get orders count per day for last N days
     */
    private function getOrdersPerDay($days = 7)
    {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        $stmt = $this->orderModel->query($sql, [$days]);
        return $stmt->fetchAll();
    }

    /**
     * Get revenue per day for last N days
     */
    private function getRevenuePerDay($days = 7)
    {
        $sql = "SELECT DATE(created_at) as date, SUM(final_amount) as revenue 
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        $stmt = $this->orderModel->query($sql, [$days]);
        return $stmt->fetchAll();
    }

    /**
     * Get revenue trend data for chart with optional filters
     */
    /**
     * Get revenue trend data for chart
     * 
     * IMPORTANT: Revenue Trend Chart Rules
     * - Only shows delivered orders (revenue-contributing orders)
     * - Independent of Quick Insights card clicks
     * - Only accepts revenue-specific date filters (revenue_date_from, revenue_date_to)
     * - Never affected by order_status or payment_status filters from cards
     * 
     * @param int $days Default number of days to show (30)
     * @param array $revenueFilters Only revenue-specific filters (revenue_date_from, revenue_date_to)
     * @return array Revenue trend data
     */
    private function getRevenueTrend($days = 30, $revenueFilters = [])
    {
        $where = [];
        $params = [];

        // ALWAYS filter to only delivered orders (revenue-contributing orders)
        // This ensures the chart shows actual revenue, not pending/cancelled orders
        $where[] = "order_status = ?";
        $params[] = ORDER_STATUS_DELIVERED;

        // Only accept revenue-specific date filters (not table filters)
        // These come from the chart's own date picker, not from Quick Insights cards
        if (!empty($revenueFilters['revenue_date_from'])) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $revenueFilters['revenue_date_from'];
        } else {
            // Default: last N days
            $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
            $params[] = $days;
        }

        if (!empty($revenueFilters['revenue_date_to'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $revenueFilters['revenue_date_to'];
        }

        // Build WHERE clause
        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $sql = "SELECT DATE(created_at) as date, SUM(final_amount) as revenue 
                FROM orders 
                {$whereClause}
                GROUP BY DATE(created_at)
                ORDER BY date ASC";

        $stmt = $this->orderModel->query($sql, $params);
        $results = $stmt->fetchAll();
        return $results !== false ? $results : [];
    }

    /**
     * Product Management - List all products
     */
    public function products()
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $search = Validator::sanitize($_GET['search'] ?? '');

        $sql = "SELECT p.*,
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p";

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
        $countSql = "SELECT COUNT(*) as total FROM products p";
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
     * Product Details & Sales View
     */
    public function productDetails($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            Session::setFlash('error', 'Product not found');
            header('Location: ' . SITE_URL . '/admin/products');
            exit;
        }
        
        // Get primary image
        $primaryImage = $this->productModel->query(
            "SELECT image_url FROM product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1", 
            [$id]
        )->fetch();
        $product['primary_image'] = $primaryImage['image_url'] ?? null;
        
        // Get sales stats
        $salesStats = $this->orderModel->getSalesStatsByProduct($id);
        
        // Get recent orders for this product
        $productOrders = $this->orderModel->getOrdersByProduct($id, 50);

        $this->render('admin/products/details', [
            'pageTitle' => 'Dashboard',
            'product' => $product,
            'salesStats' => $salesStats,
            'orders' => $productOrders
        ]);
    }

    /**
     * Products Inventory Management
     */
    public function productsInventory()
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $search = Validator::sanitize($_GET['search'] ?? '');
        $stockFilter = isset($_GET['stock']) ? $_GET['stock'] : 'all'; // all, low, out

        $sql = "SELECT p.*,
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                p.stock_quantity as current_stock
                FROM products p";

        $params = [];
        $where = [];

        if (!empty($search)) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Stock filter - using default low stock threshold of 10
        // Note: low_stock_threshold column doesn't exist in products table, using default value
        $defaultLowStockThreshold = 10;
        if ($stockFilter === 'low') {
            $where[] = "p.stock_quantity > 0 AND p.stock_quantity <= ?";
            $params[] = $defaultLowStockThreshold;
        } elseif ($stockFilter === 'out') {
            $where[] = "p.stock_quantity <= 0";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY p.stock_quantity ASC, p.name ASC";

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM products p";
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

        require_once APP_PATH . '/helpers/Pagination.php';
        $pagination = new Pagination($total, $perPage, $page);

        $pageTitle = 'Inventory Management';
        $this->render('admin/products/inventory', [
            'pageTitle' => $pageTitle,
            'products' => $products,
            'pagination' => $pagination,
            'search' => $search,
            'stockFilter' => $stockFilter
        ]);
    }

    /**
     * Product Reviews Management
     */
    public function productsReviews()
    {
        require_once APP_PATH . '/models/Review.php';
        $reviewModel = new Review();

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $search = Validator::sanitize($_GET['search'] ?? '');
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all'; // all, approved, pending

        $sql = "SELECT r.*, p.name as product_name, p.sku, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as product_image,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name, u.email as customer_email
                FROM reviews r
                JOIN products p ON r.product_id = p.product_id
                JOIN users u ON r.user_id = u.user_id";

        $params = [];
        $where = [];

        if (!empty($search)) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR r.comment LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Status filter
        if ($statusFilter === 'approved') {
            $where[] = "r.is_approved = 1";
        } elseif ($statusFilter === 'pending') {
            $where[] = "r.is_approved = 0";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY r.created_at DESC";

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM reviews r
                     JOIN products p ON r.product_id = p.product_id
                     JOIN users u ON r.user_id = u.user_id";
        if (!empty($where)) {
            $countSql .= " WHERE " . implode(" AND ", $where);
        }
        $countStmt = $reviewModel->query($countSql, $params);
        $total = $countStmt->fetch()['total'] ?? 0;

        // Add pagination
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $reviewModel->query($sql, $params);
        $reviews = $stmt->fetchAll();

        require_once APP_PATH . '/helpers/Pagination.php';
        $pagination = new Pagination($total, $perPage, $page);

        $pageTitle = 'Product Reviews';
        $this->render('admin/products/reviews', [
            'pageTitle' => $pageTitle,
            'reviews' => $reviews,
            'pagination' => $pagination,
            'search' => $search,
            'statusFilter' => $statusFilter
        ]);
    }

    /**
     * View Single Review Details
     */
    public function productsReviewsView($reviewId)
    {
        require_once APP_PATH . '/models/Review.php';
        require_once APP_PATH . '/models/Order.php';

        $reviewModel = new Review();
        $orderModel = new Order();

        // Get review details with all necessary joins
        // We can reuse the query logic but specific for one ID
        $sql = "SELECT r.*, p.name as product_name, p.sku, p.slug as product_slug,
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as product_image,
                u.first_name, u.last_name, u.email as customer_email, u.phone as customer_phone,
                u.profile_image as customer_image
                FROM reviews r
                JOIN products p ON r.product_id = p.product_id
                JOIN users u ON r.user_id = u.user_id
                WHERE r.review_id = ?";

        $review = $reviewModel->query($sql, [$reviewId])->fetch();

        if (!$review) {
            Session::setFlash('error', 'Review not found');
            header('Location: ' . SITE_URL . '/admin/products/reviews');
            exit;
        }

        // Get Order Data if available
        $order = null;
        if (!empty($review['order_id'])) {
            $order = $orderModel->find($review['order_id']);
        }

        $pageTitle = 'Review Details';
        $this->render('admin/products/reviews_view', [
            'pageTitle' => $pageTitle,
            'review' => $review,
            'order' => $order
        ]);
    }

    /**
     * Update review status (AJAX)
     * Route: /admin/products/reviews-update-status
     */
    public function productsReviewsUpdateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $reviewId = $_POST['review_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (!$reviewId || !$status) {
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        // Convert status to uppercase to match ENUM/Constants
        $status = strtoupper($status);
        $allowedStatuses = ['APPROVED', 'REJECTED', 'HIDDEN', 'PENDING'];
        if (!in_array($status, $allowedStatuses)) {
            echo json_encode(['error' => 'Invalid status']);
            return;
        }

        require_once APP_PATH . '/models/Review.php';
        $reviewModel = new Review();

        try {
            // Using update directly or a specific method if it exists
            // Review model has updateStatus method
            if (method_exists($reviewModel, 'updateStatus')) {
                $reviewModel->updateStatus($reviewId, $status, Session::getUserId());
            } else {
                $reviewModel->update($reviewId, ['status' => $status]);
            }

            echo json_encode(['success' => true, 'message' => "Review updated to $status"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete Review (AJAX)
     * Route: /admin/products/reviews-delete
     */
    public function productsReviewsDelete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $reviewId = $_POST['review_id'] ?? null;

        if (!$reviewId) {
            echo json_encode(['error' => 'Missing review ID']);
            return;
        }

        require_once APP_PATH . '/models/Review.php';
        $reviewModel = new Review();

        try {
            $reviewModel->delete($reviewId);
            echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Add new product
     * 
     * Common attributes are loaded unconditionally and independently of category selection.
     * They are global attributes that should appear for all products.
     */
    public function productAdd()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProductSave();
        } else {
            // var_dump(get_class($this->attributeModel));
            // var_dump(get_class_methods($this->attributeModel));
            // exit;
            // var_dump((new ReflectionClass(Attribute::class))->getFileName());
            // exit;

            // var_dump(get_class_methods($this->attributeModel));


            // STEP 1: ALWAYS load active common attributes FIRST, unconditionally
            // This happens BEFORE any category logic and is independent of category selection
            // Common attributes are global and must be available on initial page load
            $commonAttributes = $this->attributeModel->getCommonAttributes(true);

            // Ensure we have an array (never null)
            if (!is_array($commonAttributes)) {
                $commonAttributes = [];
            }

            // STEP 2: Load categories (for dropdown selection)
            // This is separate from attribute loading
            $categories = $this->categoryModel->getAll(true); // Include inactive for admin

            // STEP 3: Category attributes are empty initially
            // They load via AJAX when category is selected (client-side)
            // This separation ensures common attributes are always visible, even without category selection
            $categoryAttributes = [];

            // STEP 4: Render form with common attributes already loaded
            // Common attributes will be visible immediately, before any category is selected
            $this->render('admin/product_form', [
                'product' => null,
                'images' => [],
                'categories' => $categories,
                'commonAttributes' => $commonAttributes, // Always present, even if empty
                'categoryAttributes' => $categoryAttributes, // Empty until category selected
                'productAttributes' => [],
                'variants' => [],
                'action' => 'Add'
            ]);
        }
    }

    /**
     * Edit product
     */
    public function productEdit($id)
    {
        $product = $this->productModel->find($id);

        if (!$product) {
            Session::setFlash('error', 'Product not found');
            header('Location: ' . SITE_URL . '/admin/products');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProductSave($id);
        } else {
            $images = $this->productModel->getImages($id);
            $variants = $this->productModel->getVariants($id, false); // Get all variants for editing
            $categories = $this->categoryModel->getAll(true); // Include inactive for admin
            $productAttributes = $this->productModel->getAttributes($id);

            // ALWAYS load active common attributes first, unconditionally
            // Common attributes are global and must be available regardless of category
            // Loaded server-side, independent of category selection
            $commonAttributes = $this->attributeModel->getCommonAttributes(true);

            // Ensure we have an array (never null)
            if (!is_array($commonAttributes)) {
                $commonAttributes = [];
            }

            // Get category attributes for the product's category (with inheritance)
            // Use getByCategory which handles both group-based and legacy systems
            $categoryAttributes = [];
            if (!empty($product['category_id'])) {
                try {
                    // getByCategory now handles both group-based and legacy direct category_id methods
                    if (method_exists($this->attributeModel, 'getByCategory')) {
                        $categoryAttributes = $this->attributeModel->getByCategory($product['category_id'], true);
                    } else {
                        // Direct database query fallback (only active attributes)
                        require_once APP_PATH . '/config/database.php';
                        $db = Database::getInstance()->getConnection();
                        $sql = "SELECT * FROM category_attributes WHERE category_id = ? AND is_active = 1 ORDER BY display_order ASC, attribute_name ASC";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$product['category_id']]);
                        $categoryAttributes = $stmt->fetchAll();
                        // Decode JSON options for select type attributes
                        foreach ($categoryAttributes as &$attribute) {
                            if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                                $decoded = json_decode($attribute['attribute_options'], true);
                                $attribute['options'] = (is_array($decoded)) ? $decoded : [];
                            } else {
                                $attribute['options'] = [];
                            }
                        }
                        unset($attribute);
                    }
                } catch (Throwable $e) {
                    // If all methods fail, use direct database query (only active attributes)
                    error_log('Error loading category attributes in productEdit: ' . $e->getMessage());
                    try {
                        require_once APP_PATH . '/config/database.php';
                        $db = Database::getInstance()->getConnection();
                        $sql = "SELECT * FROM category_attributes WHERE category_id = ? AND is_active = 1 ORDER BY display_order ASC, attribute_name ASC";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$product['category_id']]);
                        $categoryAttributes = $stmt->fetchAll();

                        // Decode JSON options for select type attributes
                        foreach ($categoryAttributes as &$attribute) {
                            if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                                $decoded = json_decode($attribute['attribute_options'], true);
                                $attribute['options'] = (is_array($decoded)) ? $decoded : [];
                            } else {
                                $attribute['options'] = [];
                            }
                        }
                        unset($attribute);
                    } catch (Throwable $dbError) {
                        error_log('Database fallback also failed: ' . $dbError->getMessage());
                        $categoryAttributes = [];
                    }
                }

                // Remove common attributes from category attributes to avoid duplicates
                $commonAttributeIds = array_column($commonAttributes, 'attribute_id');
                $categoryAttributes = array_filter($categoryAttributes, function ($attr) use ($commonAttributeIds) {
                    return !in_array($attr['attribute_id'], $commonAttributeIds);
                });
                $categoryAttributes = array_values($categoryAttributes); // Re-index array
            }
            


            $this->render('admin/product_form', [
                'product' => $product,
                'images' => $images,
                'variants' => $variants,
                'categories' => $categories,
                'commonAttributes' => $commonAttributes,
                'categoryAttributes' => $categoryAttributes,
                'productAttributes' => $productAttributes,
                'action' => 'Edit'
            ]);
        }
    }

    /**
     * Delete product
     */
    public function productDelete($id)
    {
        // Validate ID
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            Session::setFlash('error', 'Invalid product ID');
            header('Location: ' . SITE_URL . '/admin/products');
            exit;
        }

        $id = (int) $id;
        $product = $this->productModel->find($id);

        if (!$product) {
            Session::setFlash('error', 'Product not found');
            header('Location: ' . SITE_URL . '/admin/products');
            exit;
        }

        // Check if product has order items (foreign key constraint prevents deletion)
        $orderItemCount = $this->productModel->query(
            "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?",
            [$id]
        )->fetch()['count'];

        if ($orderItemCount > 0) {
            Session::setFlash('error', 'Cannot delete product. It has ' . $orderItemCount . ' order item(s) associated with it. To preserve order history, products with orders cannot be deleted. Consider setting the product status to "inactive" instead.');
            header('Location: ' . SITE_URL . '/admin/products');
            exit;
        }

        // Delete product images first (they have CASCADE, but let's be explicit)
        $images = $this->productModel->getImages($id);
        if (!empty($images)) {
            $uploader = new ImageUpload();
            foreach ($images as $image) {
                $imagePath = $image['image_url'];
                $filePath = (strpos($imagePath, '/') === 0) ? substr($imagePath, 1) : $imagePath;
                $uploader->delete($filePath);
            }
        }

        // Attempt to delete the product
        try {
            if ($this->productModel->delete($id)) {
                Session::setFlash('success', 'Product deleted successfully');
            } else {
                Session::setFlash('error', 'Failed to delete product');
            }
        } catch (PDOException $e) {
            // Catch any remaining foreign key constraint errors
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                Session::setFlash('error', 'Cannot delete product. It is referenced by other records. Consider setting the product status to "inactive" instead.');
            } else {
                Session::setFlash('error', 'Failed to delete product: ' . $e->getMessage());
            }
        }

        header('Location: ' . SITE_URL . '/admin/products');
        exit;
    }

    /**
     * Recalculate Top Selling Products
     * Route: /admin/products/recalculate-top
     */
    public function productsRecalculateTopSelling()
    {
        // Only allow POST requests for this action
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
             // Allow GET for simple testing link, but ideally should be POST
        }

        require_once APP_PATH . '/services/TopSellingService.php';
        
        try {
            $service = new TopSellingService();
            $count = $service->recalculateAll();
            
            Session::setFlash('success', "Top Selling Products recalculated successfully. Processed {$count} products.");
        } catch (Exception $e) {
            Session::setFlash('error', "Failed to recalculate: " . $e->getMessage());
        }
        
        // Redirect back to dashboard or products page
        header('Location: ' . SITE_URL . '/admin/dashboard');
        exit;
    }

    /**
     * Handle product save (add/edit)
     */
    private function handleProductSave($productId = null)
    {
        // Debug Logging
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            error_log('HandleProductSave POST: ' . print_r($_POST, true));
            error_log('HandleProductSave FILES: ' . print_r($_FILES, true));
        }
        $data = [
            'name' => Validator::sanitize($_POST['name'] ?? ''),
            'slug' => $this->generateSlug($_POST['name'] ?? ''),
            'description' => Validator::sanitize($_POST['description'] ?? ''),
            'short_description' => Validator::sanitize($_POST['short_description'] ?? ''),
            'price' => (float) ($_POST['price'] ?? 0),
            'sale_price' => !empty($_POST['sale_price']) ? (float) $_POST['sale_price'] : null,
            'cost_price' => !empty($_POST['cost_price']) ? (float) $_POST['cost_price'] : null,
            'sku' => Validator::sanitize($_POST['sku'] ?? ''),
            'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
            // Shipping
            'weight' => !empty($_POST['weight']) ? (float) $_POST['weight'] : null,
            'dimensions' => Validator::sanitize($_POST['dimensions'] ?? ''),
            'shipping_class' => Validator::sanitize($_POST['shipping_class'] ?? ''),
            // Return & Warranty
            'return_policy' => Validator::sanitize($_POST['return_policy'] ?? '7_days'),
            'warranty_type' => Validator::sanitize($_POST['warranty_type'] ?? 'none'),
            'warranty_duration' => Validator::sanitize($_POST['warranty_duration'] ?? ''),
            'warranty_description' => Validator::sanitize($_POST['warranty_description'] ?? ''),
            // SEO
            'meta_title' => Validator::sanitize($_POST['meta_title'] ?? ''),
            'meta_description' => Validator::sanitize($_POST['meta_description'] ?? ''),
            'meta_keywords' => Validator::sanitize($_POST['meta_keywords'] ?? ''),
            // Tax
            'tax_status' => Validator::sanitize($_POST['tax_status'] ?? 'taxable'),
            'tax_class' => Validator::sanitize($_POST['tax_class'] ?? 'standard'),
            'gst_percent' => !empty($_POST['gst_percent']) ? (float) $_POST['gst_percent'] : 18.00,
            
            // Note: age_group, gender, brand, and material are now handled through the dynamic attribute system
            // They should be defined as attributes for the category/attribute group if needed
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_new_arrival' => isset($_POST['is_new_arrival']) ? 1 : 0,
            'is_bestseller' => isset($_POST['is_bestseller']) ? 1 : 0,
            'status' => Validator::sanitize($_POST['status'] ?? PRODUCT_STATUS_ACTIVE)
        ];

        // Handle category_id
        $categoryId = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        if ($categoryId) {
            $data['category_id'] = $categoryId;
        }

        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Product name is required';
        }

        // Validate category_id
        if (empty($categoryId)) {
            $errors[] = 'Category is required';
        } else {
            // Verify category exists
            $category = $this->categoryModel->getById($categoryId);
            if (!$category) {
                $errors[] = 'Selected category does not exist';
            }
        }

        // Validate Images (Safety Check)
        if (!$productId) {
            $hasImages = false;
            if (!empty($_FILES['new_images']['name'])) {
                foreach ($_FILES['new_images']['name'] as $idx => $files) {
                    if (!empty($files['main'])) {
                        $hasImages = true;
                        break;
                    }
                }
            }
            // Also check legacy images just in case
            if (!$hasImages && !empty($_FILES['images']['name'][0])) {
                $hasImages = true;
            }

            if (!$hasImages) {
                $errors[] = 'At least one product image is required.';
            }
        }

        if (empty($errors)) {
            if ($productId) {
                // Update existing product
                $data['updated_at'] = date('Y-m-d H:i:s');
                if ($this->productModel->update($productId, $data)) {

                    
                    // Handle image upload
                    $this->handleProductImages($productId);

                    // Handle product attributes
                    $this->handleProductAttributes($productId, $categoryId);

                    // Handle product variants
                    $this->handleProductVariants($productId);

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

                    // Handle product attributes
                    $this->handleProductAttributes($newProductId, $categoryId);

                    // Handle product variants
                    $this->handleProductVariants($newProductId);

                    Session::setFlash('success', 'Product added successfully');
                    header('Location: ' . SITE_URL . '/admin/products');
                    exit;
                } else {
                    $errors[] = 'Failed to create product';
                }
            }
        }

        // ALWAYS load active common attributes first, unconditionally
        // Common attributes are global and must be available regardless of category selection or validation errors
        // This ensures common attributes are visible even when form is re-rendered with validation errors
        $commonAttributes = $this->attributeModel->getCommonAttributes(true);

        // Ensure we have an array (never null)
        if (!is_array($commonAttributes)) {
            $commonAttributes = [];
        }

        // Load categories (for dropdown selection)
        $categories = $this->categoryModel->getAll(true); // Include inactive for admin

        // Get category attributes if category is selected (with inheritance support)
        // Only active attributes are loaded to match the Add Product form behavior
        // Use getByCategory which handles both group-based and legacy systems
        $categoryAttributes = [];
        if (!empty($categoryId)) {
            try {
                // getByCategory now handles both group-based and legacy direct category_id methods
                if (method_exists($this->attributeModel, 'getByCategory')) {
                    $categoryAttributes = $this->attributeModel->getByCategory($categoryId, true);
                }
            } catch (Throwable $e) {
                error_log('Error loading category attributes in handleProductSave: ' . $e->getMessage());
                $categoryAttributes = [];
            }

            // Remove common attributes from category attributes to avoid duplicates
            $commonAttributeIds = array_column($commonAttributes, 'attribute_id');
            $categoryAttributes = array_filter($categoryAttributes, function ($attr) use ($commonAttributeIds) {
                return !in_array($attr['attribute_id'], $commonAttributeIds);
            });
            $categoryAttributes = array_values($categoryAttributes); // Re-index array
        }

        $this->render('admin/product_form', [
            'product' => $productId ? $this->productModel->find($productId) : null,
            'images' => $productId ? $this->productModel->getImages($productId) : [],
            'variants' => $productId ? $this->productModel->getVariants($productId, false) : [],
            'errors' => $errors,
            'data' => $data,
            'categories' => $categories,
            'commonAttributes' => $commonAttributes,
            'categoryAttributes' => $categoryAttributes,
            'productAttributes' => $productId ? $this->productModel->getAttributes($productId) : [],
            'action' => $productId ? 'Edit' : 'Add'
        ]);
    }

    /**
     * Handle product attributes save
     * Uses the new attribute group system with inheritance support
     * Also handles Common attributes (always available regardless of category)
     */
    private function handleProductAttributes($productId, $categoryId)
    {
        // ALWAYS load active common attributes first, unconditionally
        // Common attributes are global and must be processed regardless of category
        // This ensures common attributes are saved even if no category is selected
        $commonAttributes = $this->attributeModel->getCommonAttributes(true);

        // Ensure we have an array (never null)
        if (!is_array($commonAttributes)) {
            $commonAttributes = [];
        }

        // Get all attributes for this category (with inheritance from parent categories)
        // Use getByCategory which handles both group-based and legacy systems
        // Only active attributes are processed to match the Add Product form behavior
        $categoryAttributes = [];
        if (!empty($categoryId)) {
            try {
                // getByCategory now handles both group-based and legacy direct category_id methods
                if (method_exists($this->attributeModel, 'getByCategory')) {
                    $categoryAttributes = $this->attributeModel->getByCategory($categoryId, true);
                }
            } catch (Throwable $e) {
                error_log('Error loading category attributes in handleProductAttributes: ' . $e->getMessage());
                // Continue with empty array - attributes won't be saved but product will still be saved
            }
        }

        // Remove common attributes from category attributes to avoid duplicates
        $commonAttributeIds = array_column($commonAttributes, 'attribute_id');
        $categoryAttributes = array_filter($categoryAttributes, function ($attr) use ($commonAttributeIds) {
            return !in_array($attr['attribute_id'], $commonAttributeIds);
        });
        $categoryAttributes = array_values($categoryAttributes); // Re-index array

        // Combine all attributes (common + category-specific)
        $allAttributes = array_merge($commonAttributes, $categoryAttributes);

        // Save each attribute value
        foreach ($allAttributes as $attribute) {
            $attributeKey = 'attribute_' . $attribute['attribute_id'];

            if (isset($_POST[$attributeKey])) {
                $value = Validator::sanitize($_POST[$attributeKey]);

                // Skip empty values unless required
                if (empty($value) && !$attribute['is_required']) {
                    continue;
                }

                // For select type, validate against options
                if ($attribute['attribute_type'] === 'select' && !empty($attribute['options'])) {
                    if (!in_array($value, $attribute['options'])) {
                        continue; // Skip invalid option
                    }
                }

                // Save the attribute value
                $this->attributeModel->saveProductAttribute($productId, $attribute['attribute_id'], $value);
            } elseif ($attribute['is_required']) {
                // Required attribute not provided - could log or handle error
                // For now, we'll skip it (validation should happen on frontend)
            }
        }
    }

    /**
     * Handle product variants save
     */
    /**
     * Handle product variants save
     */
    private function handleProductVariants($productId)
    {
        // Get variant data from POST
        $variants = [];

        if (isset($_POST['variants']) && is_array($_POST['variants'])) {
            $variants = $_POST['variants'];
        }

        // Get existing variant IDs to track which ones to keep
        $existingVariants = $this->productModel->getVariants($productId, false);
        $existingVariantIds = array_column($existingVariants, 'variant_id');
        $processedVariantIds = [];

        $uploader = new ImageUpload();
        $uploadDir = 'products/variants';

        // Save or update variants
        foreach ($variants as $index => $variantData) {
            if (empty($variantData['size'])) {
                continue; // Skip variants without size
            }

            // Handle Variant Image Upload
            $imageUrl = $variantData['existing_image_url'] ?? null;
            
            // Check if a new file was selected for this variant index
            // PHP structure for array inputs: $_FILES['variants']['name'][$index]['image_file']
            if (isset($_FILES['variants']['name'][$index]['image_file']) && !empty($_FILES['variants']['name'][$index]['image_file'])) {
                $file = [
                    'name' => $_FILES['variants']['name'][$index]['image_file'],
                    'type' => $_FILES['variants']['type'][$index]['image_file'],
                    'tmp_name' => $_FILES['variants']['tmp_name'][$index]['image_file'],
                    'error' => $_FILES['variants']['error'][$index]['image_file'],
                    'size' => $_FILES['variants']['size'][$index]['image_file']
                ];

                $result = $uploader->upload($file, $uploadDir, 'variant_' . $productId . '_');
                if ($result['success']) {
                    $imageUrl = $result['relative_path'];
                    if (strpos($imageUrl, '/') !== 0) {
                        $imageUrl = '/' . $imageUrl;
                    }
                } else {
                    // Start: error logging
                    error_log("Variant image upload failed for product {$productId}, index {$index}: " . ($result['error'] ?? 'Unknown error'));
                    // We continue saving the variant, just without the new image
                }
            } elseif (!empty($variantData['image_url_text'])) {
                 // User provided a manual URL (e.g. external link)
                 $imageUrl = Validator::sanitize($variantData['image_url_text']);
            }

            // flexible attributes system
            $attributes = [];
            if (!empty($variantData['size'])) {
                $attributes['Size'] = Validator::sanitize($variantData['size']);
            }
            if (!empty($variantData['color'])) {
                $attributes['Color'] = Validator::sanitize($variantData['color']);
            }
            if (!empty($variantData['color_code'])) {
                $attributes['Color Code'] = Validator::sanitize($variantData['color_code']);
            }
            // Allow for other dynamic attributes if passed in the future
            if (!empty($variantData['attributes']) && is_array($variantData['attributes'])) {
                foreach ($variantData['attributes'] as $k => $v) {
                    $attributes[$k] = Validator::sanitize($v);
                }
            }

            $variantInfo = [
                'attributes' => $attributes,
                'price' => !empty($variantData['price']) ? (float) $variantData['price'] : 0.00,
                'sale_price' => !empty($variantData['sale_price']) ? (float) $variantData['sale_price'] : 0.00,
                'additional_price' => !empty($variantData['additional_price']) ? (float) $variantData['additional_price'] : 0.00,
                'stock_quantity' => !empty($variantData['stock_quantity']) ? (int) $variantData['stock_quantity'] : 0,
                'sku' => !empty($variantData['sku']) ? Validator::sanitize($variantData['sku']) : null,
                'image_url' => $imageUrl,
                'is_active' => isset($variantData['is_active']) ? (int) $variantData['is_active'] : 1
            ];

            // If variant_id is provided, update existing variant
            if (!empty($variantData['variant_id']) && is_numeric($variantData['variant_id'])) {
                $variantInfo['variant_id'] = (int) $variantData['variant_id'];
                $processedVariantIds[] = $variantInfo['variant_id'];
            }

            $this->productModel->saveVariant($productId, $variantInfo);
        }

        // Delete variants that were removed
        $variantsToDelete = array_diff($existingVariantIds, $processedVariantIds);
        foreach ($variantsToDelete as $variantId) {
            // Also optional: Delete the variant image file if it exists and is local
            $this->productModel->deleteVariant($variantId);
        }
    }

    /**
     * Get category attributes via AJAX (with inheritance support)
     * Uses the new attribute group system with category inheritance
     */
    public function getCategoryAttributes()
    {
        // Set JSON header first
        header('Content-Type: application/json; charset=utf-8');

        // Start output buffering to catch any unwanted output
        ob_start();

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Invalid request method');
            }

            $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

            // Get common attributes (always available, regardless of category)
            // Common attributes are loaded unconditionally - they are global and category-independent
            $commonAttributes = [];
            try {
                $commonAttributes = $this->attributeModel->getCommonAttributes(true); // Only active common attributes

                // Ensure we have an array (never null)
                if (!is_array($commonAttributes)) {
                    $commonAttributes = [];
                }
            } catch (Throwable $e) {
                error_log('Error loading common attributes: ' . $e->getMessage());
                $commonAttributes = [];
            }

            // Get category-specific attributes (only if category is selected)
            // Use getByCategory which handles both group-based and legacy systems
            $categoryAttributes = [];
            if ($categoryId) {
                try {
                    // getByCategory now handles both group-based and legacy direct category_id methods
                    if (method_exists($this->attributeModel, 'getByCategory')) {
                        $categoryAttributes = $this->attributeModel->getByCategory($categoryId, true);
                    } else {
                        // Direct database query fallback (only active attributes)
                        require_once APP_PATH . '/config/database.php';
                        $db = Database::getInstance()->getConnection();
                        $sql = "SELECT * FROM category_attributes WHERE category_id = ? AND is_active = 1 ORDER BY display_order ASC, attribute_name ASC";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$categoryId]);
                        $categoryAttributes = $stmt->fetchAll();
                        // Decode JSON options for select type attributes
                        foreach ($categoryAttributes as &$attribute) {
                            if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                                $decoded = json_decode($attribute['attribute_options'], true);
                                $attribute['options'] = (is_array($decoded)) ? $decoded : [];
                            } else {
                                $attribute['options'] = [];
                            }
                            // Decode show_when condition if exists
                            if (!empty($attribute['show_when'])) {
                                $decoded = json_decode($attribute['show_when'], true);
                                $attribute['show_when_decoded'] = (is_array($decoded)) ? $decoded : null;
                            } else {
                                $attribute['show_when_decoded'] = null;
                            }
                        }
                        unset($attribute);
                    }
                } catch (Throwable $e) {
                    // If all methods fail, use direct database query (only active attributes)
                    error_log('Error loading category attributes in getCategoryAttributes: ' . $e->getMessage());
                    error_log('Stack trace: ' . $e->getTraceAsString());
                    try {
                        require_once APP_PATH . '/config/database.php';
                        $db = Database::getInstance()->getConnection();
                        $sql = "SELECT * FROM category_attributes WHERE category_id = ? AND is_active = 1 ORDER BY display_order ASC, attribute_name ASC";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([$categoryId]);
                        $categoryAttributes = $stmt->fetchAll();

                        // Decode JSON options for select type attributes
                        foreach ($categoryAttributes as &$attribute) {
                            if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                                $decoded = json_decode($attribute['attribute_options'], true);
                                $attribute['options'] = (is_array($decoded)) ? $decoded : [];
                            } else {
                                $attribute['options'] = [];
                            }
                            // Decode show_when condition if exists
                            if (!empty($attribute['show_when'])) {
                                $decoded = json_decode($attribute['show_when'], true);
                                $attribute['show_when_decoded'] = (is_array($decoded)) ? $decoded : null;
                            } else {
                                $attribute['show_when_decoded'] = null;
                            }
                        }
                        unset($attribute);
                    } catch (Throwable $dbError) {
                        error_log('Database fallback also failed: ' . $dbError->getMessage());
                        $categoryAttributes = [];
                    }
                }
            }

            // Ensure attributes are arrays
            if (!is_array($commonAttributes)) {
                $commonAttributes = [];
            }
            if (!is_array($categoryAttributes)) {
                $categoryAttributes = [];
            }

            // Remove common attributes from category attributes to avoid duplicates
            $commonAttributeIds = array_column($commonAttributes, 'attribute_id');
            $categoryAttributes = array_filter($categoryAttributes, function ($attr) use ($commonAttributeIds) {
                return !in_array($attr['attribute_id'], $commonAttributeIds);
            });
            $categoryAttributes = array_values($categoryAttributes); // Re-index array

            // Combine all attributes for backward compatibility
            $allAttributes = array_merge($commonAttributes, $categoryAttributes);

            // Separate base attributes (no dependencies) from dependent attributes for ALL attributes
            $baseAttributes = [];
            $dependentAttributes = [];
            $attributeMap = []; // For quick lookup

            foreach ($allAttributes as $attribute) {
                $attributeMap[$attribute['attribute_id']] = $attribute;

                if (empty($attribute['depends_on'])) {
                    $baseAttributes[] = $attribute;
                } else {
                    $dependentAttributes[] = $attribute;
                }
            }

            // Separate base and dependent attributes for CATEGORY-SPECIFIC attributes only
            $categoryBaseAttributes = [];
            $categoryDependentAttributes = [];
            $categoryAttributeMap = [];

            foreach ($categoryAttributes as $attribute) {
                $categoryAttributeMap[$attribute['attribute_id']] = $attribute;

                if (empty($attribute['depends_on'])) {
                    $categoryBaseAttributes[] = $attribute;
                } else {
                    $categoryDependentAttributes[] = $attribute;
                }
            }

            // Group attributes by attribute group for better organization
            $groupedAttributes = [];
            foreach ($allAttributes as $attribute) {
                $groupName = $attribute['group_name'] ?? 'Other';
                if (!isset($groupedAttributes[$groupName])) {
                    $groupedAttributes[$groupName] = [];
                }
                $groupedAttributes[$groupName][] = $attribute;
            }

            $response = [
                'success' => true,
                'commonAttributes' => $commonAttributes,
                'categoryAttributes' => $categoryAttributes,
                'attributes' => $allAttributes, // For backward compatibility
                'groupedAttributes' => $groupedAttributes,
                'baseAttributes' => $baseAttributes, // All attributes without dependencies (common + category)
                'dependentAttributes' => $dependentAttributes, // All attributes with dependencies (common + category)
                'categoryBaseAttributes' => $categoryBaseAttributes, // Category-specific attributes without dependencies
                'categoryDependentAttributes' => $categoryDependentAttributes, // Category-specific attributes with dependencies
                'attributeMap' => $attributeMap, // Quick lookup map for all attributes
                'categoryAttributeMap' => $categoryAttributeMap // Quick lookup map for category attributes only
            ];
        } catch (Exception $e) {
            error_log('getCategoryAttributes error: ' . $e->getMessage());
            error_log('getCategoryAttributes stack trace: ' . $e->getTraceAsString());
            $response = [
                'success' => false,
                'message' => 'Failed to load attributes: ' . $e->getMessage()
            ];
        } catch (Error $e) {
            error_log('getCategoryAttributes fatal error: ' . $e->getMessage());
            error_log('getCategoryAttributes fatal error file: ' . $e->getFile() . ' line: ' . $e->getLine());
            error_log('getCategoryAttributes fatal error stack trace: ' . $e->getTraceAsString());
            $response = [
                'success' => false,
                'message' => 'An error occurred while loading attributes: ' . $e->getMessage()
            ];
        } catch (Throwable $e) {
            error_log('getCategoryAttributes throwable error: ' . $e->getMessage());
            error_log('getCategoryAttributes throwable error file: ' . $e->getFile() . ' line: ' . $e->getLine());
            error_log('getCategoryAttributes throwable error stack: ' . $e->getTraceAsString());
            $response = [
                'success' => false,
                'message' => 'An error occurred while loading attributes: ' . $e->getMessage()
            ];
        }

        // Clear any output that might have been generated
        ob_clean();

        // Output JSON response
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // End output buffering
        ob_end_flush();

        exit;
    }

    /**
     * Handle product image uploads
     */
    private function handleProductImages($productId)
    {
        $uploader = new ImageUpload();
        $uploadDir = 'products';
        $uploadErrors = [];

        // 1. Handle Existing Images Zoom Updates
        if (isset($_FILES['existing_zoom_images'])) {
            foreach ($_FILES['existing_zoom_images']['name'] as $imageId => $fileName) {
                if (!empty($fileName)) {
                    // Construct single file array for uploader
                    $file = [
                        'name' => $_FILES['existing_zoom_images']['name'][$imageId],
                        'type' => $_FILES['existing_zoom_images']['type'][$imageId],
                        'tmp_name' => $_FILES['existing_zoom_images']['tmp_name'][$imageId],
                        'error' => $_FILES['existing_zoom_images']['error'][$imageId],
                        'size' => $_FILES['existing_zoom_images']['size'][$imageId],
                    ];
                    $result = $uploader->upload($file, $uploadDir, 'product_zoom_');
                    if ($result['success']) {
                        $zoomUrl = $result['relative_path'];
                        if (strpos($zoomUrl, '/') !== 0) {
                            $zoomUrl = '/' . $zoomUrl;
                        }

                        $this->productModel->query(
                            "UPDATE product_images SET zoom_image_url = ? WHERE image_id = ? AND product_id = ?",
                            [$zoomUrl, $imageId, $productId]
                        );
                    } else {
                         // Log error for zoom update
                         error_log("Zoom image update failed for product {$productId}, image {$imageId}: " . ($result['error'] ?? 'Unknown error'));
                         $uploadErrors[] = "Failed to update zoom image: " . ($result['error'] ?? 'Unknown error');
                    }
                }
            }
        }

        // 2. Handle New Images (Rows)
        if (isset($_FILES['new_images'])) {
            // Check if global error exists (e.g. post_max_size exceeded)
            if (isset($_FILES['new_images']['error']) && !is_array($_FILES['new_images']['error'])) {
                 error_log("Global upload error for product {$productId}: " . $_FILES['new_images']['error']);
                 Session::setFlash('error', 'Image upload failed. File too large or server error.');
                 return;
            }

            foreach ($_FILES['new_images']['name'] as $index => $files) {
                // Check if main image exists
                if (!empty($files['main'])) {
                    // Upload Main
                    $mainFile = [
                        'name' => $_FILES['new_images']['name'][$index]['main'],
                        'type' => $_FILES['new_images']['type'][$index]['main'],
                        'tmp_name' => $_FILES['new_images']['tmp_name'][$index]['main'],
                        'error' => $_FILES['new_images']['error'][$index]['main'],
                        'size' => $_FILES['new_images']['size'][$index]['main'],
                    ];

                    $mainResult = $uploader->upload($mainFile, $uploadDir, 'product_');

                    if ($mainResult['success']) {
                        $mainUrl = $mainResult['relative_path'];
                        if (strpos($mainUrl, '/') !== 0) {
                            $mainUrl = '/' . $mainUrl;
                        }

                        // Upload Zoom (Optional)
                        $zoomUrl = null;
                        if (!empty($_FILES['new_images']['name'][$index]['zoom'])) {
                            $zoomFile = [
                                'name' => $_FILES['new_images']['name'][$index]['zoom'],
                                'type' => $_FILES['new_images']['type'][$index]['zoom'],
                                'tmp_name' => $_FILES['new_images']['tmp_name'][$index]['zoom'],
                                'error' => $_FILES['new_images']['error'][$index]['zoom'],
                                'size' => $_FILES['new_images']['size'][$index]['zoom'],
                            ];
                            $zoomResult = $uploader->upload($zoomFile, $uploadDir, 'product_zoom_');
                            if ($zoomResult['success']) {
                                $zoomUrl = $zoomResult['relative_path'];
                                if (strpos($zoomUrl, '/') !== 0) {
                                    $zoomUrl = '/' . $zoomUrl;
                                }
                            } else {
                                $uploadErrors[] = "Zoom image failed for image set #".($index+1).": " . ($zoomResult['error'] ?? 'Unknown error');
                            }
                        }

                        // Determine Order & Primary
                        $hasPrimary = $this->productModel->query(
                            "SELECT COUNT(*) as count FROM product_images WHERE product_id = ? AND is_primary = 1",
                            [$productId]
                        )->fetch()['count'] > 0;

                        $maxOrder = $this->productModel->query(
                            "SELECT COALESCE(MAX(display_order), -1) as max_order FROM product_images WHERE product_id = ?",
                            [$productId]
                        )->fetch()['max_order'];

                        $isPrimary = (!$hasPrimary) ? 1 : 0; // First one becomes primary if none exist
                        $displayOrder = $maxOrder + 1;

                        $this->productModel->query(
                            "INSERT INTO product_images (product_id, image_url, zoom_image_url, is_primary, display_order, created_at) VALUES (?, ?, ?, ?, ?, ?)",
                            [$productId, $mainUrl, $zoomUrl, $isPrimary, $displayOrder, date('Y-m-d H:i:s')]
                        );
                    } else {
                        error_log("Main image upload failed for product {$productId}, index {$index}: " . ($mainResult['error'] ?? 'Unknown error'));
                }
            }
        }
    }

        // 3. Handle Image Reordering (New Feature)
        // 3. Handle Image Reordering (New Feature)
        if (isset($_POST['image_order']) && !empty($_POST['image_order'])) {
            $orderString = $_POST['image_order'];
            error_log("Image Order received for Product {$productId}: " . $orderString); // Debug Log

            $orderedIds = explode(',', $orderString);
            
            // Validate IDs are numeric
            $orderedIds = array_filter($orderedIds, 'is_numeric');
            
            if (!empty($orderedIds)) {
                // strict primary reset: ensure all are 0 first
                $this->productModel->query("UPDATE product_images SET is_primary = 0 WHERE product_id = ?", [$productId]);

                $displayOrder = 0;
                foreach ($orderedIds as $imageId) {
                    // First image (index 0) becomes primary
                    $isPrimary = ($displayOrder === 0) ? 1 : 0;
                    
                    $this->productModel->query(
                        "UPDATE product_images SET display_order = ?, is_primary = ? WHERE image_id = ? AND product_id = ?",
                        [$displayOrder, $isPrimary, $imageId, $productId]
                    );
                    
                    $displayOrder++;
                }
            }
        }
        
        // If there were upload errors, flash them to the user
        if (!empty($uploadErrors)) {
            $existingFlash = Session::getFlash('warning') ?? '';
            $errorMsg = "Some images could not be uploaded: <br>" . implode('<br>', $uploadErrors);
            Session::setFlash('warning', $existingFlash . $errorMsg);
        }

        // Backward compatibility: If old 'images[]' is used (though we will hide it)
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            // ... legacy code if needed, but we can assume we only use the new one.
        }
    }

    /**
     * Reorder product images (AJAX)
     * Route: /admin/products/reorder-images
     */
    public function productsReorderImages()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $orderString = isset($_POST['image_order']) ? $_POST['image_order'] : '';

        if (!$productId || empty($orderString)) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $orderedIds = explode(',', $orderString);
        $orderedIds = array_filter($orderedIds, 'is_numeric');

        if (empty($orderedIds)) {
            echo json_encode(['success' => false, 'message' => 'Invalid image order data']);
            exit;
        }

        try {
            // Strict primary reset: ensure all are 0 first
            $this->productModel->query("UPDATE product_images SET is_primary = 0 WHERE product_id = ?", [$productId]);

            $displayOrder = 0;
            foreach ($orderedIds as $imageId) {
                // First image (index 0) becomes primary
                $isPrimary = ($displayOrder === 0) ? 1 : 0;
                
                $this->productModel->query(
                    "UPDATE product_images SET display_order = ?, is_primary = ? WHERE image_id = ? AND product_id = ?",
                    [$displayOrder, $isPrimary, $imageId, $productId]
                );
                
                $displayOrder++;
            }

            echo json_encode(['success' => true, 'message' => 'Image order updated successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete product image
     */
    public function productImageDelete()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $imageId = (int) ($_POST['image_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);

        if (!$imageId || !$productId) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        // Get image info before deletion
        $imageStmt = $this->productModel->query(
            "SELECT image_url, is_primary FROM product_images WHERE image_id = ? AND product_id = ?",
            [$imageId, $productId]
        );
        $image = $imageStmt->fetch();

        if (!$image) {
            echo json_encode(['success' => false, 'message' => 'Image not found']);
            exit;
        }

        // Delete image file
        $uploader = new ImageUpload();
        $imagePath = $image['image_url'];
        // Image_url is stored as /assets/uploads/... (with leading slash)
        // ImageUpload::delete() prepends PUBLIC_PATH, so we need to remove leading slash
        // to avoid double path issues
        $filePath = (strpos($imagePath, '/') === 0) ? substr($imagePath, 1) : $imagePath;
        $uploader->delete($filePath);

        // Also delete zoom image if exists
        // Check if there is a zoom image column first (to avoid errors if column doesn't match for some reason, though it should)
        if (isset($image['zoom_image_url']) && !empty($image['zoom_image_url'])) {
            $zoomPath = $image['zoom_image_url'];
            $zoomFilePath = (strpos($zoomPath, '/') === 0) ? substr($zoomPath, 1) : $zoomPath;
            $uploader->delete($zoomFilePath);
        } else {
            // Fallback: Check DB again specifically for zoom_image_url if it wasn't in original fetch
            $zoomQuery = $this->productModel->query("SELECT zoom_image_url FROM product_images WHERE image_id = ?", [$imageId]);
            $zoomResult = $zoomQuery->fetch();
            if ($zoomResult && !empty($zoomResult['zoom_image_url'])) {
                $zoomPath = $zoomResult['zoom_image_url'];
                $zoomFilePath = (strpos($zoomPath, '/') === 0) ? substr($zoomPath, 1) : $zoomPath;
                $uploader->delete($zoomFilePath);
            }
        }

        // Delete from database
        $deleteStmt = $this->productModel->query(
            "DELETE FROM product_images WHERE image_id = ? AND product_id = ?",
            [$imageId, $productId]
        );

        // If deleted image was primary, set the first remaining image as primary
        if ($image['is_primary']) {
            $firstImageStmt = $this->productModel->query(
                "SELECT image_id FROM product_images WHERE product_id = ? ORDER BY display_order LIMIT 1",
                [$productId]
            );
            $firstImage = $firstImageStmt->fetch();

            if ($firstImage) {
                $this->productModel->query(
                    "UPDATE product_images SET is_primary = 1 WHERE image_id = ?",
                    [$firstImage['image_id']]
                );
            }
        }

        echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        exit;
    }

    /**
     * Generate URL slug from name
     */
    private function generateSlug($text)
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');
        return $text . '-' . time();
    }

    /**
     * Require admin authentication
     */
    private function requireAdmin()
    {
        if (!Session::isAdmin()) {
            // Check if this is an AJAX/API request
            // Check X-Requested-With header
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
            
            // Check Accept header
            if (!$isAjax && !empty($_SERVER['HTTP_ACCEPT'])) {
                $accept = strtolower($_SERVER['HTTP_ACCEPT']);
                if (strpos($accept, 'application/json') !== false) {
                    $isAjax = true;
                }
            }

            if ($isAjax) {
                // Clear any existing headers to prevent interference
                header_remove();
                http_response_code(403);
                header('HTTP/1.1 403 Forbidden');
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Access denied. Admin privileges required.',
                    'redirect' => SITE_URL . '/user/login'
                ]);
                exit;
            }

            Session::setFlash('error', 'Access denied. Admin privileges required.');
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }
    }

    /**
     * Validate data dependency - ensures parent data exists before allowing child page access
     * 
     * This method implements the "Data Dependency Rule" from admin navigation standards:
     * - Child page cannot exist without parent data
     * - If parent data is invalid or deleted, redirect to parent listing
     * 
     * @param mixed $data The data record (e.g., category, product, attribute)
     * @param string $parentUrl The URL to redirect to if data is invalid (e.g., '/admin/categories')
     * @param string $entityName The name of the entity for error messages (e.g., 'Category', 'Product')
     * @return bool Returns true if data is valid, false otherwise (and redirects)
     */
    private function validateDataDependency($data, $parentUrl, $entityName = 'Item')
    {
        if (!$data || empty($data)) {
            Session::setFlash('error', $entityName . ' not found. Redirecting to ' . strtolower($entityName) . ' list.');
            header('Location: ' . SITE_URL . $parentUrl);
            exit;
        }
        return true;
    }

    /**
     * Category Management - List all categories
     * Handles routing: /admin/categories
     */
    public function categories(...$params)
    {

        // If there are params, it might be a sub-action (add, edit, delete, etc.)
        // Route to the appropriate method
        if (!empty($params[0])) {
            $subAction = $params[0];
            $remainingParams = array_slice($params, 1);

            // Map sub-actions to methods
            $actionMap = [
                'add' => 'categoryAdd',
                'edit' => 'categoryEdit',
                'delete' => 'categoryDelete',
                'activate' => 'categoryActivate',
                'deactivate' => 'categoryDeactivate'
            ];

            if (isset($actionMap[$subAction])) {
                $method = $actionMap[$subAction];
                if (method_exists($this, $method)) {
                    return call_user_func_array([$this, $method], $remainingParams);
                }
            }
        }

        // Default: list all categories (both active and inactive for admin)
        // Always fetch fresh data directly from database - no caching
        // IMPORTANT: Do NOT filter by is_active status unless explicitly requested
        $search = Validator::sanitize($_GET['search'] ?? '');
        $statusFilter = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : '';

        // Use the admin-specific method that includes ALL categories by default (both active and inactive)
        // Only add status filter if explicitly provided - otherwise show ALL categories
        $filters = [];
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        // Only add status filter if explicitly provided - this ensures ALL categories are shown by default
        if (!empty($statusFilter) && ($statusFilter === 'active' || $statusFilter === 'inactive')) {
            $filters['status'] = $statusFilter;
        }

        // Order by created_at DESC by default (newest first)
        $filters['orderBy'] = 'created_at DESC';

        // Fetch categories directly from database
        try {
            $categories = $this->categoryModel->getAllForAdmin($filters);

            // echo '<pre>';
            // print_r($categories);
            // exit;

            // If no categories returned but we expect data (no filters), try fallback method
            if (!empty($categories) && !empty($search) && !empty($statusFilter)) {

                $totalCount = $this->categoryModel->count();
                echo $totalCount;
                exit;
                if ($totalCount > 0) {
                    // There are categories in DB but getAllForAdmin returned empty - use fallback
                    error_log("AdminController::categories() Warning: getAllForAdmin returned empty but count() shows {$totalCount} categories. Using fallback.");
                    $categories = $this->categoryModel->getAllDirect();

                    print_r($categories);

                    // Add child_count and product_count manually
                    foreach ($categories as $category) {
                        $category['child_count'] = $this->categoryModel->query(
                            "SELECT COUNT(*) as count FROM categories WHERE parent_id = ?",
                            [$category['category_id']]
                        )->fetch()['count'] ?? 0;

                        $category['product_count'] = $this->categoryModel->query(
                            "SELECT COUNT(*) as count FROM products WHERE category_id = ?",
                            [$category['category_id']]
                        )->fetch()['count'] ?? 0;
                    }
                    unset($category); // Unset reference
                }
            } else {
                // print_r($categories);exit;
                // $categories = $stmt->fetchAll();
            }

            $error = null;
        } catch (Exception $e) {
            // Handle database errors
            error_log("AdminController::categories() Error: " . $e->getMessage());
            error_log("AdminController::categories() Stack trace: " . $e->getTraceAsString());
            $categories = [];
            $error = "Failed to load categories. Please try again later.";
            Session::setFlash('error', $error);
        }

        $this->render('admin/categories/index', [
            'categories' => $categories,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'error' => $error ?? null
        ]);

    }

    /**
     * Add new category (clean implementation, independent of edit flow)
     * URL: /admin/categories/add
     */
    public function categoryAdd()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->createCategory();
            return;
        }

        // GET: render empty form for creating a category
        $parentCategories = $this->categoryModel->getParentCategories(true);

        $this->render('admin/categories/form', [
            'category' => null,
            'parentCategories' => $parentCategories,
            'action' => 'Add',
            'mode' => 'add',
            'errors' => [],
            'data' => []
        ]);
    }

    /**
     * Edit category (rebuilt from scratch)
     * URL: /admin/categories/edit/{id}
     * The category ID from the URL is the single source of truth.
     */
    public function categoryEdit($id)
    {
        // The ID from the URL is the only identifier we trust
        if (!is_numeric($id)) {
            Session::setFlash('error', 'Invalid category ID');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        $categoryId = (int) $id;
        $category = $this->categoryModel->getById($categoryId);

        // print_r($category);
        // exit;

        if (!$category) {
            Session::setFlash('error', 'Category not found');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateCategory($categoryId, $category);
            return;
        }


        // GET: render form populated strictly from the DB row for this ID
        $parentCategories = $this->categoryModel->getParentCategories(true);
        // Exclude this category and all its children from parent options
        $childCategories = $this->categoryModel->getChildCategories($categoryId, true);
        $excludeIds = [$categoryId];
        foreach ($childCategories as $child) {
            $excludeIds[] = $child['category_id'];
        }
        $parentCategories = array_filter($parentCategories, function ($cat) use ($excludeIds) {
            return !in_array($cat['category_id'], $excludeIds);
        });

        $this->render('admin/categories/form', [
            'category' => $category,
            'parentCategories' => $parentCategories,
            'action' => 'Edit',
            'mode' => 'edit',
            'errors' => [],
            'data' => []
        ]);
    }

    /**
     * Delete category
     */
    public function categoryDelete($id)
    {
        // Validate ID
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            Session::setFlash('error', 'Invalid category ID');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        $id = (int) $id;
        $category = $this->categoryModel->find($id);

        if (!$category) {
            Session::setFlash('error', 'Category not found');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        // Check if category has products
        $productCount = $this->categoryModel->query(
            "SELECT COUNT(*) as count FROM products WHERE category_id = ?",
            [$id]
        )->fetch()['count'];

        if ($productCount > 0) {
            Session::setFlash('error', 'Cannot delete category. It has ' . $productCount . ' product(s) associated with it.');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        // Check if category has child categories
        $childCount = $this->categoryModel->query(
            "SELECT COUNT(*) as count FROM categories WHERE parent_id = ?",
            [$id]
        )->fetch()['count'];

        if ($childCount > 0) {
            Session::setFlash('error', 'Cannot delete category. It has ' . $childCount . ' sub-category(ies). Please delete or move them first.');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        // Delete category image if exists
        if (!empty($category['image'])) {
            $uploader = new ImageUpload();
            $imagePath = $category['image'];
            $filePath = (strpos($imagePath, '/') === 0) ? substr($imagePath, 1) : $imagePath;
            $uploader->delete($filePath);
        }

        if ($this->categoryModel->delete($id)) {
            Session::setFlash('success', 'Category deleted successfully');
        } else {
            Session::setFlash('error', 'Failed to delete category');
        }

        header('Location: ' . SITE_URL . '/admin/categories');
        exit;
    }

    /**
     * Activate category
     */
    public function categoryActivate($id)
    {
        // Validate ID
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            Session::setFlash('error', 'Invalid category ID');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        $id = (int) $id;
        $category = $this->categoryModel->find($id);

        if (!$category) {
            Session::setFlash('error', 'Category not found');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        if ($this->categoryModel->activate($id)) {
            Session::setFlash('success', 'Category activated successfully');
        } else {
            Session::setFlash('error', 'Failed to activate category');
        }

        header('Location: ' . SITE_URL . '/admin/categories');
        exit;
    }

    /**
     * Deactivate category
     */
    public function categoryDeactivate($id)
    {
        // Validate ID
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            Session::setFlash('error', 'Invalid category ID');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        $id = (int) $id;
        $category = $this->categoryModel->find($id);

        if (!$category) {
            Session::setFlash('error', 'Category not found');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        if ($this->categoryModel->deactivate($id)) {
            Session::setFlash('success', 'Category deactivated successfully');
        } else {
            Session::setFlash('error', 'Failed to deactivate category');
        }

        header('Location: ' . SITE_URL . '/admin/categories');
        exit;
    }

    /**
     * Create a new category (used only by categoryAdd)
     */
    private function createCategory()
    {
        $name = Validator::sanitize($_POST['name'] ?? '');
        $description = Validator::sanitize($_POST['description'] ?? '');
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $displayOrder = !empty($_POST['display_order']) ? (int) $_POST['display_order'] : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Category name is required';
        }

        if (!empty($name) && $this->categoryModel->nameExists($name, null)) {
            $errors[] = 'Category name already exists';
        }

        $slug = $this->categoryModel->generateSlug($name, null);

        // Validate parent for create
        if ($parentId) {
            $parentCategory = $this->categoryModel->getById($parentId);
            if (!$parentCategory) {
                $errors[] = 'Selected parent category does not exist';
            }
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'parent_id' => $parentId,
            'display_order' => $displayOrder,
            'is_active' => $isActive
        ];

        // Handle image upload (create only)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new ImageUpload();
            $uploadDir = 'categories';
            $result = $uploader->upload($_FILES['image'], $uploadDir, 'category_');

            if ($result['success']) {
                $imageUrl = $result['relative_path'];
                if (strpos($imageUrl, '/') !== 0) {
                    $imageUrl = '/' . $imageUrl;
                }
                $data['image'] = $imageUrl;
            } else {
                $errors[] = $result['error'] ?? 'Failed to upload image';
            }
        }

        if (empty($errors)) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $newCategoryId = $this->categoryModel->create($data);

            if ($newCategoryId) {
                Session::setFlash('success', 'Category added successfully');
                header('Location: ' . SITE_URL . '/admin/categories');
                exit;
            }

            $errors[] = 'Failed to create category';
        }

        // If errors, re-render add form
        $parentCategories = $this->categoryModel->getParentCategories(true);
        $formData = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'parent_id' => $_POST['parent_id'] ?? null,
            'display_order' => $_POST['display_order'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        $this->render('admin/categories/form', [
            'category' => null,
            'parentCategories' => $parentCategories,
            'errors' => $errors,
            'data' => $formData,
            'action' => 'Add',
            'mode' => 'add'
        ]);
    }

    /**
     * Update an existing category (used only by categoryEdit)
     * The provided $category array is the row fetched for the URL ID and is
     * used purely as context; all updates target that ID only.
     */
    private function updateCategory(int $categoryId, array $category)
    {
        $name = Validator::sanitize($_POST['name'] ?? '');
        $description = Validator::sanitize($_POST['description'] ?? '');
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $displayOrder = !empty($_POST['display_order']) ? (int) $_POST['display_order'] : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Category name is required';
        }

        if (!empty($name) && $this->categoryModel->nameExists($name, $categoryId)) {
            $errors[] = 'Category name already exists';
        }

        $slug = $this->categoryModel->generateSlug($name, $categoryId);

        // Validate parent: cannot be self or any of its children
        if ($parentId) {
            $parentCategory = $this->categoryModel->getById($parentId);
            if (!$parentCategory) {
                $errors[] = 'Selected parent category does not exist';
            } elseif ($parentId === $categoryId) {
                $errors[] = 'Category cannot be its own parent';
            } else {
                $childCategories = $this->categoryModel->getChildCategories($categoryId, true);
                foreach ($childCategories as $child) {
                    if ((int) $child['category_id'] === $parentId) {
                        $errors[] = 'Cannot set a child category as parent';
                        break;
                    }
                }
            }
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'parent_id' => $parentId,
            'display_order' => $displayOrder,
            'is_active' => $isActive
        ];

        // Handle optional image replacement
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new ImageUpload();
            $uploadDir = 'categories';
            $result = $uploader->upload($_FILES['image'], $uploadDir, 'category_');

            if ($result['success']) {
                $imageUrl = $result['relative_path'];
                if (strpos($imageUrl, '/') !== 0) {
                    $imageUrl = '/' . $imageUrl;
                }
                $data['image'] = $imageUrl;

                // Delete old image for this exact category ID
                if (!empty($category['image'])) {
                    $oldImagePath = $category['image'];
                    $oldFilePath = (strpos($oldImagePath, '/') === 0) ? substr($oldImagePath, 1) : $oldImagePath;
                    $uploader->delete($oldFilePath);
                }
            } else {
                $errors[] = $result['error'] ?? 'Failed to upload image';
            }
        }

        if (empty($errors)) {
            try {
                $updateData = array_merge($data, ['updated_at' => date('Y-m-d H:i:s')]);
                $updateResult = $this->categoryModel->update($categoryId, $updateData);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'updated_at') !== false) {
                    $updateResult = $this->categoryModel->update($categoryId, $data);
                } else {
                    throw $e;
                }
            }

            if ($updateResult) {
                Session::setFlash('success', 'Category updated successfully');
                header('Location: ' . SITE_URL . '/admin/categories');
                exit;
            }

            $errors[] = 'Failed to update category';
        }

        // If there are validation or update errors, re-render the edit form
        $parentCategories = $this->categoryModel->getParentCategories(true);
        $childCategories = $this->categoryModel->getChildCategories($categoryId, true);
        $excludeIds = [$categoryId];
        foreach ($childCategories as $child) {
            $excludeIds[] = $child['category_id'];
        }
        $parentCategories = array_filter($parentCategories, function ($cat) use ($excludeIds) {
            return !in_array($cat['category_id'], $excludeIds);
        });

        $formData = [
            'name' => $_POST['name'] ?? $category['name'],
            'description' => $_POST['description'] ?? $category['description'],
            'parent_id' => $_POST['parent_id'] ?? $category['parent_id'],
            'display_order' => $_POST['display_order'] ?? $category['display_order'],
            'is_active' => isset($_POST['is_active']) ? 1 : (int) $category['is_active']
        ];

        $this->render('admin/categories/form', [
            'category' => $this->categoryModel->getById($categoryId),
            'parentCategories' => $parentCategories,
            'errors' => $errors,
            'data' => $formData,
            'action' => 'Edit',
            'mode' => 'edit'
        ]);
    }

    /**
     * Category Attributes Management - List all attributes
     * Handles routing: /admin/attributes
     */
    public function attributes(...$params)
    {
        // If there are params, it might be a sub-action (add, edit, delete, etc.)
        if (!empty($params[0])) {
            $subAction = $params[0];
            $remainingParams = array_slice($params, 1);

            // Map sub-actions to methods
            $actionMap = [
                'add' => 'attributeAdd',
                'edit' => 'attributeEdit',
                'delete' => 'attributeDelete',
                'activate' => 'attributeActivate',
                'deactivate' => 'attributeDeactivate',
                'toggle-required' => 'attributeToggleRequired'
            ];

            if (isset($actionMap[$subAction])) {
                $method = $actionMap[$subAction];
                if (method_exists($this, $method)) {
                    return call_user_func_array([$this, $method], $remainingParams);
                }
            }
        }

        // Default: list all attributes grouped by category and attribute group
        $search = Validator::sanitize($_GET['search'] ?? '');
        $categoryFilter = isset($_GET['category_id']) && !empty($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

        // Get all categories for filter dropdown
        $allCategories = $this->categoryModel->getAll(true);

        // Build query to get attributes with category info (legacy) and group info (new system)
        // This query shows:
        // 1. Attributes directly assigned to categories (legacy - has category_id)
        // 2. Attributes assigned to groups (new - has group_id, may or may not have category_id)
        $sql = "SELECT ca.*, 
                c.name as category_name, 
                c.slug as category_slug,
                ag.group_name,
                ag.group_id as attribute_group_id
                FROM category_attributes ca
                LEFT JOIN categories c ON ca.category_id = c.category_id
                LEFT JOIN attribute_groups ag ON ca.group_id = ag.group_id
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (ca.attribute_name LIKE ? OR c.name LIKE ? OR ag.group_name LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($categoryFilter > 0) {
            // Filter by category: show attributes directly assigned to this category OR
            // attributes in groups assigned to this category
            $sql .= " AND (ca.category_id = ? OR ca.group_id IN (
                SELECT group_id FROM category_attribute_groups WHERE category_id = ?
            ))";
            $params[] = $categoryFilter;
            $params[] = $categoryFilter;
        }

        $sql .= " ORDER BY 
                CASE WHEN c.name IS NOT NULL THEN 0 ELSE 1 END,
                c.name ASC, 
                ag.group_name ASC,
                ca.display_order ASC, 
                ca.attribute_name ASC";

        // Use database connection directly for cross-table query
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $attributes = $stmt->fetchAll();

        // Group attributes by category (legacy) or by attribute group (new system)
        $groupedAttributes = [];
        foreach ($attributes as $attribute) {
            // Determine grouping key: use category_id if available, otherwise use group_id
            $groupKey = null;
            $groupName = null;
            $groupType = null;

            if (!empty($attribute['category_id'])) {
                // Legacy: group by category
                $groupKey = 'cat_' . $attribute['category_id'];
                $groupName = $attribute['category_name'];
                $groupType = 'category';
            } elseif (!empty($attribute['attribute_group_id'])) {
                // New: group by attribute group
                $groupKey = 'group_' . $attribute['attribute_group_id'];
                $groupName = $attribute['group_name'] . ' (Attribute Group)';
                $groupType = 'group';
            } else {
                // Fallback: ungrouped attributes
                $groupKey = 'ungrouped';
                $groupName = 'Ungrouped Attributes';
                $groupType = 'ungrouped';
            }

            if (!isset($groupedAttributes[$groupKey])) {
                $groupedAttributes[$groupKey] = [
                    'group_key' => $groupKey,
                    'group_name' => $groupName,
                    'group_type' => $groupType,
                    'category_id' => $attribute['category_id'] ?? null,
                    'category_slug' => $attribute['category_slug'] ?? null,
                    'group_id' => $attribute['attribute_group_id'] ?? null,
                    'attributes' => []
                ];
            }

            // Decode options if select type
            if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                $attribute['options'] = json_decode($attribute['attribute_options'], true);
            } else {
                $attribute['options'] = [];
            }

            $groupedAttributes[$groupKey]['attributes'][] = $attribute;
        }

        $this->render('admin/attributes/index', [
            'groupedAttributes' => $groupedAttributes,
            'allCategories' => $allCategories,
            'search' => $search,
            'categoryFilter' => $categoryFilter
        ]);
    }

    /**
     * Add new attribute
     * URL: /admin/attributes/add
     */
    public function attributeAdd()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->createAttribute();
            return;
        }

        // GET: render empty form
        $categories = $this->categoryModel->getAll(true);
        require_once APP_PATH . '/models/AttributeGroup.php';
        $attributeGroupModel = new AttributeGroup();
        $attributeGroups = $attributeGroupModel->getAll(false);

        // Get all attributes for dependency selection
        $availableAttributes = $this->attributeModel->getAll(false);

        $this->render('admin/attributes/form', [
            'attribute' => null,
            'categories' => $categories,
            'attributeGroups' => $attributeGroups,
            'availableAttributes' => $availableAttributes,
            'action' => 'Add',
            'mode' => 'add',
            'errors' => [],
            'data' => []
        ]);
    }

    /**
     * Edit attribute
     * URL: /admin/attributes/edit/{id}
     */
    public function attributeEdit($id)
    {
        if (!is_numeric($id)) {
            Session::setFlash('error', 'Invalid attribute ID');
            header('Location: ' . SITE_URL . '/admin/attributes');
            exit;
        }

        $attributeId = (int) $id;
        // Use direct database query since Model methods aren't being recognized
        require_once APP_PATH . '/config/database.php';
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM category_attributes WHERE attribute_id = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$attributeId]);
        $attribute = $stmt->fetch();

        if (!$attribute) {
            Session::setFlash('error', 'Attribute not found');
            header('Location: ' . SITE_URL . '/admin/attributes');
            exit;
        }

        // Decode JSON options for select type attributes
        if ($attribute && $attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
            $attribute['options'] = json_decode($attribute['attribute_options'], true);
        } else {
            $attribute['options'] = [];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateAttribute($attributeId, $attribute);
            return;
        }

        // GET: render form populated with attribute data
        $categories = $this->categoryModel->getAll(true);
        require_once APP_PATH . '/models/AttributeGroup.php';
        $attributeGroupModel = new AttributeGroup();
        $attributeGroups = $attributeGroupModel->getAll(false);

        // Decode show_when if exists
        if (!empty($attribute['show_when'])) {
            $decoded = json_decode($attribute['show_when'], true);
            $attribute['show_when_decoded'] = (is_array($decoded)) ? $decoded : null;
        } else {
            $attribute['show_when_decoded'] = null;
        }

        // Get available attributes for dependency selection
        $availableAttributes = $this->attributeModel->getAll(false);

        $this->render('admin/attributes/form', [
            'attribute' => $attribute,
            'categories' => $categories,
            'attributeGroups' => $attributeGroups,
            'availableAttributes' => $availableAttributes,
            'action' => 'Edit',
            'mode' => 'edit',
            'errors' => [],
            'data' => []
        ]);
    }

    /**
     * Delete attribute
     */
    public function attributeDelete($id)
    {
        if (!is_numeric($id)) {
            Session::setFlash('error', 'Invalid attribute ID');
            header('Location: ' . SITE_URL . '/admin/attributes');
            exit;
        }

        $id = (int) $id;
        // Use database connection directly
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM category_attributes WHERE attribute_id = ?");
        $stmt->execute([$id]);
        $attribute = $stmt->fetch();

        if (!$attribute) {
            Session::setFlash('error', 'Attribute not found');
            header('Location: ' . SITE_URL . '/admin/attributes');
            exit;
        }

        // Check if attribute is used by any products
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM product_attributes WHERE attribute_id = ?");
        $stmt->execute([$id]);
        $productCount = $stmt->fetch()['count'];

        if ($productCount > 0) {
            Session::setFlash('error', 'Cannot delete attribute. It is used by ' . $productCount . ' product(s).');
            header('Location: ' . SITE_URL . '/admin/attributes');
            exit;
        }

        // Delete using direct database query
        $deleteStmt = $db->prepare("DELETE FROM category_attributes WHERE attribute_id = ?");
        if ($deleteStmt->execute([$id])) {
            Session::setFlash('success', 'Attribute deleted successfully');
        } else {
            Session::setFlash('error', 'Failed to delete attribute');
        }

        header('Location: ' . SITE_URL . '/admin/attributes');
        exit;
    }

    /**
     * Activate attribute
     */
    public function attributeActivate($id)
    {
        if (!is_numeric($id)) {
            Session::setFlash('error', 'Invalid attribute ID');
            header('Location: ' . SITE_URL . '/admin/attributes');
            exit;
        }

        $id = (int) $id;
        // Use database connection directly
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM category_attributes WHERE attribute_id = ?");
        $stmt->execute([$id]);
        $attribute = $stmt->fetch();

        if (!$attribute) {
            Session::setFlash('error', 'Attribute not found');
            header('Location: ' . SITE_URL . '/admin/attributes');
            exit;
        }

        // Update using direct database query
        $updateStmt = $db->prepare("UPDATE category_attributes SET is_active = 1 WHERE attribute_id = ?");
        if ($updateStmt->execute([$id])) {
            Session::setFlash('success', 'Attribute activated successfully');
        } else {
            Session::setFlash('error', 'Failed to activate attribute');
        }

        header('Location: ' . SITE_URL . '/admin/attributes');
        exit;
    }

    /**
     * Deactivate attribute
     */
    public function attributeDeactivate($id)
    {
        if (!is_numeric($id)) {
            Session::setFlash('error', 'Invalid attribute ID');
            header('Location: ' . SITE_URL . '/admin/attributes');
            exit;
        }

        $id = (int) $id;
        // Use database connection directly
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM category_attributes WHERE attribute_id = ?");
        $stmt->execute([$id]);
        $attribute = $stmt->fetch();

        if (!$attribute) {
            Session::setFlash('error', 'Attribute not found');
            header('Location: ' . SITE_URL . '/admin/attributes');
            exit;
        }

        // Update using direct database query
        $updateStmt = $db->prepare("UPDATE category_attributes SET is_active = 0 WHERE attribute_id = ?");
        if ($updateStmt->execute([$id])) {
            Session::setFlash('success', 'Attribute deactivated successfully');
        } else {
            Session::setFlash('error', 'Failed to deactivate attribute');
        }

        header('Location: ' . SITE_URL . '/admin/attributes');
        exit;
    }

    /**
     * Toggle attribute required status (AJAX endpoint)
     * URL: /admin/attributes/toggle-required/{id}
     */
    public function attributeToggleRequired($id)
    {
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        if (!is_numeric($id)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid attribute ID']);
            exit;
        }

        $id = (int) $id;

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $isRequired = isset($input['is_required']) ? (int) $input['is_required'] : 0;

        // Validate is_required value (should be 0 or 1)
        if ($isRequired !== 0 && $isRequired !== 1) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid required status value']);
            exit;
        }

        // Use database connection directly
        $db = Database::getInstance()->getConnection();

        // Check if attribute exists
        $stmt = $db->prepare("SELECT * FROM category_attributes WHERE attribute_id = ?");
        $stmt->execute([$id]);
        $attribute = $stmt->fetch();

        if (!$attribute) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Attribute not found']);
            exit;
        }

        // Update is_required status
        $updateStmt = $db->prepare("UPDATE category_attributes SET is_required = ? WHERE attribute_id = ?");
        if ($updateStmt->execute([$isRequired, $id])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => $isRequired ? 'Attribute marked as required' : 'Attribute marked as optional',
                'is_required' => $isRequired
            ]);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update attribute required status']);
        }
        exit;
    }

    /**
     * Create a new attribute
     */
    private function createAttribute()
    {
        $categoryId = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $groupId = !empty($_POST['group_id']) ? (int) $_POST['group_id'] : null;
        $attributeName = Validator::sanitize($_POST['attribute_name'] ?? '');
        $attributeType = Validator::sanitize($_POST['attribute_type'] ?? 'text');
        $attributeOptions = $_POST['attribute_options'] ?? '';
        $isRequired = isset($_POST['is_required']) ? 1 : 0;
        $displayOrder = !empty($_POST['display_order']) ? (int) $_POST['display_order'] : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];

        // At least one of category_id or group_id must be provided
        if (empty($categoryId) && empty($groupId)) {
            $errors[] = 'Either Category or Attribute Group must be selected';
        }

        // Validate category if provided
        if (!empty($categoryId)) {
            $category = $this->categoryModel->getById($categoryId);
            if (!$category) {
                $errors[] = 'Selected category does not exist';
            }
        }

        // Validate group if provided
        if (!empty($groupId)) {
            require_once APP_PATH . '/models/AttributeGroup.php';
            $attributeGroupModel = new AttributeGroup();
            $group = $attributeGroupModel->getById($groupId);
            if (!$group) {
                $errors[] = 'Selected attribute group does not exist';
            }
        }

        if (empty($attributeName)) {
            $errors[] = 'Attribute name is required';
        } else {
            // Check if attribute name already exists
            if ($this->attributeModel->nameExists($attributeName, $groupId, $categoryId)) {
                $errors[] = 'Attribute name "' . htmlspecialchars($attributeName) . '" is already in use. Please choose a different name.';
            }
        }

        if (!in_array($attributeType, ['text', 'select', 'number', 'textarea', 'color'])) {
            $errors[] = 'Invalid attribute type';
        }

        // Validate and process options for select type
        $optionsJson = null;
        if ($attributeType === 'select') {
            if (empty($attributeOptions)) {
                $errors[] = 'Options are required for select type attributes';
            } else {
                // Parse options (comma-separated or JSON array)
                $options = [];
                if (strpos($attributeOptions, '[') === 0) {
                    // JSON array format
                    $decoded = json_decode($attributeOptions, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $options = $decoded;
                    } else {
                        $errors[] = 'Invalid JSON format for options';
                    }
                } else {
                    // Comma-separated format
                    $options = array_map('trim', explode(',', $attributeOptions));
                    $options = array_filter($options); // Remove empty values
                }

                if (empty($options)) {
                    $errors[] = 'At least one option is required for select type';
                } else {
                    $optionsJson = json_encode($options);
                }
            }
        }

        // Process dependency rules
        $dependsOn = !empty($_POST['depends_on']) ? (int) $_POST['depends_on'] : null;
        $showWhenType = $_POST['show_when_type'] ?? '';
        $showWhenValue = $_POST['show_when_value'] ?? '';
        $showWhenValues = $_POST['show_when_values'] ?? '';
        $isFilterable = isset($_POST['is_filterable']) ? 1 : 0;
        $isVariant = isset($_POST['is_variant']) ? 1 : 0;

        $showWhenJson = null;
        if ($dependsOn) {
            if ($showWhenType === 'value' && !empty($showWhenValue)) {
                $showWhenJson = json_encode(['value' => trim($showWhenValue)]);
            } elseif (($showWhenType === 'in' || $showWhenType === 'not_in') && !empty($showWhenValues)) {
                $values = array_map('trim', explode(',', $showWhenValues));
                $values = array_filter($values); // Remove empty
                if (!empty($values)) {
                    $showWhenJson = json_encode(['operator' => $showWhenType, 'values' => $values]);
                }
            }
            if (!$showWhenJson) {
                $errors[] = 'Show When condition is required when Depends On is selected';
            }
        }

        if (empty($errors)) {
            // Insert attribute directly using database connection
            require_once APP_PATH . '/config/database.php';
            $db = Database::getInstance()->getConnection();

            $sql = "INSERT INTO category_attributes (category_id, group_id, attribute_name, attribute_type, attribute_options, is_required, display_order, is_active, depends_on, show_when, is_filterable, is_variant, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $params = [
                !empty($categoryId) ? $categoryId : null, // category_id is optional (can be NULL if group_id is provided)
                !empty($groupId) ? $groupId : null, // group_id is optional (can be NULL if category_id is provided)
                $attributeName,
                $attributeType,
                $optionsJson,
                $isRequired ? 1 : 0,
                $displayOrder,
                $isActive ? 1 : 0,
                $dependsOn,
                $showWhenJson,
                $isFilterable,
                $isVariant
            ];

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $newAttributeId = $db->lastInsertId();


            if ($newAttributeId) {
                Session::setFlash('success', 'Attribute added successfully');
                header('Location: ' . SITE_URL . '/admin/attributes');
                exit;
            }

            $errors[] = 'Failed to create attribute';
        }

        // If errors, re-render form
        $categories = $this->categoryModel->getAll(true);
        require_once APP_PATH . '/models/AttributeGroup.php';
        $attributeGroupModel = new AttributeGroup();
        $attributeGroups = $attributeGroupModel->getAll(false);
        $formData = [
            'category_id' => $_POST['category_id'] ?? '',
            'group_id' => $_POST['group_id'] ?? '',
            'attribute_name' => $_POST['attribute_name'] ?? '',
            'attribute_type' => $_POST['attribute_type'] ?? 'text',
            'attribute_options' => $_POST['attribute_options'] ?? '',
            'is_required' => isset($_POST['is_required']) ? 1 : 0,
            'display_order' => $_POST['display_order'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Get available attributes for dependency selection
        $availableAttributes = [];
        if (!empty($groupId)) {
            $availableAttributes = $this->attributeModel->getByGroup($groupId, false);
        } elseif (!empty($categoryId)) {
            $availableAttributes = $this->attributeModel->getByCategory($categoryId, false);
        } else {
            // If no group/category selected yet, get all attributes
            $availableAttributes = $this->attributeModel->getAll(false);
        }

        $this->render('admin/attributes/form', [
            'attribute' => null,
            'categories' => $categories,
            'attributeGroups' => $attributeGroups,
            'availableAttributes' => $availableAttributes,
            'errors' => $errors,
            'data' => $formData,
            'action' => 'Add',
            'mode' => 'add'
        ]);
    }

    /**
     * Update an existing attribute
     */
    private function updateAttribute(int $attributeId, array $attribute)
    {
        $categoryId = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $groupId = !empty($_POST['group_id']) ? (int) $_POST['group_id'] : null;
        $attributeName = Validator::sanitize($_POST['attribute_name'] ?? '');
        $attributeType = Validator::sanitize($_POST['attribute_type'] ?? 'text');
        $attributeOptions = $_POST['attribute_options'] ?? '';
        $isRequired = isset($_POST['is_required']) ? 1 : 0;
        $displayOrder = !empty($_POST['display_order']) ? (int) $_POST['display_order'] : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];

        // At least one of category_id or group_id must be provided
        if (empty($categoryId) && empty($groupId)) {
            $errors[] = 'Either Category or Attribute Group must be selected';
        }

        // Validate category if provided
        if (!empty($categoryId)) {
            $category = $this->categoryModel->getById($categoryId);
            if (!$category) {
                $errors[] = 'Selected category does not exist';
            }
        }

        // Validate group if provided
        if (!empty($groupId)) {
            require_once APP_PATH . '/models/AttributeGroup.php';
            $attributeGroupModel = new AttributeGroup();
            $group = $attributeGroupModel->getById($groupId);
            if (!$group) {
                $errors[] = 'Selected attribute group does not exist';
            }
        }

        if (empty($attributeName)) {
            $errors[] = 'Attribute name is required';
        } else {
            // Check if attribute name already exists (excluding current attribute)
            if ($this->attributeModel->nameExists($attributeName, $groupId, $categoryId, $attributeId)) {
                $errors[] = 'Attribute name "' . htmlspecialchars($attributeName) . '" is already in use. Please choose a different name.';
            }
        }

        if (!in_array($attributeType, ['text', 'select', 'number', 'textarea', 'color'])) {
            $errors[] = 'Invalid attribute type';
        }

        // Validate and process options for select type
        $optionsJson = null;
        if ($attributeType === 'select') {
            if (empty($attributeOptions)) {
                $errors[] = 'Options are required for select type attributes';
            } else {
                // Parse options (comma-separated or JSON array)
                $options = [];
                if (strpos($attributeOptions, '[') === 0) {
                    // JSON array format
                    $decoded = json_decode($attributeOptions, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $options = $decoded;
                    } else {
                        $errors[] = 'Invalid JSON format for options';
                    }
                } else {
                    // Comma-separated format
                    $options = array_map('trim', explode(',', $attributeOptions));
                    $options = array_filter($options); // Remove empty values
                }

                if (empty($options)) {
                    $errors[] = 'At least one option is required for select type';
                } else {
                    $optionsJson = json_encode($options);
                }
            }
        }

        // Process dependency rules
        $dependsOn = !empty($_POST['depends_on']) ? (int) $_POST['depends_on'] : null;
        $showWhenType = $_POST['show_when_type'] ?? '';
        $showWhenValue = $_POST['show_when_value'] ?? '';
        $showWhenValues = $_POST['show_when_values'] ?? '';
        $isFilterable = isset($_POST['is_filterable']) ? 1 : 0;
        $isVariant = isset($_POST['is_variant']) ? 1 : 0;

        $showWhenJson = null;
        if ($dependsOn) {
            if ($showWhenType === 'value' && !empty($showWhenValue)) {
                $showWhenJson = json_encode(['value' => trim($showWhenValue)]);
            } elseif (($showWhenType === 'in' || $showWhenType === 'not_in') && !empty($showWhenValues)) {
                $values = array_map('trim', explode(',', $showWhenValues));
                $values = array_filter($values); // Remove empty
                if (!empty($values)) {
                    $showWhenJson = json_encode(['operator' => $showWhenType, 'values' => $values]);
                }
            }
            if (!$showWhenJson) {
                $errors[] = 'Show When condition is required when Depends On is selected';
            }
        }

        if (empty($errors)) {
            // Update attribute directly using database connection
            require_once APP_PATH . '/config/database.php';
            $db = Database::getInstance()->getConnection();

            $sql = "UPDATE category_attributes 
                    SET category_id = ?, group_id = ?, attribute_name = ?, attribute_type = ?, attribute_options = ?, 
                        is_required = ?, display_order = ?, is_active = ?, depends_on = ?, show_when = ?, is_filterable = ?, is_variant = ?, updated_at = NOW() 
                    WHERE attribute_id = ?";

            $params = [
                !empty($categoryId) ? $categoryId : null, // category_id is optional (can be NULL if group_id is provided)
                !empty($groupId) ? $groupId : null, // group_id is optional (can be NULL if category_id is provided)
                $attributeName,
                $attributeType,
                $optionsJson,
                $isRequired ? 1 : 0,
                $displayOrder,
                $isActive ? 1 : 0,
                $dependsOn,
                $showWhenJson,
                $isFilterable,
                $isVariant,
                $attributeId
            ];

            $stmt = $db->prepare($sql);

            if ($stmt->execute($params)) {
                Session::setFlash('success', 'Attribute updated successfully');
                header('Location: ' . SITE_URL . '/admin/attributes');
                exit;
            }

            $errors[] = 'Failed to update attribute';
        }

        // If errors, re-render form
        $categories = $this->categoryModel->getAll(true);
        require_once APP_PATH . '/models/AttributeGroup.php';
        $attributeGroupModel = new AttributeGroup();
        $attributeGroups = $attributeGroupModel->getAll(false);
        $formData = [
            'category_id' => $_POST['category_id'] ?? $attribute['category_id'],
            'group_id' => $_POST['group_id'] ?? $attribute['group_id'],
            'attribute_name' => $_POST['attribute_name'] ?? $attribute['attribute_name'],
            'attribute_type' => $_POST['attribute_type'] ?? $attribute['attribute_type'],
            'attribute_options' => $_POST['attribute_options'] ?? ($attribute['attribute_options'] ?? ''),
            'is_required' => isset($_POST['is_required']) ? 1 : (int) $attribute['is_required'],
            'display_order' => $_POST['display_order'] ?? $attribute['display_order'],
            'is_active' => isset($_POST['is_active']) ? 1 : (int) $attribute['is_active']
        ];

        // Get attribute with decoded options using direct database query
        require_once APP_PATH . '/config/database.php';
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM category_attributes WHERE attribute_id = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$attributeId]);
        $attributeForRender = $stmt->fetch();
        if ($attributeForRender && $attributeForRender['attribute_type'] === 'select' && !empty($attributeForRender['attribute_options'])) {
            $attributeForRender['options'] = json_decode($attributeForRender['attribute_options'], true);
        } else {
            $attributeForRender['options'] = [];
        }

        // Decode show_when if exists
        if (!empty($attributeForRender['show_when'])) {
            $decoded = json_decode($attributeForRender['show_when'], true);
            $attributeForRender['show_when_decoded'] = (is_array($decoded)) ? $decoded : null;
        } else {
            $attributeForRender['show_when_decoded'] = null;
        }

        // Get available attributes for dependency selection
        $availableAttributes = $this->attributeModel->getAll(false);

        $this->render('admin/attributes/form', [
            'attribute' => $attributeForRender,
            'categories' => $categories,
            'attributeGroups' => $attributeGroups,
            'availableAttributes' => $availableAttributes,
            'errors' => $errors,
            'data' => $formData,
            'action' => 'Edit',
            'mode' => 'edit'
        ]);
    }

    /**
     * Attribute Groups Management
     * URL: /admin/attribute-groups
     */
    public function attributeGroups(...$params)
    {
        require_once APP_PATH . '/models/AttributeGroup.php';
        $attributeGroupModel = new AttributeGroup();

        // If there are params, it might be a sub-action
        if (!empty($params[0])) {
            $subAction = $params[0];
            $remainingParams = array_slice($params, 1);

            $actionMap = [
                'add' => 'attributeGroupAdd',
                'edit' => 'attributeGroupEdit',
                'delete' => 'attributeGroupDelete',
                'assign' => 'attributeGroupAssignToCategory',
                'assign-to-category' => 'attributeGroupAssignToCategory'
            ];

            if (isset($actionMap[$subAction])) {
                $method = $actionMap[$subAction];
                if (method_exists($this, $method)) {
                    return call_user_func_array([$this, $method], $remainingParams);
                }
            }
        }

        // Default: list all attribute groups
        $groups = $attributeGroupModel->getAll(false);

        // Get attribute count for each group
        foreach ($groups as &$group) {
            $attributes = $attributeGroupModel->getAttributes($group['group_id'], false);
            $group['attribute_count'] = count($attributes);
        }
        unset($group);

        $this->render('admin/attribute_groups/index', [
            'groups' => $groups
        ]);
    }

    /**
     * Add new attribute group
     */
    public function attributeGroupAdd()
    {
        require_once APP_PATH . '/models/AttributeGroup.php';
        $attributeGroupModel = new AttributeGroup();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groupName = Validator::sanitize($_POST['group_name'] ?? '');
            $description = Validator::sanitize($_POST['description'] ?? '');
            $displayOrder = isset($_POST['display_order']) ? (int) $_POST['display_order'] : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $errors = [];

            if (empty($groupName)) {
                $errors[] = 'Group name is required';
            }

            if (empty($errors)) {
                $data = [
                    'group_name' => $groupName,
                    'description' => $description,
                    'display_order' => $displayOrder,
                    'is_active' => $isActive
                ];

                if ($attributeGroupModel->createGroup($data)) {
                    Session::setFlash('success', 'Attribute group created successfully');
                    header('Location: ' . SITE_URL . '/admin/attribute-groups');
                    exit;
                } else {
                    $errors[] = 'Failed to create attribute group';
                }
            }

            $this->render('admin/attribute_groups/form', [
                'group' => null,
                'errors' => $errors,
                'data' => $_POST,
                'action' => 'Add'
            ]);
            return;
        }

        // GET: render form
        $this->render('admin/attribute_groups/form', [
            'group' => null,
            'errors' => [],
            'data' => [],
            'action' => 'Add'
        ]);
    }

    /**
     * Edit attribute group
     */
    public function attributeGroupEdit($id)
    {
        require_once APP_PATH . '/models/AttributeGroup.php';
        $attributeGroupModel = new AttributeGroup();

        if (!is_numeric($id)) {
            Session::setFlash('error', 'Invalid group ID');
            header('Location: ' . SITE_URL . '/admin/attribute-groups');
            exit;
        }

        $groupId = (int) $id;
        $group = $attributeGroupModel->getById($groupId);

        if (!$group) {
            Session::setFlash('error', 'Attribute group not found');
            header('Location: ' . SITE_URL . '/admin/attribute-groups');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groupName = Validator::sanitize($_POST['group_name'] ?? '');
            $description = Validator::sanitize($_POST['description'] ?? '');
            $displayOrder = isset($_POST['display_order']) ? (int) $_POST['display_order'] : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $errors = [];

            if (empty($groupName)) {
                $errors[] = 'Group name is required';
            }

            if (empty($errors)) {
                $data = [
                    'group_name' => $groupName,
                    'description' => $description,
                    'display_order' => $displayOrder,
                    'is_active' => $isActive
                ];

                if ($attributeGroupModel->updateGroup($groupId, $data)) {
                    Session::setFlash('success', 'Attribute group updated successfully');
                    header('Location: ' . SITE_URL . '/admin/attribute-groups');
                    exit;
                } else {
                    $errors[] = 'Failed to update attribute group';
                }
            }

            $this->render('admin/attribute_groups/form', [
                'group' => $group,
                'errors' => $errors,
                'data' => $_POST,
                'action' => 'Edit'
            ]);
            return;
        }

        // GET: render form
        $this->render('admin/attribute_groups/form', [
            'group' => $group,
            'errors' => [],
            'data' => [],
            'action' => 'Edit'
        ]);
    }

    /**
     * Delete attribute group
     */
    public function attributeGroupDelete($id)
    {
        require_once APP_PATH . '/models/AttributeGroup.php';
        $attributeGroupModel = new AttributeGroup();

        if (!is_numeric($id)) {
            Session::setFlash('error', 'Invalid group ID');
            header('Location: ' . SITE_URL . '/admin/attribute-groups');
            exit;
        }

        $groupId = (int) $id;
        $group = $attributeGroupModel->getById($groupId);

        if (!$group) {
            Session::setFlash('error', 'Attribute group not found');
            header('Location: ' . SITE_URL . '/admin/attribute-groups');
            exit;
        }

        // Check if group has attributes
        $attributes = $attributeGroupModel->getAttributes($groupId, false);
        if (count($attributes) > 0) {
            Session::setFlash('error', 'Cannot delete attribute group. It contains ' . count($attributes) . ' attribute(s). Please remove or reassign attributes first.');
            header('Location: ' . SITE_URL . '/admin/attribute-groups');
            exit;
        }

        if ($attributeGroupModel->deleteGroup($groupId)) {
            Session::setFlash('success', 'Attribute group deleted successfully');
        } else {
            Session::setFlash('error', 'Failed to delete attribute group');
        }

        header('Location: ' . SITE_URL . '/admin/attribute-groups');
        exit;
    }

    /**
     * Assign attribute groups to category
     */
    public function attributeGroupAssignToCategory()
    {
        require_once APP_PATH . '/models/AttributeGroup.php';
        require_once APP_PATH . '/models/Category.php';
        $attributeGroupModel = new AttributeGroup();
        $categoryModel = new Category();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
            $groupIds = isset($_POST['group_ids']) && is_array($_POST['group_ids'])
                ? array_map('intval', $_POST['group_ids'])
                : [];

            if (!$categoryId) {
                Session::setFlash('error', 'Category is required');
                header('Location: ' . SITE_URL . '/admin/attribute-groups/assign');
                exit;
            }

            // Remove existing direct assignments (keep inherited for backward compatibility)
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM category_attribute_groups WHERE category_id = ? AND is_inherited = 0");
            $stmt->execute([$categoryId]);

            // Add new direct assignments
            foreach ($groupIds as $groupId) {
                $attributeGroupModel->assignToCategory($categoryId, $groupId, false);
            }

            // Note: Inheritance is computed dynamically when reading (by traversing up the category tree)
            // We don't need to store inherited groups in the database, but we keep the propagation
            // method for backward compatibility and potential future use
            // $this->propagateGroupInheritance($categoryId, $groupIds);

            Session::setFlash('success', 'Attribute groups assigned to category successfully');
            header('Location: ' . SITE_URL . '/admin/categories');
            exit;
        }

        // GET: render assignment form
        $categories = $categoryModel->getAll(true);
        $groups = $attributeGroupModel->getAll(false);

        $this->render('admin/attribute_groups/assign', [
            'categories' => $categories,
            'groups' => $groups
        ]);
    }

    /**
     * Propagate attribute group inheritance to child categories
     * When groups are assigned to a parent category, child categories should inherit them
     * @param int $parentCategoryId
     * @param array $groupIds Array of group IDs to propagate
     */
    private function propagateGroupInheritance($parentCategoryId, $groupIds)
    {
        require_once APP_PATH . '/models/Category.php';
        require_once APP_PATH . '/models/AttributeGroup.php';

        $categoryModel = new Category();
        $attributeGroupModel = new AttributeGroup();

        // Get all child categories (recursively)
        $childCategories = $this->getAllChildCategories($parentCategoryId);

        // For each child category, assign the groups as inherited
        foreach ($childCategories as $childCategoryId) {
            foreach ($groupIds as $groupId) {
                // Check if group is already assigned (directly or inherited)
                $existing = $attributeGroupModel->query(
                    "SELECT mapping_id, is_inherited FROM category_attribute_groups WHERE category_id = ? AND group_id = ?",
                    [$childCategoryId, $groupId]
                )->fetch();

                if ($existing) {
                    // If it exists but is not inherited, update it to inherited
                    // (This handles the case where a child had it directly assigned, then parent got it)
                    if (!$existing['is_inherited']) {
                        $attributeGroupModel->query(
                            "UPDATE category_attribute_groups SET is_inherited = 1 WHERE mapping_id = ?",
                            [$existing['mapping_id']]
                        );
                    }
                } else {
                    // Assign as inherited
                    $attributeGroupModel->assignToCategory($childCategoryId, $groupId, true);
                }
            }
        }
    }

    /**
     * Get all child categories recursively
     * @param int $parentCategoryId
     * @return array Array of child category IDs
     */
    private function getAllChildCategories($parentCategoryId)
    {
        $childCategories = [];
        $categoryModel = new Category();

        // Get direct children
        $directChildren = $categoryModel->getChildCategories($parentCategoryId, true);

        foreach ($directChildren as $child) {
            $childId = $child['category_id'];
            $childCategories[] = $childId;

            // Recursively get grandchildren
            $grandchildren = $this->getAllChildCategories($childId);
            $childCategories = array_merge($childCategories, $grandchildren);
        }

        return $childCategories;
    }

    /**
     * Hero Banner Management - List all banners
     */
    public function heroBanners()
    {
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = Validator::sanitize($_GET['status']);
        }
        if (!empty($_GET['target_type'])) {
            $filters['target_type'] = Validator::sanitize($_GET['target_type']);
        }

        $banners = $this->heroBannerModel->getAllBanners($filters);
        $categories = $this->categoryModel->getAll(true);

        $this->render('admin/hero_banners/index', [
            'banners' => $banners,
            'categories' => $categories,
            'filters' => $filters
        ]);
    }

    /**
     * Add new hero banner
     */
    public function heroBannerAdd()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleHeroBannerSave();
        } else {
            $categories = $this->categoryModel->getAll(true);
            $this->render('admin/hero_banners/form', [
                'banner' => null,
                'categories' => $categories,
                'action' => 'Add'
            ]);
        }
    }

    /**
     * Edit hero banner
     */
    public function heroBannerEdit($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleHeroBannerSave($id);
        } else {
            $banner = $this->heroBannerModel->find($id);
            if (!$banner) {
                Session::setFlash('error', 'Banner not found');
                header('Location: ' . SITE_URL . '/admin/hero-banners');
                exit;
            }

            $categories = $this->categoryModel->getAll(true);
            $this->render('admin/hero_banners/form', [
                'banner' => $banner,
                'categories' => $categories,
                'action' => 'Edit'
            ]);
        }
    }

    /**
     * Delete hero banner
     */
    public function heroBannerDelete($id)
    {
        $banner = $this->heroBannerModel->find($id);
        if (!$banner) {
            Session::setFlash('error', 'Banner not found');
            header('Location: ' . SITE_URL . '/admin/hero-banners');
            exit;
        }

        // Delete images
        $uploader = new ImageUpload();
        if (!empty($banner['desktop_image'])) {
            $uploader->delete($banner['desktop_image']);
        }
        if (!empty($banner['mobile_image'])) {
            $uploader->delete($banner['mobile_image']);
        }

        if ($this->heroBannerModel->delete($id)) {
            Session::setFlash('success', 'Banner deleted successfully');
        } else {
            Session::setFlash('error', 'Failed to delete banner');
        }

        header('Location: ' . SITE_URL . '/admin/hero-banners');
        exit;
    }

    /**
     * Toggle banner status
     */
    public function heroBannerToggleStatus($id)
    {
        if ($this->heroBannerModel->toggleStatus($id)) {
            Session::setFlash('success', 'Banner status updated');
        } else {
            Session::setFlash('error', 'Failed to update banner status');
        }

        header('Location: ' . SITE_URL . '/admin/hero-banners');
        exit;
    }

    /**
     * Update banner display order (AJAX)
     */
    public function heroBannerUpdateOrder()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['orders']) || !is_array($input['orders'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid order data']);
            exit;
        }

        if ($this->heroBannerModel->bulkUpdateDisplayOrders($input['orders'])) {
            echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update order']);
        }
        exit;
    }

    /**
     * Handle hero banner save (add/edit)
     */
    private function handleHeroBannerSave($bannerId = null)
    {
        // Sanitize CTA URL properly - only trim and strip tags, don't use htmlspecialchars
        // as it will break URL query parameters (e.g., ?category=summer)
        $ctaUrl = !empty($_POST['cta_url']) ? trim(strip_tags($_POST['cta_url'])) : '';

        $data = [
            'title' => Validator::sanitize($_POST['title'] ?? ''),
            'description' => Validator::sanitize($_POST['description'] ?? ''),
            'cta_text' => Validator::sanitize($_POST['cta_text'] ?? ''),
            'cta_url' => $ctaUrl,
            'priority' => (int) ($_POST['priority'] ?? 0),
            'status' => Validator::sanitize($_POST['status'] ?? 'active'),
            'device_visibility' => Validator::sanitize($_POST['device_visibility'] ?? 'both'),
            'target_type' => Validator::sanitize($_POST['target_type'] ?? 'homepage'),
            'target_id' => !empty($_POST['target_id']) ? (int) $_POST['target_id'] : null,
            'start_date' => !empty($_POST['start_date']) ? date('Y-m-d H:i:s', strtotime($_POST['start_date'])) : null,
            'end_date' => !empty($_POST['end_date']) ? date('Y-m-d H:i:s', strtotime($_POST['end_date'])) : null,
            'auto_slide_enabled' => isset($_POST['auto_slide_enabled']) ? 1 : 0,
            'slide_duration' => (int) ($_POST['slide_duration'] ?? 5000),
            'content_enabled' => isset($_POST['content_enabled']) && ($_POST['content_enabled'] === '1' || $_POST['content_enabled'] === true) ? 1 : 0,
            'image_enabled' => isset($_POST['image_enabled']) && ($_POST['image_enabled'] === '1' || $_POST['image_enabled'] === true) ? 1 : 0
        ];

        // Handle image uploads
        $uploader = new ImageUpload();

        if (!empty($_FILES['desktop_image']['tmp_name'])) {
            $result = $uploader->upload($_FILES['desktop_image'], 'banners', 'hero_desktop_');
            if ($result['success']) {
                $data['desktop_image'] = $result['relative_path'];
                // Delete old image if editing
                if ($bannerId) {
                    $oldBanner = $this->heroBannerModel->find($bannerId);
                    if (!empty($oldBanner['desktop_image'])) {
                        $uploader->delete($oldBanner['desktop_image']);
                    }
                }
            } else {
                Session::setFlash('error', 'Desktop image upload failed: ' . $result['error']);
                header('Location: ' . SITE_URL . '/admin/hero-banner/' . ($bannerId ? 'edit/' . $bannerId : 'add'));
                exit;
            }
        } elseif ($bannerId) {
            // Keep existing image
            $existing = $this->heroBannerModel->find($bannerId);
            $data['desktop_image'] = $existing['desktop_image'];
        }

        if (!empty($_FILES['mobile_image']['tmp_name'])) {
            $result = $uploader->upload($_FILES['mobile_image'], 'banners', 'hero_mobile_');
            if ($result['success']) {
                $data['mobile_image'] = $result['relative_path'];
                // Delete old image if editing
                if ($bannerId) {
                    $oldBanner = $this->heroBannerModel->find($bannerId);
                    if (!empty($oldBanner['mobile_image'])) {
                        $uploader->delete($oldBanner['mobile_image']);
                    }
                }
            } else {
                Session::setFlash('error', 'Mobile image upload failed: ' . $result['error']);
                header('Location: ' . SITE_URL . '/admin/hero-banner/' . ($bannerId ? 'edit/' . $bannerId : 'add'));
                exit;
            }
        } elseif ($bannerId) {
            // Keep existing image
            $existing = $this->heroBannerModel->find($bannerId);
            $data['mobile_image'] = $existing['mobile_image'];
        }

        // Set display order for new banners
        if (!$bannerId) {
            $data['display_order'] = $this->heroBannerModel->getNextDisplayOrder();
        }

        // Validate data
        $errors = $this->heroBannerModel->validate($data, $bannerId);
        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            header('Location: ' . SITE_URL . '/admin/hero-banner/' . ($bannerId ? 'edit/' . $bannerId : 'add'));
            exit;
        }

        // Save banner
        if ($bannerId) {
            if ($this->heroBannerModel->update($bannerId, $data)) {
                Session::setFlash('success', 'Banner updated successfully');
            } else {
                Session::setFlash('error', 'Failed to update banner');
            }
        } else {
            if ($this->heroBannerModel->create($data)) {
                Session::setFlash('success', 'Banner added successfully');
            } else {
                Session::setFlash('error', 'Failed to create banner');
            }
        }

        header('Location: ' . SITE_URL . '/admin/hero-banners');
        exit;
    }

    /**
     * Orders List (Admin)
     */
    public function orders()
    {
        // Check if first argument is an order ID (for order detail view)
        $args = func_get_args();
        if (!empty($args[0]) && is_numeric($args[0])) {
            $orderId = (int) $args[0];
            $this->orderDetail($orderId);
            return;
        }

        require_once APP_PATH . '/helpers/Pagination.php';

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 15;
        $perPage = max(10, min(100, $perPage)); // Between 10-100 (supports 10, 25, 50, 100)

        // Get filters
        // Support both 'order_status' and 'status' parameters for backward compatibility
        $orderStatusParam = isset($_GET['order_status']) ? $_GET['order_status'] : (isset($_GET['status']) ? $_GET['status'] : '');

        $filters = [
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
            'customer_name' => isset($_GET['customer_name']) ? trim($_GET['customer_name']) : '',
            'mobile' => isset($_GET['mobile']) ? trim($_GET['mobile']) : '',
            'order_status' => $orderStatusParam,
            'payment_status' => isset($_GET['payment_status']) ? $_GET['payment_status'] : '',
            'payment_method' => isset($_GET['payment_method']) ? $_GET['payment_method'] : '',
            'delivery_type' => isset($_GET['delivery_type']) ? $_GET['delivery_type'] : '',
            'price_min' => isset($_GET['price_min']) ? trim($_GET['price_min']) : '',
            'price_max' => isset($_GET['price_max']) ? trim($_GET['price_max']) : '',
            'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
            'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : ''
        ];

        // Remove empty filters
        $filters = array_filter($filters, function ($value) {
            return $value !== '';
        });

        // Get order analytics - ALWAYS use global counts (no filters)
        // Cards should show global metrics, only the table should be filtered
        $analytics = $this->orderModel->getOrderAnalytics([]);

        // Ensure analytics is always an array with all required keys
        if (!is_array($analytics) || empty($analytics)) {
            $analytics = [
                'orders_today' => 0,
                'orders_week' => 0,
                'orders_month' => 0,
                'pending_orders' => 0,
                'shipped_orders' => 0,
                'delivered_orders' => 0,
                'total_revenue' => 0,
                'revenue_month' => 0,
                'revenue_week' => 0,
                'revenue_today' => 0
            ];
        } else {
            // Ensure all keys exist with defaults
            $analytics['orders_today'] = $analytics['orders_today'] ?? 0;
            $analytics['pending_orders'] = $analytics['pending_orders'] ?? 0;
            $analytics['shipped_orders'] = $analytics['shipped_orders'] ?? 0;
            $analytics['delivered_orders'] = $analytics['delivered_orders'] ?? 0;
            $analytics['total_revenue'] = $analytics['total_revenue'] ?? 0;
        }

        // Get revenue trend data for chart (last 30 days)
        // IMPORTANT: Revenue chart is independent of Quick Insights card clicks
        // Only pass revenue-specific filters (from chart's own date picker)
        // Never pass order_status, payment_status, or table date filters
        $revenueFilters = [];
        if (isset($_GET['revenue_date_from'])) {
            $revenueFilters['revenue_date_from'] = $_GET['revenue_date_from'];
        }
        if (isset($_GET['revenue_date_to'])) {
            $revenueFilters['revenue_date_to'] = $_GET['revenue_date_to'];
        }
        $revenueTrend = $this->getRevenueTrend(30, $revenueFilters);

        // Get orders with enhanced filters
        $orders = $this->orderModel->getAllOrdersEnhanced($filters, $page, $perPage);
        $totalOrders = $this->orderModel->getAllOrdersCountEnhanced($filters);

        // Create pagination
        $pagination = new Pagination($totalOrders, $perPage, $page, SITE_URL . '/admin/orders');

        $this->render('admin/orders/index', [
            'orders' => $orders,
            'pagination' => $pagination,
            'filters' => $filters,
            'perPage' => $perPage,
            'analytics' => $analytics,
            'revenueTrend' => $revenueTrend
        ]);
    }

    /**
     * Get revenue trend data (AJAX)
     * Used for updating the chart without page reload
     */
    public function ordersRevenueData()
    {
        // Check if request is AJAX
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            // Allow if it accepts json
            $accepts = $_SERVER['HTTP_ACCEPT'] ?? '';
            if (strpos($accepts, 'application/json') === false) {
                 // Fallback or error if not intended for direct access, but for now allow testing
            }
        }

        $revenueFilters = [];
        if (isset($_GET['revenue_date_from'])) {
            $revenueFilters['revenue_date_from'] = $_GET['revenue_date_from'];
        }
        if (isset($_GET['revenue_date_to'])) {
            $revenueFilters['revenue_date_to'] = $_GET['revenue_date_to'];
        }

        // Default to 30 days if no date provided
        $revenueTrend = $this->getRevenueTrend(30, $revenueFilters);
        
        // Prepare data for Chart.js
        $labels = [];
        $data = [];
        
        // If we have a date range, ensure we have all dates filled in
        if (!empty($revenueFilters['revenue_date_from'])) {
            $startDate = new DateTime($revenueFilters['revenue_date_from']);
            // If to_date is not set, use today
            $endDate = !empty($revenueFilters['revenue_date_to']) 
                ? new DateTime($revenueFilters['revenue_date_to']) 
                : new DateTime(); // Today
                
            // Generate full date range map
            $dateMap = [];
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));
            
            foreach ($period as $dt) {
                $dateKey = $dt->format('Y-m-d');
                $labels[] = $dt->format('M d');
                $data[] = 0;
                $dateMap[$dateKey] = count($data) - 1;
            }
            
            // Fill with actual data
            foreach ($revenueTrend as $item) {
                $date = $item['date'] ?? '';
                if (isset($dateMap[$date])) {
                    $data[$dateMap[$date]] = (float)($item['revenue'] ?? 0);
                }
            }
        } else {
            // Default 30 days logic (similar to viewed in index.php)
            // But good to replicate here for consistency
            $dateMap = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('M d', strtotime($date));
                $data[] = 0;
                $dateMap[$date] = count($data) - 1;
            }

            foreach ($revenueTrend as $item) {
                $date = $item['date'] ?? '';
                if (isset($dateMap[$date])) {
                    $data[$dateMap[$date]] = (float)($item['revenue'] ?? 0);
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'labels' => $labels,
            'data' => $data,
            'total_revenue' => array_sum($data) // Optional extra
        ]);
        exit;
    }
    public function orderDetail($orderId)
    {
        $order = $this->orderModel->getOrderWithDetails($orderId);

        if (!$order) {
            Session::setFlash('error', 'Order not found');
            header('Location: ' . SITE_URL . '/admin/orders');
            exit;
        }

        $items = $this->orderModel->getOrderItems($orderId);
        $statusHistory = $this->orderModel->getStatusHistory($orderId);

        // Fetch Previous and Next Order IDs for navigation
        $prevOrder = $this->orderModel->query("SELECT order_id FROM orders WHERE order_id < ? ORDER BY order_id DESC LIMIT 1", [$orderId])->fetch();
        $nextOrder = $this->orderModel->query("SELECT order_id FROM orders WHERE order_id > ? ORDER BY order_id ASC LIMIT 1", [$orderId])->fetch();

        // Fetch Customer Stats
        $customerStats = [];
        if (!empty($order['user_id'])) {
            $customerStats = $this->userModel->query(
                "SELECT COUNT(*) as total_orders, SUM(final_amount) as total_spent 
                 FROM orders WHERE user_id = ? AND order_status != ?", 
                [$order['user_id'], ORDER_STATUS_CANCELLED]
            )->fetch();
        }

        // Fetch Payments
        $payments = $this->orderModel->getPayments($orderId);

        $this->render('admin/orders/detail', [
            'order' => $order,
            'items' => $items,
            'statusHistory' => $statusHistory,
            'prevOrder' => $prevOrder ? $prevOrder['order_id'] : null,
            'nextOrder' => $nextOrder ? $nextOrder['order_id'] : null,
            'customerStats' => $customerStats,
            'payments' => $payments
        ]);
    }

    /**
     * Update Order Status (AJAX)
     */
    public function updateOrderStatus()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
        $newStatus = isset($_POST['status']) ? $_POST['status'] : '';
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

        if (!$orderId || !$newStatus) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        // Validate status transition
        if (!$this->orderModel->isValidStatusTransition($order['order_status'], $newStatus)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status transition']);
            exit;
        }

        $userId = Session::getUserId();
        $result = $this->orderModel->updateOrderStatus($orderId, $newStatus, $userId, $notes);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Order status updated successfully',
                'order_status' => $newStatus
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
        }
        exit;
    }

    /**
     * Update Payment Status (AJAX)
     */
    public function updatePaymentStatus()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
        $newStatus = isset($_POST['status']) ? $_POST['status'] : '';

        if (!$orderId || !$newStatus) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        $validPaymentStatuses = [
            PAYMENT_STATUS_PENDING,
            PAYMENT_STATUS_PAID,
            PAYMENT_STATUS_FAILED,
            PAYMENT_STATUS_REFUNDED
        ];

        if (!in_array($newStatus, $validPaymentStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid payment status']);
            exit;
        }

        $userId = Session::getUserId();
        $result = $this->orderModel->updatePaymentStatus($orderId, $newStatus, $userId);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'payment_status' => $newStatus
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update payment status']);
        }
        exit;
    }

    /**
     * Update Shipping Details (AJAX)
     */
    public function updateShippingDetails()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;

        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Missing order ID']);
            exit;
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        $data = [];

        if (isset($_POST['courier_partner'])) {
            $data['courier_partner'] = trim($_POST['courier_partner']);
        }

        if (isset($_POST['tracking_id'])) {
            $data['tracking_id'] = trim($_POST['tracking_id']);
        }

        if (isset($_POST['estimated_delivery'])) {
            $data['estimated_delivery'] = $_POST['estimated_delivery'];
        }

        if (isset($_POST['delivery_type'])) {
            $data['delivery_type'] = $_POST['delivery_type'];
        }

        if (empty($data)) {
            echo json_encode(['success' => false, 'message' => 'No data to update']);
            exit;
        }

        $result = $this->orderModel->updateShippingDetails($orderId, $data);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Shipping details updated successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update shipping details']);
        }
        exit;
    }

    /**
     * Mark Order as Paid (Manual/COD)
     */
    public function markOrderPaid()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : 'Manual payment confirmation';

        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Missing order ID']);
            exit;
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        try {
            // Update Order Payment Status
            $this->orderModel->updatePaymentStatus($orderId, PAYMENT_STATUS_PAID, Session::getUserId());

            // Log Payment
            $this->orderModel->addPayment([
                'order_id' => $orderId,
                'payment_method' => $order['payment_method'],
                'amount' => $order['final_amount'],
                'status' => 'paid',
                'type' => 'payment',
                'notes' => $notes,
                'created_by' => Session::getUserId()
            ]);

            echo json_encode(['success' => true, 'message' => 'Order marked as paid']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error processing payment: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Process Refund
     */
    public function processOrderRefund()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
        $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

        if (!$orderId || $amount <= 0 || empty($reason)) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        if ($amount > $order['final_amount']) {
             echo json_encode(['success' => false, 'message' => 'Refund amount cannot exceed order total']);
             exit;
        }

        $result = $this->orderModel->processRefund($orderId, $amount, $reason, Session::getUserId());

        if ($result) {
            $newStatus = ($amount >= $order['final_amount']) ? PAYMENT_STATUS_REFUNDED : 'partially_refunded';
            $this->orderModel->updatePaymentStatus($orderId, $newStatus, Session::getUserId());
            echo json_encode(['success' => true, 'message' => 'Refund processed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to process refund']);
        }
        exit;
    }

    /**
     * Export Orders (CSV)
     * Accessible via: /admin/orders/export
     */
    public function ordersExport()
    {
        // Get filters from query string
        $filters = [
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
            'customer_name' => isset($_GET['customer_name']) ? trim($_GET['customer_name']) : '',
            'mobile' => isset($_GET['mobile']) ? trim($_GET['mobile']) : '',
            'order_status' => isset($_GET['order_status']) ? $_GET['order_status'] : '',
            'payment_status' => isset($_GET['payment_status']) ? $_GET['payment_status'] : '',
            'payment_method' => isset($_GET['payment_method']) ? $_GET['payment_method'] : '',
            'delivery_type' => isset($_GET['delivery_type']) ? $_GET['delivery_type'] : '',
            'price_min' => isset($_GET['price_min']) ? trim($_GET['price_min']) : '',
            'price_max' => isset($_GET['price_max']) ? trim($_GET['price_max']) : '',
            'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
            'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : ''
        ];

        // Remove empty filters
        $filters = array_filter($filters, function ($value) {
            return $value !== '';
        });

        // Get all orders matching filters (no pagination for export)
        $orders = $this->orderModel->getAllOrdersEnhanced($filters, 1, 10000);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV Header
        fputcsv($output, [
            'Order ID',
            'Order Number',
            'Order Date',
            'Customer Name',
            'Email',
            'Phone',
            'Payment Method',
            'Payment Status',
            'Order Status',
            'Total Amount',
            'Delivery Type',
            'Tracking ID',
            'Courier Partner',
            'Estimated Delivery',
            'Item Count'
        ]);

        // CSV Data
        foreach ($orders as $order) {
            fputcsv($output, [
                $order['order_id'],
                $order['order_number'],
                $order['created_at'],
                $order['customer_name'] ?? 'N/A',
                $order['email'] ?? '',
                $order['phone'] ?? '',
                $order['payment_method'] ?? '',
                $order['payment_status'] ?? '',
                $order['order_status'] ?? '',
                $order['final_amount'],
                $order['delivery_type'] ?? 'standard',
                $order['tracking_id'] ?? '',
                $order['courier_partner'] ?? '',
                $order['estimated_delivery'] ?? '',
                $order['item_count'] ?? 0
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Cancel Order (AJAX)
     */
    public function cancelOrder()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Missing order ID']);
            exit;
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        // Check if order can be cancelled
        $cancellableStatuses = [ORDER_STATUS_PENDING, ORDER_STATUS_CONFIRMED, ORDER_STATUS_PROCESSING];
        if (!in_array($order['order_status'], $cancellableStatuses)) {
            echo json_encode(['success' => false, 'message' => 'This order cannot be cancelled']);
            exit;
        }

        $userId = Session::getUserId();
        $result = $this->orderModel->updateOrderStatus($orderId, ORDER_STATUS_CANCELLED, $userId, $notes);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'order_status' => ORDER_STATUS_CANCELLED
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel order']);
        }
        exit;
    }

    /**
     * Settings Page
     */
    public function settings()
    {
        $settingsModel = new Settings();
        $activeSection = $_GET['section'] ?? 'general';

        // Get all settings grouped
        $allSettings = $settingsModel->getAll();
        $settingsByGroup = [];

        foreach ($allSettings as $key => $setting) {
            $group = $setting['group'] ?? 'general';
            if (!isset($settingsByGroup[$group])) {
                $settingsByGroup[$group] = [];
            }
            $settingsByGroup[$group][$key] = $setting;
        }

        // CRITICAL FIX: Ensure logo_type exists in general settings with a default value
        // This prevents the view from defaulting incorrectly when the setting doesn't exist yet
        if ($activeSection === 'general' && !isset($settingsByGroup['general']['logo_type'])) {
            // Check if logo_type exists in database but wasn't grouped correctly
            $logoTypeValue = $settingsModel->get('logo_type', 'text');
            $settingsByGroup['general']['logo_type'] = [
                'value' => $logoTypeValue,
                'type' => 'text',
                'group' => 'general',
                'is_encrypted' => false,
                'description' => 'Logo display type: image or text'
            ];
        }

        $pageTitle = 'Settings';
        $activeSubsection = $_GET['subsection'] ?? '';
        
        $this->render('admin/settings', [
            'pageTitle' => $pageTitle,
            'activeSection' => $activeSection,
            'activeSubsection' => $activeSubsection,
            'settingsByGroup' => $settingsByGroup,
            'allSettings' => $allSettings
        ]);
    }

    /**
     * Save Settings (AJAX)
     */
    public function settingsSave()
    {
        header('Content-Type: application/json');

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                exit;
            }

            $group = $_POST['group'] ?? '';

            if (empty($group)) {
                echo json_encode(['success' => false, 'message' => 'Settings group is required']);
                exit;
            }

            $settingsModel = new Settings();
            $settingsToSave = [];

            // Get existing settings to determine type and encryption
            try {
                $existingSettings = $settingsModel->getByGroup($group);
            } catch (Exception $e) {
                error_log("Settings save error - Failed to get existing settings for group '{$group}': " . $e->getMessage());

                // Provide specific error message based on error type
                $errorMessage = 'Failed to load existing settings.';
                $errorMsgLower = strtolower($e->getMessage());

                if (
                    strpos($errorMsgLower, 'does not exist') !== false ||
                    strpos($errorMsgLower, 'unknown table') !== false ||
                    strpos($errorMsgLower, 'table') !== false && strpos($errorMsgLower, 'not found') !== false
                ) {
                    $errorMessage = 'Settings table not found. Please run the database migration: database/add_settings_system.sql';
                } elseif (
                    strpos($errorMsgLower, 'connection') !== false ||
                    strpos($errorMsgLower, 'sqlstate') !== false ||
                    strpos($errorMsgLower, 'access denied') !== false ||
                    strpos($errorMsgLower, 'unknown database') !== false
                ) {
                    $errorMessage = 'Database connection error. Please check your database configuration.';
                    if (ENVIRONMENT === 'development') {
                        $errorMessage .= ' Run database/test_connection.php to diagnose the issue.';
                    }
                } elseif (
                    strpos($errorMsgLower, 'connection is not available') !== false ||
                    strpos($errorMsgLower, 'connection failed') !== false
                ) {
                    $errorMessage = 'Database connection is not available. Please verify MySQL is running and database credentials are correct.';
                }

                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage,
                    'error' => ENVIRONMENT === 'development' ? $e->getMessage() : null
                ]);
                exit;
            }

            // Extract settings from POST data
            // Handle both regular POST and FormData (which may use array notation)
            $settings = [];

            // First, try to get settings from $_POST with array notation
            if (isset($_POST['settings']) && is_array($_POST['settings'])) {
                $settings = $_POST['settings'];
            } else {
                // Fallback: parse settings from flat POST keys like "settings[key]"
                foreach ($_POST as $key => $value) {
                    // Match keys like "settings[key]" or "settings[key][subkey]"
                    if (preg_match('/^settings\[([^\]]+)\]/', $key, $matches)) {
                        $settingKey = $matches[1];
                        $settings[$settingKey] = $value;
                    }
                }
            }

            // If still empty, try parsing from raw input (for FormData with bracket notation)
            if (empty($settings) && !empty($_POST)) {
                // Try to parse multipart/form-data manually
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (strpos($contentType, 'multipart/form-data') !== false) {
                    // FormData sends data with bracket notation that PHP should parse
                    // But if it's not working, try parsing all POST keys
                    foreach ($_POST as $key => $value) {
                        // Check if key contains 'settings' anywhere
                        if (preg_match('/settings\[(.+?)\]/', $key, $matches)) {
                            $settings[$matches[1]] = $value;
                        }
                    }
                }
            }

            // Debug logging in development
            if (ENVIRONMENT === 'development' && empty($settings)) {
                error_log("Settings save - No settings found in POST. POST keys: " . implode(', ', array_keys($_POST)));
                error_log("Settings save - Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
                error_log("Settings save - POST data sample: " . print_r(array_slice($_POST, 0, 5, true), true));
            }

            // Handle file uploads
            // PHP restructures $_FILES when using bracket notation (settings[key])
            // So settings[store_logo] becomes $_FILES['settings']['name']['store_logo'], etc.
            if (!empty($_FILES)) {
                $uploader = new ImageUpload();

                // Check for nested structure (bracket notation)
                if (isset($_FILES['settings']) && is_array($_FILES['settings']['name'])) {
                    // Handle bracket notation: settings[key]
                    foreach ($_FILES['settings']['name'] as $settingKey => $fileName) {
                        // Skip if no file was uploaded
                        if ($_FILES['settings']['error'][$settingKey] === UPLOAD_ERR_NO_FILE) {
                            continue;
                        }

                        // Reconstruct file array for ImageUpload helper
                        $fileData = [
                            'name' => $_FILES['settings']['name'][$settingKey],
                            'type' => $_FILES['settings']['type'][$settingKey],
                            'tmp_name' => $_FILES['settings']['tmp_name'][$settingKey],
                            'error' => $_FILES['settings']['error'][$settingKey],
                            'size' => $_FILES['settings']['size'][$settingKey]
                        ];

                        if ($fileData['error'] === UPLOAD_ERR_OK && is_uploaded_file($fileData['tmp_name'])) {
                            try {
                                $subfolder = 'settings';
                                $prefix = str_replace('_', '_', $settingKey) . '_';

                                // Don't apply hard validation for logo_image - allow upload and resize after
                                // This matches the behavior of store_logo which works correctly
                                $maxWidth = null;
                                $maxHeight = null;

                                $result = $uploader->upload($fileData, $subfolder, $prefix, $maxWidth, $maxHeight);

                                if ($result['success']) {
                                    // Normalize path separators for cross-platform compatibility
                                    $relativePath = str_replace('\\', '/', $result['relative_path']);
                                    // Ensure path starts with / for consistency
                                    if (substr($relativePath, 0, 1) !== '/') {
                                        $relativePath = '/' . $relativePath;
                                    }

                                    // Apply dimension constraints after upload if needed
                                    if ($settingKey === 'logo_image' && file_exists(PUBLIC_PATH . $relativePath)) {
                                        $actualMaxHeight = isset($settings['logo_image_max_height']) ?
                                            (int) $settings['logo_image_max_height'] :
                                            (isset($existingSettings['logo_image_max_height']['value']) ?
                                                (int) $existingSettings['logo_image_max_height']['value'] : 200);
                                        $actualMaxWidth = isset($settings['logo_image_max_width']) ?
                                            (int) $settings['logo_image_max_width'] :
                                            (isset($existingSettings['logo_image_max_width']['value']) ?
                                                (int) $existingSettings['logo_image_max_width']['value'] : 400);

                                        $constrainResult = $uploader->constrainImage(PUBLIC_PATH . $relativePath, $actualMaxWidth, $actualMaxHeight);
                                        if (!$constrainResult['success']) {
                                            error_log("Logo image constraint warning: " . ($constrainResult['error'] ?? 'Unknown error'));
                                        }
                                    }

                                    $settings[$settingKey] = $relativePath;

                                    // If this is logo_image, ensure logo_type is set to 'image' if not already set
                                    if ($settingKey === 'logo_image' && (!isset($settings['logo_type']) || $settings['logo_type'] !== 'image')) {
                                        $settings['logo_type'] = 'image';
                                        if (ENVIRONMENT === 'development') {
                                            error_log("Settings save - Auto-setting logo_type to 'image' after logo_image upload");
                                        }
                                    }

                                    // Log successful upload in development
                                    if (ENVIRONMENT === 'development') {
                                        error_log("Settings save - File uploaded successfully for '{$settingKey}': {$relativePath}");
                                    }
                                } else {
                                    echo json_encode([
                                        'success' => false,
                                        'message' => 'File upload failed for ' . $settingKey . ': ' . ($result['error'] ?? 'Unknown error')
                                    ]);
                                    exit;
                                }
                            } catch (Exception $e) {
                                error_log("Settings save error - File upload failed for '{$settingKey}': " . $e->getMessage());
                                echo json_encode([
                                    'success' => false,
                                    'message' => 'File upload failed for ' . $settingKey . ': ' . $e->getMessage()
                                ]);
                                exit;
                            }
                        } elseif ($fileData['error'] !== UPLOAD_ERR_NO_FILE) {
                            $uploadErrors = [
                                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                            ];
                            $errorMsg = $uploadErrors[$fileData['error']] ?? 'Unknown upload error';
                            echo json_encode([
                                'success' => false,
                                'message' => 'File upload error for ' . $settingKey . ': ' . $errorMsg
                            ]);
                            exit;
                        }
                    }
                } else {
                    // Fallback: Handle flat structure (non-bracket notation)
                    foreach ($_FILES as $fieldName => $fileData) {
                        if (strpos($fieldName, 'settings[') === 0) {
                            $settingKey = preg_replace('/settings\[(.*?)\]/', '$1', $fieldName);

                            if ($fileData['error'] === UPLOAD_ERR_OK && is_uploaded_file($fileData['tmp_name'])) {
                                try {
                                    $subfolder = 'settings';
                                    $prefix = str_replace('_', '_', $settingKey) . '_';

                                    // Don't apply hard validation for logo_image - allow upload and resize after
                                    // This matches the behavior of store_logo which works correctly
                                    $maxWidth = null;
                                    $maxHeight = null;

                                    $result = $uploader->upload($fileData, $subfolder, $prefix, $maxWidth, $maxHeight);

                                    if ($result['success']) {
                                        // Normalize path separators for cross-platform compatibility
                                        $relativePath = str_replace('\\', '/', $result['relative_path']);
                                        // Ensure path starts with / for consistency
                                        if (substr($relativePath, 0, 1) !== '/') {
                                            $relativePath = '/' . $relativePath;
                                        }

                                        // Apply dimension constraints after upload if needed
                                        if ($settingKey === 'logo_image' && file_exists(PUBLIC_PATH . $relativePath)) {
                                            $actualMaxHeight = isset($settings['logo_image_max_height']) ?
                                                (int) $settings['logo_image_max_height'] :
                                                (isset($existingSettings['logo_image_max_height']['value']) ?
                                                    (int) $existingSettings['logo_image_max_height']['value'] : 200);
                                            $actualMaxWidth = isset($settings['logo_image_max_width']) ?
                                                (int) $settings['logo_image_max_width'] :
                                                (isset($existingSettings['logo_image_max_width']['value']) ?
                                                    (int) $existingSettings['logo_image_max_width']['value'] : 400);

                                            $constrainResult = $uploader->constrainImage(PUBLIC_PATH . $relativePath, $actualMaxWidth, $actualMaxHeight);
                                            if (!$constrainResult['success']) {
                                                error_log("Logo image constraint warning: " . ($constrainResult['error'] ?? 'Unknown error'));
                                            }
                                        }

                                        $settings[$settingKey] = $relativePath;

                                        // If this is logo_image, ensure logo_type is set to 'image' if not already set
                                        if ($settingKey === 'logo_image' && (!isset($settings['logo_type']) || $settings['logo_type'] !== 'image')) {
                                            $settings['logo_type'] = 'image';
                                            if (ENVIRONMENT === 'development') {
                                                error_log("Settings save - Auto-setting logo_type to 'image' after logo_image upload");
                                            }
                                        }

                                        // Log successful upload in development
                                        if (ENVIRONMENT === 'development') {
                                            error_log("Settings save - File uploaded successfully for '{$settingKey}': {$relativePath}");
                                        }
                                    } else {
                                        echo json_encode([
                                            'success' => false,
                                            'message' => 'File upload failed: ' . ($result['error'] ?? 'Unknown error')
                                        ]);
                                        exit;
                                    }
                                } catch (Exception $e) {
                                    error_log("Settings save error - File upload failed for '{$settingKey}': " . $e->getMessage());
                                    echo json_encode([
                                        'success' => false,
                                        'message' => 'File upload failed: ' . $e->getMessage()
                                    ]);
                                    exit;
                                }
                            } elseif ($fileData['error'] !== UPLOAD_ERR_NO_FILE) {
                                $uploadErrors = [
                                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                                ];
                                $errorMsg = $uploadErrors[$fileData['error']] ?? 'Unknown upload error';
                                echo json_encode([
                                    'success' => false,
                                    'message' => 'File upload error: ' . $errorMsg
                                ]);
                                exit;
                            }
                        }
                    }
                }
            }

            // Initialize settingsToSave array
            $settingsToSave = [];

            // Ensure logo_type is always included if it's in the POST data (even if not in $settings array)
            // This handles cases where logo_type might not be parsed correctly from POST
            // CRITICAL: logo_type must be explicitly captured from radio button selection
            if (isset($_POST['settings']['logo_type'])) {
                $settings['logo_type'] = $_POST['settings']['logo_type'];
            } elseif (isset($_POST['logo_type'])) {
                $settings['logo_type'] = $_POST['logo_type'];
            } else {
                // Try to find logo_type in raw POST data (for FormData submissions)
                foreach ($_POST as $key => $value) {
                    if (preg_match('/settings\[logo_type\]/', $key) || $key === 'logo_type') {
                        $settings['logo_type'] = $value;
                        break;
                    }
                }
            }

            // CRITICAL FIX: Always ensure logo_type is set if form is being submitted
            // If logo_type is missing but we have other settings, default to 'text' to prevent undefined state
            // But only if this is a general settings save
            if ($group === 'general' && !isset($settings['logo_type']) && !empty($settings)) {
                // If logo_type is not in POST, check if there's an existing value
                $existingLogoType = $existingSettings['logo_type']['value'] ?? 'text';
                $settings['logo_type'] = $existingLogoType;
            }

            if (empty($settings)) {
                // In development, provide more details
                $errorMsg = 'No settings to save. Please ensure the form contains settings fields.';
                if (ENVIRONMENT === 'development') {
                    $postKeys = array_keys($_POST);
                    $errorMsg .= ' Received POST keys: ' . (empty($postKeys) ? 'none' : implode(', ', $postKeys));
                    if (!empty($postKeys)) {
                        $errorMsg .= '. Sample data: ' . json_encode(array_slice($_POST, 0, 3, true));
                    }
                }
                echo json_encode([
                    'success' => false,
                    'message' => $errorMsg,
                    'debug' => ENVIRONMENT === 'development' ? [
                        'post_keys' => array_keys($_POST),
                        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
                        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'not set'
                    ] : null
                ]);
                exit;
            }

            foreach ($settings as $key => $value) {
                // Get setting metadata
                $existing = $existingSettings[$key] ?? null;

                // CRITICAL FIX: Handle checkbox values properly
                // Checkboxes need to be normalized to '1' or '0' string
                if ($existing && isset($existing['type']) && $existing['type'] === 'checkbox') {
                    // Normalize checkbox value: true/1/'1'/'true' -> '1', everything else -> '0'
                    if ($value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'on') {
                        $value = '1';
                    } else {
                        $value = '0';
                    }
                }

                // Special handling for logo_type - always save it, even if empty (defaults to 'text')
                if ($key === 'logo_type') {
                    // CRITICAL FIX: Ensure logo_type is always saved with a valid value
                    // Normalize the value to ensure it's either 'image' or 'text'
                    $value = trim($value);
                    if (empty($value) || !in_array($value, ['image', 'text'])) {
                        $value = 'text'; // Default to text if invalid
                    }
                    // logo_type is a text setting, not an image setting
                    $type = 'text';
                    $isEncrypted = false;
                    // CRITICAL: Ensure logo_type is always saved with group 'general'
                    $group = 'general';
                } else {
                    // Determine type - check if it's an image setting
                    $type = $existing['type'] ?? 'text';
                    // Auto-detect image type for known image settings
                    $imageSettings = ['store_logo', 'dashboard_logo', 'store_favicon', 'header_logo', 'footer_logo', 'og_image', 'twitter_image', 'logo_image'];
                    if (in_array($key, $imageSettings) && $type === 'text' && !empty($value)) {
                        $type = 'image';
                    }

                    $isEncrypted = $existing['is_encrypted'] ?? false;

                    // Skip if value is masked (encrypted field not changed)
                    if ($isEncrypted && ($value === '••••••••••••' || empty($value))) {
                        // Keep existing encrypted value - don't update
                        continue;
                    }
                }

                // For image/file type settings, preserve existing value if new value is empty
                // EXCEPT: Allow explicit clearing for dashboard_logo and logo_image (sent from remove button)
                // This prevents accidentally clearing image settings, but allows intentional removal
                if (($type === 'image' || $type === 'file') && empty($value) && !empty($existing['value'])) {
                    // Check if this is an explicit removal
                    // The remove button sends a hidden input with logo_image_remove flag
                    $isLogoImageRemoval = ($key === 'logo_image' && isset($_POST['logo_image_remove']) && $_POST['logo_image_remove'] === '1');
                    $isDashboardLogoRemoval = ($key === 'dashboard_logo' && $value === '' && array_key_exists('dashboard_logo', $settings));

                    if ($isLogoImageRemoval || $isDashboardLogoRemoval) {
                        // Allow clearing - set value to empty string
                        $value = '';
                        // Also delete the old file if it exists
                        if (!empty($existing['value'])) {
                            try {
                                $oldLogoPath = PUBLIC_PATH . '/' . ltrim($existing['value'], '/');
                                if (file_exists($oldLogoPath)) {
                                    @unlink($oldLogoPath);
                                }
                            } catch (Exception $e) {
                                error_log("Failed to delete old logo file: " . $e->getMessage());
                            }
                        }
                    } else {
                        // Keep existing image/file value - don't update
                        continue;
                    }
                }

                // Handle logo_image removal explicitly (when no new file is uploaded)
                if ($key === 'logo_image' && isset($_POST['logo_image_remove']) && $_POST['logo_image_remove'] === '1') {
                    // Check if no new file was uploaded
                    $hasNewFile = false;
                    if (!empty($_FILES)) {
                        if (isset($_FILES['settings']) && is_array($_FILES['settings']['name']) && isset($_FILES['settings']['name']['logo_image'])) {
                            $hasNewFile = $_FILES['settings']['error']['logo_image'] !== UPLOAD_ERR_NO_FILE;
                        }
                    }

                    // If no new file uploaded and removal is requested, clear the logo
                    if (!$hasNewFile) {
                        $value = '';
                    }
                }

                // Convert checkbox values - ensure it's always '1' or '0'
                if ($type === 'checkbox') {
                    // Normalize checkbox: true/1/'1'/'true'/'on' -> '1', everything else -> '0'
                    if ($value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'on') {
                        $value = '1';
                    } else {
                        $value = '0';
                    }
                }

                // For image/file uploads, verify the file exists before saving
                if (($type === 'image' || $type === 'file') && !empty($value)) {
                    $fullPath = strpos($value, PUBLIC_PATH) === 0 ? $value : PUBLIC_PATH . ltrim($value, '/');
                    if (!file_exists($fullPath)) {
                        error_log("Settings save warning - File not found for '{$key}': {$fullPath}");
                        // Still save the path - it might be a URL or the file might be in a different location
                        // But log the warning for debugging
                    }
                }

                $settingsToSave[$key] = [
                    'value' => $value,
                    'group' => $group,
                    'type' => $type,
                    'is_encrypted' => $isEncrypted
                ];
            }

            // CRITICAL: Auto-set maintenance_start_time when maintenance_mode_enabled is being enabled
            if ($group === 'maintenance' && isset($settingsToSave['maintenance_mode_enabled'])) {
                $isEnabling = ($settingsToSave['maintenance_mode_enabled']['value'] === '1');
                $existingMaintenance = $existingSettings['maintenance_mode_enabled'] ?? null;
                $wasEnabled = $existingMaintenance && ($existingMaintenance['value'] === true || $existingMaintenance['value'] === '1' || $existingMaintenance['value'] === 1);

                // If enabling maintenance and start_time is not set, set it now
                if ($isEnabling && !$wasEnabled) {
                    $settingsToSave['maintenance_start_time'] = [
                        'value' => date('Y-m-d H:i:s'),
                        'group' => 'maintenance',
                        'type' => 'datetime',
                        'is_encrypted' => false
                    ];
                }
                // If disabling maintenance, clear start_time and end_time
                elseif (!$isEnabling && $wasEnabled) {
                    $settingsToSave['maintenance_start_time'] = [
                        'value' => '',
                        'group' => 'maintenance',
                        'type' => 'datetime',
                        'is_encrypted' => false
                    ];
                    $settingsToSave['maintenance_end_time'] = [
                        'value' => '',
                        'group' => 'maintenance',
                        'type' => 'datetime',
                        'is_encrypted' => false
                    ];
                }
            }

            // Save settings with error handling
            $result = $settingsModel->updateBatch($settingsToSave);

            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Settings saved successfully'
                ]);
            } else {
                error_log("Settings save error - updateBatch failed: " . ($result['error'] ?? 'Unknown error'));
                echo json_encode([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to save settings',
                    'error' => ENVIRONMENT === 'development' ? ($result['error'] ?? null) : null
                ]);
            }
        } catch (Exception $e) {
            error_log("Settings save error - Exception: " . $e->getMessage());
            error_log("Settings save error - Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while saving settings',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : null
            ]);
        } catch (Error $e) {
            error_log("Settings save error - Fatal error: " . $e->getMessage());
            error_log("Settings save error - File: " . $e->getFile() . " Line: " . $e->getLine());
            echo json_encode([
                'success' => false,
                'message' => 'A fatal error occurred while saving settings',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : null
            ]);
        }
        exit;
    }

    /**
     * Test Integration Connection (AJAX)
     */
    public function settingsTestConnection()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $integration = $_POST['integration'] ?? '';
        $settingsModel = new Settings();

        $result = ['success' => false, 'message' => 'Unknown integration'];

        switch ($integration) {
            case 'smtp':
                $host = $settingsModel->get('integration_smtp_host');
                $port = $settingsModel->get('integration_smtp_port', 587);
                $username = $settingsModel->get('integration_smtp_username');
                $password = $settingsModel->get('integration_smtp_password');

                if (empty($host) || empty($username) || empty($password)) {
                    $result = ['success' => false, 'message' => 'SMTP credentials not configured'];
                } else {
                    // Simple connection test
                    $connection = @fsockopen($host, $port, $errno, $errstr, 5);
                    if ($connection) {
                        fclose($connection);
                        $result = ['success' => true, 'message' => 'SMTP connection successful'];
                    } else {
                        $result = ['success' => false, 'message' => 'SMTP connection failed: ' . $errstr];
                    }
                }
                break;

            case 'google_maps':
                $apiKey = $settingsModel->get('integration_google_maps_key');
                if (empty($apiKey)) {
                    $result = ['success' => false, 'message' => 'Google Maps API key not configured'];
                } else {
                    // Test API key with a simple geocoding request
                    $testUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=test&key=" . urlencode($apiKey);
                    $response = @file_get_contents($testUrl);
                    if ($response) {
                        $data = json_decode($response, true);
                        if (isset($data['status']) && $data['status'] !== 'REQUEST_DENIED') {
                            $result = ['success' => true, 'message' => 'Google Maps API key is valid'];
                        } else {
                            $result = ['success' => false, 'message' => 'Google Maps API key is invalid'];
                        }
                    } else {
                        $result = ['success' => false, 'message' => 'Could not test Google Maps API'];
                    }
                }
                break;
        }

        echo json_encode($result);
        exit;
    }

    /**
     * Backup Database (Super Admin Only)
     */
    public function settingsBackupDatabase()
    {
        // Additional check for super admin (you can implement role-based check here)
        if (!Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. Super Admin privileges required.');
            header('Location: ' . SITE_URL . '/admin/settings?section=backup');
            exit;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $dbname = 'kids_bazaar'; // Get from config

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            // Get all tables
            $tables = [];
            $result = $db->query("SHOW TABLES");
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $output = "-- Database Backup\n";
            $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // Get table structure
                $output .= "-- Table structure for `$table`\n";
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                $createTable = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
                $output .= $createTable['Create Table'] . ";\n\n";

                // Get table data
                $rows = $db->query("SELECT * FROM `$table`");
                if ($rows->rowCount() > 0) {
                    $output .= "-- Data for table `$table`\n";
                    while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                        $values = [];
                        foreach ($row as $value) {
                            $values[] = $db->quote($value);
                        }
                        $output .= "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }

            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

            echo $output;
            exit;
        } catch (Exception $e) {
            Session::setFlash('error', 'Backup failed: ' . $e->getMessage());
            header('Location: ' . SITE_URL . '/admin/settings?section=backup');
            exit;
        }
    }

    /**
     * Quick Insights Page
     */
    public function quickInsights()
    {
        $oneWeekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        $oneMonthAgo = date('Y-m-d H:i:s', strtotime('-30 days'));

        // Get quick insights data
        $productsThisWeek = $this->productModel->query("SELECT COUNT(*) as total FROM products WHERE created_at >= ?", [$oneWeekAgo])->fetch()['total'] ?? 0;
        $ordersThisWeek = $this->orderModel->query("SELECT COUNT(*) as total FROM orders WHERE created_at >= ?", [$oneWeekAgo])->fetch()['total'] ?? 0;
        $revenueThisWeek = $this->orderModel->query("SELECT SUM(final_amount) as total FROM orders WHERE created_at >= ? AND order_status = ?", [$oneWeekAgo, ORDER_STATUS_DELIVERED])->fetch()['total'] ?? 0;
        $customersThisWeek = $this->userModel->query("SELECT COUNT(*) as total FROM users WHERE user_type = ? AND created_at >= ?", [USER_TYPE_CUSTOMER, $oneWeekAgo])->fetch()['total'] ?? 0;

        $productsThisMonth = $this->productModel->query("SELECT COUNT(*) as total FROM products WHERE created_at >= ?", [$oneMonthAgo])->fetch()['total'] ?? 0;
        $ordersThisMonth = $this->orderModel->query("SELECT COUNT(*) as total FROM orders WHERE created_at >= ?", [$oneMonthAgo])->fetch()['total'] ?? 0;
        $revenueThisMonth = $this->orderModel->query("SELECT SUM(final_amount) as total FROM orders WHERE created_at >= ? AND order_status = ?", [$oneMonthAgo, ORDER_STATUS_DELIVERED])->fetch()['total'] ?? 0;

        $pageTitle = 'Quick Insights';
        $this->render('admin/quick_insights', [
            'pageTitle' => $pageTitle,
            'productsThisWeek' => $productsThisWeek,
            'ordersThisWeek' => $ordersThisWeek,
            'revenueThisWeek' => $revenueThisWeek,
            'customersThisWeek' => $customersThisWeek,
            'productsThisMonth' => $productsThisMonth,
            'ordersThisMonth' => $ordersThisMonth,
            'revenueThisMonth' => $revenueThisMonth
        ]);
    }

    /**
     * Dashboard Analytics (Quick View)
     * Purpose: Fast decision-making, at-a-glance analytics
     * Shows: Summary charts (last 7/30 days), high-level KPIs, no filters, read-only
     */
    public function analytics()
    {
        // Quick view - only 7 and 30 day options, no advanced filters
        $days = isset($_GET['days']) ? (int) $_GET['days'] : 7;
        if (!in_array($days, [7, 30])) {
            $days = 7;
        }

        // Get revenue and orders data for quick charts
        $revenueData = $this->getRevenueTrend($days, []);
        $ordersData = $this->getOrdersPerDay($days);

        // Calculate quick KPIs
        $totalRevenue = array_sum(array_column($revenueData, 'revenue'));
        $totalOrders = array_sum(array_column($ordersData, 'count'));
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Conversion snapshot (orders vs revenue trend)
        $previousPeriodDays = $days;
        $previousPeriodStart = date('Y-m-d', strtotime("-" . ($days * 2) . " days"));
        $previousPeriodEnd = date('Y-m-d', strtotime("-" . $days . " days"));

        $previousRevenue = $this->orderModel->query("
            SELECT SUM(final_amount) as revenue 
            FROM orders 
            WHERE order_status = ? AND DATE(created_at) BETWEEN ? AND ?
        ", [ORDER_STATUS_DELIVERED, $previousPeriodStart, $previousPeriodEnd])->fetch()['revenue'] ?? 0;

        $revenueGrowth = $previousRevenue > 0 ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        $pageTitle = 'Analytics (Quick View)';
        $this->render('admin/analytics', [
            'pageTitle' => $pageTitle,
            'revenueData' => $revenueData,
            'ordersData' => $ordersData,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'revenueGrowth' => $revenueGrowth,
            'days' => $days
        ]);
    }

    /**
     * Revenue Analytics Page (Detailed Analysis)
     * Purpose: Detailed business analysis, financial reporting & auditing
     * Shows: Advanced filters, export options, drill-down tables, MoM/YoY comparisons
     */
    public function revenueAnalytics()
    {
        // Advanced filters
        $days = isset($_GET['days']) ? (int) $_GET['days'] : 30;
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
        $paymentMethod = $_GET['payment_method'] ?? '';

        // Build filter conditions
        $revenueFilters = [];
        if (!empty($dateFrom)) {
            $revenueFilters['revenue_date_from'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $revenueFilters['revenue_date_to'] = $dateTo;
        }

        // Get revenue data with filters
        $revenueData = $this->getRevenueTrend($days, $revenueFilters);

        // Build WHERE clause for additional filters (with alias for JOIN queries)
        $whereWithAlias = ["o.order_status = ?"];
        $params = [ORDER_STATUS_DELIVERED];

        // Build WHERE clause for simple queries (without alias)
        $whereWithoutAlias = ["order_status = ?"];
        $paramsSimple = [ORDER_STATUS_DELIVERED];

        if (!empty($dateFrom)) {
            $whereWithAlias[] = "DATE(o.created_at) >= ?";
            $whereWithoutAlias[] = "DATE(created_at) >= ?";
            $params[] = $dateFrom;
            $paramsSimple[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $whereWithAlias[] = "DATE(o.created_at) <= ?";
            $whereWithoutAlias[] = "DATE(created_at) <= ?";
            $params[] = $dateTo;
            $paramsSimple[] = $dateTo;
        }
        if ($categoryId > 0) {
            $whereWithAlias[] = "p.category_id = ?";
            $params[] = $categoryId;
        }
        if (!empty($paymentMethod)) {
            $whereWithAlias[] = "o.payment_method = ?";
            $whereWithoutAlias[] = "payment_method = ?";
            $params[] = $paymentMethod;
            $paramsSimple[] = $paymentMethod;
        }

        $whereClause = implode(' AND ', $whereWithAlias);
        $whereClauseSimple = implode(' AND ', $whereWithoutAlias);

        // Calculate totals
        $totalRevenue = array_sum(array_column($revenueData, 'revenue'));
        $averageDailyRevenue = count($revenueData) > 0 ? $totalRevenue / count($revenueData) : 0;

        // Get detailed top products by revenue with filters
        $topProductsQuery = "
            SELECT p.name, p.sku, SUM(oi.quantity * oi.price) as revenue, 
                   COUNT(DISTINCT o.order_id) as order_count,
                   SUM(oi.quantity) as total_quantity
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE {$whereClause}
            GROUP BY p.product_id, p.name, p.sku
            ORDER BY revenue DESC
            LIMIT 50
        ";
        $topProducts = $this->orderModel->query($topProductsQuery, $params)->fetchAll();

        // Month-over-Month comparison
        $currentMonthStart = date('Y-m-01');
        $currentMonthEnd = date('Y-m-t');
        $previousMonthStart = date('Y-m-01', strtotime('-1 month'));
        $previousMonthEnd = date('Y-m-t', strtotime('-1 month'));

        $currentMonthRevenue = $this->orderModel->query("
            SELECT SUM(final_amount) as revenue 
            FROM orders 
            WHERE order_status = ? AND DATE(created_at) BETWEEN ? AND ?
        ", [ORDER_STATUS_DELIVERED, $currentMonthStart, $currentMonthEnd])->fetch()['revenue'] ?? 0;

        $previousMonthRevenue = $this->orderModel->query("
            SELECT SUM(final_amount) as revenue 
            FROM orders 
            WHERE order_status = ? AND DATE(created_at) BETWEEN ? AND ?
        ", [ORDER_STATUS_DELIVERED, $previousMonthStart, $previousMonthEnd])->fetch()['revenue'] ?? 0;

        $momGrowth = $previousMonthRevenue > 0 ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 : 0;

        // Year-over-Year comparison
        $currentYearStart = date('Y-01-01');
        $currentYearEnd = date('Y-12-31');
        $previousYearStart = date('Y-01-01', strtotime('-1 year'));
        $previousYearEnd = date('Y-12-31', strtotime('-1 year'));

        $currentYearRevenue = $this->orderModel->query("
            SELECT SUM(final_amount) as revenue 
            FROM orders 
            WHERE order_status = ? AND DATE(created_at) BETWEEN ? AND ?
        ", [ORDER_STATUS_DELIVERED, $currentYearStart, $currentYearEnd])->fetch()['revenue'] ?? 0;

        $previousYearRevenue = $this->orderModel->query("
            SELECT SUM(final_amount) as revenue 
            FROM orders 
            WHERE order_status = ? AND DATE(created_at) BETWEEN ? AND ?
        ", [ORDER_STATUS_DELIVERED, $previousYearStart, $previousYearEnd])->fetch()['revenue'] ?? 0;

        $yoyGrowth = $previousYearRevenue > 0 ? (($currentYearRevenue - $previousYearRevenue) / $previousYearRevenue) * 100 : 0;

        // Revenue by payment method
        $revenueByPaymentMethod = $this->orderModel->query("
            SELECT payment_method, SUM(final_amount) as revenue, COUNT(*) as order_count
            FROM orders
            WHERE {$whereClauseSimple}
            GROUP BY payment_method
            ORDER BY revenue DESC
        ", $paramsSimple)->fetchAll();

        // Revenue by category
        $revenueByCategory = $this->orderModel->query("
            SELECT c.name as category_name, SUM(oi.quantity * oi.price) as revenue, COUNT(DISTINCT o.order_id) as order_count
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            JOIN products p ON oi.product_id = p.product_id
            JOIN categories c ON p.category_id = c.category_id
            WHERE {$whereClause}
            GROUP BY c.category_id, c.name
            ORDER BY revenue DESC
            LIMIT 20
        ", $params)->fetchAll();

        // Get all categories for filter dropdown
        $categories = $this->categoryModel->getAll(true);

        $pageTitle = 'Revenue Analytics (Detailed Report)';
        $this->render('admin/revenue_analytics', [
            'pageTitle' => $pageTitle,
            'revenueData' => $revenueData,
            'totalRevenue' => $totalRevenue,
            'averageDailyRevenue' => $averageDailyRevenue,
            'topProducts' => $topProducts,
            'days' => $days,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'categoryId' => $categoryId,
            'paymentMethod' => $paymentMethod,
            'categories' => $categories,
            'currentMonthRevenue' => $currentMonthRevenue,
            'previousMonthRevenue' => $previousMonthRevenue,
            'momGrowth' => $momGrowth,
            'currentYearRevenue' => $currentYearRevenue,
            'previousYearRevenue' => $previousYearRevenue,
            'yoyGrowth' => $yoyGrowth,
            'revenueByPaymentMethod' => $revenueByPaymentMethod,
            'revenueByCategory' => $revenueByCategory
        ]);
    }

    /**
     * Export Revenue Analytics to CSV
     */
    public function revenueAnalyticsExport()
    {
        $format = $_GET['format'] ?? 'csv';
        $days = isset($_GET['days']) ? (int) $_GET['days'] : 30;
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
        $paymentMethod = $_GET['payment_method'] ?? '';

        // Build filter conditions
        if ($categoryId > 0) {
            // If category filter is applied, need JOIN with products
            $whereWithAlias = ["o.order_status = ?"];
            $params = [ORDER_STATUS_DELIVERED];

            if (!empty($dateFrom)) {
                $whereWithAlias[] = "DATE(o.created_at) >= ?";
                $params[] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $whereWithAlias[] = "DATE(o.created_at) <= ?";
                $params[] = $dateTo;
            }
            $whereWithAlias[] = "p.category_id = ?";
            $params[] = $categoryId;
            if (!empty($paymentMethod)) {
                $whereWithAlias[] = "o.payment_method = ?";
                $params[] = $paymentMethod;
            }

            $whereClause = implode(' AND ', $whereWithAlias);

            // Get detailed revenue data with category filter
            $revenueData = $this->orderModel->query("
                SELECT DATE(o.created_at) as date, 
                       COUNT(DISTINCT o.order_id) as order_count,
                       SUM(o.final_amount) as revenue,
                       AVG(o.final_amount) as avg_order_value
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE {$whereClause}
                GROUP BY DATE(o.created_at)
                ORDER BY date ASC
            ", $params)->fetchAll();
        } else {
            // No category filter - simple query
            $whereWithoutAlias = ["order_status = ?"];
            $paramsSimple = [ORDER_STATUS_DELIVERED];

            if (!empty($dateFrom)) {
                $whereWithoutAlias[] = "DATE(created_at) >= ?";
                $paramsSimple[] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $whereWithoutAlias[] = "DATE(created_at) <= ?";
                $paramsSimple[] = $dateTo;
            }
            if (!empty($paymentMethod)) {
                $whereWithoutAlias[] = "payment_method = ?";
                $paramsSimple[] = $paymentMethod;
            }

            $whereClauseSimple = implode(' AND ', $whereWithoutAlias);

            // Get detailed revenue data
            $revenueData = $this->orderModel->query("
                SELECT DATE(created_at) as date, 
                       COUNT(DISTINCT order_id) as order_count,
                       SUM(final_amount) as revenue,
                       AVG(final_amount) as avg_order_value
                FROM orders
                WHERE {$whereClauseSimple}
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ", $paramsSimple)->fetchAll();
        }

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="revenue_analytics_' . date('Y-m-d') . '.csv"');

            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8 Excel compatibility
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers
            fputcsv($output, ['Date', 'Orders', 'Revenue', 'Average Order Value']);

            // Data rows
            foreach ($revenueData as $row) {
                fputcsv($output, [
                    $row['date'],
                    $row['order_count'],
                    number_format($row['revenue'], 2),
                    number_format($row['avg_order_value'], 2)
                ]);
            }

            fclose($output);
            exit;
        }

        // PDF export would require a library like TCPDF or FPDF
        // For now, redirect back with error
        Session::setFlash('error', 'PDF export not yet implemented. Please use CSV export.');
        header('Location: ' . SITE_URL . '/admin/revenue-analytics?' . http_build_query($_GET));
        exit;
    }

    /**
     * Recent Orders Page
     */
    public function recentOrders()
    {
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
        $status = $_GET['status'] ?? '';

        $where = [];
        $params = [];

        if (!empty($status)) {
            $where[] = "o.order_status = ?";
            $params[] = $status;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT o.*, 
                COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'Guest') as customer_name,
                u.email as customer_email,
                u.phone
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                {$whereClause} 
                ORDER BY o.created_at DESC 
                LIMIT ?";
        $params[] = $limit;

        $recentOrders = $this->orderModel->query($sql, $params)->fetchAll();

        $pageTitle = 'Recent Orders';
        $this->render('admin/recent_orders', [
            'pageTitle' => $pageTitle,
            'recentOrders' => $recentOrders,
            'limit' => $limit,
            'status' => $status
        ]);
    }

    /**
     * Manage Customers Page
     */
    public function customers()
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $search = Validator::sanitize($_GET['search'] ?? '');
        $status = Validator::sanitize($_GET['status'] ?? '');
        $customerId = isset($_GET['customer_id']) ? (int) $_GET['customer_id'] : null;

        $sql = "SELECT *, TRIM(CONCAT(IFNULL(first_name, ''), ' ', IFNULL(last_name, ''))) as name FROM users WHERE user_type = ?";
        $params = [USER_TYPE_CUSTOMER];

        // If specific customer requested, prioritize/filter them
        // We'll still show others, but we might want to ensure they are on the first page or just show one.
        // For deep linking, usually showing just that one or including it in the search is best.
        if ($customerId) {
            $sql .= " AND user_id = ?";
            $params[] = $customerId;
        }

        if (!empty($status)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY created_at DESC";

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM users WHERE user_type = ?";
        $countParams = [USER_TYPE_CUSTOMER];

        if (!empty($status)) {
            $countSql .= " AND status = ?";
            $countParams[] = $status;
        }

        if (!empty($search)) {
            $countSql .= " AND (first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
        }

        if ($customerId) {
            $countSql .= " AND user_id = ?";
            $countParams[] = $customerId;
        }
        $countStmt = $this->userModel->query($countSql, $countParams);
        $total = $countStmt->fetch()['total'] ?? 0;

        // Add pagination
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $customers = $this->userModel->query($sql, $params)->fetchAll();

        // Get order stats for each customer
        foreach ($customers as &$customer) {
            $stats = $this->orderModel->query("SELECT COUNT(*) as total, SUM(total_amount) as total_spend, MAX(created_at) as last_order FROM orders WHERE user_id = ?", [$customer['user_id']])->fetch();
            $customer['order_count'] = $stats['total'] ?? 0;
            $customer['total_spend'] = $stats['total_spend'] ?? 0;
            $customer['last_order_date'] = $stats['last_order'] ?? null;
        }

        $pageTitle = 'Manage Customers';
        $this->render('admin/customers', [
            'pageTitle' => $pageTitle,
            'customers' => $customers,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status
        ]);
    }

    /**
     * Customer Groups Management
     */
    public function customersGroups()
    {
        // For now, show a placeholder page
        // This can be enhanced later with a customer groups system
        $pageTitle = 'Customer Groups';
        $this->render('admin/customers/groups', [
            'pageTitle' => $pageTitle
        ]);
    }

    /**
     * Customer Reviews & Feedback
     */
    public function customersReviews()
    {
        require_once APP_PATH . '/models/Review.php';
        $reviewModel = new Review();

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $search = Validator::sanitize($_GET['search'] ?? '');

        $sql = "SELECT r.*, p.name as product_name, p.sku, p.slug as product_slug,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name, u.email as customer_email,
                u.user_id
                FROM reviews r
                JOIN products p ON r.product_id = p.product_id
                JOIN users u ON r.user_id = u.user_id
                WHERE u.user_type = ?";

        $params = [USER_TYPE_CUSTOMER];

        if (!empty($search)) {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR p.name LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY r.created_at DESC";

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM reviews r
                     JOIN products p ON r.product_id = p.product_id
                     JOIN users u ON r.user_id = u.user_id
                     WHERE u.user_type = ?";
        $countParams = [USER_TYPE_CUSTOMER];
        if (!empty($search)) {
            $countSql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR p.name LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
            $countParams[] = $searchTerm;
        }
        $countStmt = $reviewModel->query($countSql, $countParams);
        $total = $countStmt->fetch()['total'] ?? 0;

        // Add pagination
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $reviewModel->query($sql, $params);
        $reviews = $stmt->fetchAll();

        require_once APP_PATH . '/helpers/Pagination.php';
        $pagination = new Pagination($total, $perPage, $page);

        $pageTitle = 'Customer Reviews & Feedback';
        $this->render('admin/customers/reviews', [
            'pageTitle' => $pageTitle,
            'reviews' => $reviews,
            'pagination' => $pagination,
            'search' => $search
        ]);
    }

    /**
     * Reports / Analytics Page
     */
    public function reports()
    {
        $days = isset($_GET['days']) ? (int) $_GET['days'] : 30;
        $reportType = isset($_GET['type']) ? $_GET['type'] : 'sales'; // sales, product, customer

        $pageTitle = 'Reports / Analytics';
        $data = [
            'pageTitle' => $pageTitle,
            'days' => $days,
            'reportType' => $reportType
        ];

        if ($reportType === 'sales') {
            // Sales report
            $salesData = $this->orderModel->query("
                SELECT DATE(created_at) as date, COUNT(*) as order_count, SUM(final_amount) as revenue
                FROM orders
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND order_status = ?
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ", [$days, ORDER_STATUS_DELIVERED])->fetchAll();

            // Order status distribution
            $statusDistribution = $this->orderModel->query("
                SELECT order_status, COUNT(*) as count
                FROM orders
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY order_status
            ", [$days])->fetchAll();

            $data['salesData'] = $salesData;
            $data['statusDistribution'] = $statusDistribution;
            $this->render('admin/reports/sales', $data);

        } elseif ($reportType === 'product') {
            // Product performance
            $productPerformance = $this->orderModel->query("
                SELECT p.product_id, p.name, p.sku, p.slug, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                JOIN products p ON oi.product_id = p.product_id
                WHERE o.order_status = ? AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY p.product_id, p.name, p.sku
                ORDER BY total_sold DESC
                LIMIT 20
            ", [ORDER_STATUS_DELIVERED, $days])->fetchAll();

            $data['productPerformance'] = $productPerformance;
            $this->render('admin/reports/product', $data);

        } elseif ($reportType === 'customer') {
            // Customer reports - comprehensive data
            $filters = [
                'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : null,
                'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : null,
                'customer_type' => isset($_GET['customer_type']) ? $_GET['customer_type'] : null,
                'status' => isset($_GET['status']) ? $_GET['status'] : null,
                'search' => isset($_GET['search']) ? $_GET['search'] : null
            ];

            // If no custom date range, use days filter
            if (empty($filters['date_from']) && empty($filters['date_to'])) {
                $filters['date_from'] = date('Y-m-d', strtotime("-{$days} days"));
                $filters['date_to'] = date('Y-m-d');
            }

            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $perPage = 20;

            // Get all customer report data
            try {
                $summaryMetrics = $this->userModel->getCustomerSummaryMetrics($days);
                $customerGrowth = $this->userModel->getCustomerGrowth($filters);
                $customerSegmentation = $this->userModel->getCustomerSegmentation($days);
                $customerValueMetrics = $this->userModel->getCustomerValueMetrics($days);
                $retentionMetrics = $this->userModel->getRetentionMetrics($filters);
                $retentionTrend = $this->userModel->getRetentionTrend($filters);
                $topCustomers = $this->userModel->getTopCustomers(20, $days);
                $customersList = $this->userModel->getCustomersWithFilters($filters, $page, $perPage);
                $totalCustomers = $this->userModel->getCustomersCountWithFilters($filters);
                
                // Ensure arrays are not null
                $summaryMetrics = $summaryMetrics ?? ['total_customers' => 0, 'new_customers' => 0, 'active_customers' => 0, 'inactive_customers' => 0, 'customers_with_orders' => 0, 'returning_customers' => 0];
                $customerGrowth = $customerGrowth ?? [];
                $customerSegmentation = $customerSegmentation ?? [];
                $customerValueMetrics = $customerValueMetrics ?? [];
                $retentionMetrics = $retentionMetrics ?? ['one_time_buyers' => 0, 'repeat_buyers' => 0, 'total_customers' => 0];
                $retentionTrend = $retentionTrend ?? [];
                $topCustomers = $topCustomers ?? [];
                $customersList = $customersList ?? [];
            } catch (Exception $e) {
                error_log("Error fetching customer reports: " . $e->getMessage());
                // Set defaults on error
                $summaryMetrics = ['total_customers' => 0, 'new_customers' => 0, 'active_customers' => 0, 'inactive_customers' => 0, 'customers_with_orders' => 0, 'returning_customers' => 0];
                $customerGrowth = [];
                $customerSegmentation = [];
                $customerValueMetrics = [];
                $retentionMetrics = ['one_time_buyers' => 0, 'repeat_buyers' => 0, 'total_customers' => 0];
                $retentionTrend = [];
                $topCustomers = [];
                $customersList = [];
                $totalCustomers = 0;
            }

            // Calculate retention rate
            $retentionRate = 0;
            if (($retentionMetrics['total_customers'] ?? 0) > 0) {
                $retentionRate = (($retentionMetrics['repeat_buyers'] ?? 0) / $retentionMetrics['total_customers']) * 100;
            }

            // Calculate average AOV and CLV
            $avgAOV = 0;
            $avgCLV = 0;
            if (!empty($customerValueMetrics)) {
                $totalAOV = array_sum(array_column($customerValueMetrics, 'avg_order_value'));
                $totalCLV = array_sum(array_column($customerValueMetrics, 'customer_lifetime_value'));
                $count = count($customerValueMetrics);
                $avgAOV = $count > 0 ? $totalAOV / $count : 0;
                $avgCLV = $count > 0 ? $totalCLV / $count : 0;
            }

            $data = array_merge($data, [
                'summaryMetrics' => $summaryMetrics,
                'customerGrowth' => $customerGrowth,
                'customerSegmentation' => $customerSegmentation,
                'customerValueMetrics' => $customerValueMetrics,
                'retentionMetrics' => $retentionMetrics,
                'retentionTrend' => $retentionTrend,
                'retentionRate' => $retentionRate,
                'avgAOV' => $avgAOV,
                'avgCLV' => $avgCLV,
                'topCustomers' => $topCustomers,
                'customersList' => $customersList,
                'totalCustomers' => $totalCustomers,
                'currentPage' => $page,
                'perPage' => $perPage,
                'filters' => $filters
            ]);

            $this->render('admin/reports/customer', $data);

        } else {
            // Default to sales report
            $salesData = $this->orderModel->query("
                SELECT DATE(created_at) as date, COUNT(*) as order_count, SUM(final_amount) as revenue
                FROM orders
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND order_status = ?
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ", [$days, ORDER_STATUS_DELIVERED])->fetchAll();

            $data['salesData'] = $salesData;
            $this->render('admin/reports/sales', $data);
        }
    }

    /**
     * Coupons Management
     */
    public function coupons()
    {
        // For now, show a placeholder page
        // This can be enhanced later with a coupon management system
        $pageTitle = 'Coupons Management';
        $this->render('admin/marketing/coupons', [
            'pageTitle' => $pageTitle
        ]);
    }

    /**
     * Discounts Management
     */
    public function discounts()
    {
        // For now, show a placeholder page
        // This can be enhanced later with a discount management system
        $pageTitle = 'Discounts Management';
        $this->render('admin/marketing/discounts', [
            'pageTitle' => $pageTitle
        ]);
    }

    /**
     * Campaign Analytics
     */
    public function campaigns()
    {
        // For now, show a placeholder page
        // This can be enhanced later with campaign analytics
        $pageTitle = 'Campaign Analytics';
        $this->render('admin/marketing/campaigns', [
            'pageTitle' => $pageTitle
        ]);
    }



    /**
     * Update customer status
     */
    public function updateCustomerStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $userId = $_POST['user_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $reason = $_POST['reason'] ?? null;
        $reasonCode = $_POST['reason_code'] ?? null; // New field
        $evidence = $_POST['evidence'] ?? null; // New field
        $duration = $_POST['duration'] ?? null; // in days

        if (!$userId || !$status) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Validate status
        $allowedStatuses = [USER_STATUS_ACTIVE, USER_STATUS_SUSPENDED, USER_STATUS_DEACTIVATED];
        if (!in_array($status, $allowedStatuses)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }

        // Get current user data for old status
        $currentUser = $this->userModel->find($userId);
        if (!$currentUser) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        $oldStatus = $currentUser['status'];

        $updateData = ['status' => $status];

        if ($status === USER_STATUS_SUSPENDED) {
            // Require reason for suspension
            if (empty($reason)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Suspension reason is required']);
                exit;
            }
            
            $updateData['suspension_reason'] = $reason;
            if ($duration) {
                $updateData['suspension_end_date'] = date('Y-m-d H:i:s', strtotime("+$duration days"));
            } else {
                $updateData['suspension_end_date'] = null; // Indefinite
            }
        } elseif ($status === USER_STATUS_ACTIVE || $status === USER_STATUS_DEACTIVATED) {
            $updateData['suspension_reason'] = null;
            $updateData['suspension_end_date'] = null;
        }

        if ($this->userModel->update($userId, $updateData)) {
            // Record status history
            // Only if status changed or it's a suspension update (to record new notes/evidence)
            $historyAdded = false;
            if ($oldStatus !== $status || $status === USER_STATUS_SUSPENDED) {
                $historyAdded = $this->userModel->addStatusHistory([
                    'user_id' => $userId,
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'reason_code' => $reasonCode,
                    'reason_text' => $reason,
                    'suspended_by_admin_id' => Session::getUserId(),
                    'evidence_reference' => $evidence
                ]);
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Customer status updated successfully',
                'debug_history_added' => $historyAdded,
                'debug_old_status' => $oldStatus,
                'debug_new_status' => $status
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to update customer status']);
        }
        exit;
    }

    /**
     * Get Customer History (AJAX)
     */
    public function customerHistory()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $userId = $_GET['user_id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing user ID']);
            exit;
        }

        $history = $this->userModel->getStatusHistory($userId);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'history' => $history]);
        exit;
    }

    /**
     * Support Ticket Management Routing
     */
    public function support($action = 'index', $id = null)
    {
        require_once APP_PATH . '/controllers/admin/SupportController.php';
        $controller = new AdminSupportController();
        
        if (method_exists($controller, $action)) {
            $controller->$action($id);
        } else {
            $controller->index();
        }
    }

    /**
     * Notification List Page
     * Route: /admin/notifications
     */
    public function notifications()
    {
        require_once APP_PATH . '/models/Notification.php';
        $notificationModel = new Notification();
        
        $userId = Session::getUserId();
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Filters
        $filters = [];
        if (isset($_GET['type']) && !empty($_GET['type'])) {
            $filters['type'] = $_GET['type'];
        }

        // Get notifications
        $notifications = $notificationModel->getUserNotifications($userId, $perPage, $offset, $filters);
        
        // Get total count for pagination
        $totalNotifications = $notificationModel->countUserNotifications($userId, $filters);
        
        require_once APP_PATH . '/helpers/Pagination.php';
        $pagination = new Pagination($totalNotifications, $perPage, $page);

        $pageTitle = 'Notifications';
        
        // Render view
        extract([
            'pageTitle' => $pageTitle,
            'notifications' => $notifications,
            'pagination' => $pagination,
            'currentFilter' => $filters['type'] ?? 'all'
        ]);
        
        require_once APP_PATH . '/views/layouts/admin_header.php';
        require_once APP_PATH . '/views/admin/notifications/index.php';
        require_once APP_PATH . '/views/layouts/admin_footer.php';
    }

    /**
     * Get latest notifications (AJAX)
     * Route: /admin/notifications/latest
     */
    public function notificationsLatest()
    {
        header('Content-Type: application/json');
        
        try {
            require_once APP_PATH . '/models/Notification.php';
            $notificationModel = new Notification();
            
            $userId = Session::getUserId();
            $notifications = $notificationModel->getUserNotifications($userId, 5);
            $unreadCount = $notificationModel->getUnreadCount($userId);

            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark notification as read (AJAX)
     * Route: /admin/notifications/mark-read
     */
    public function notificationsMarkRead()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        require_once APP_PATH . '/models/Notification.php';
        $notificationModel = new Notification();

        $userId = Session::getUserId();
        $data = json_decode(file_get_contents('php://input'), true);
        $notificationId = $data['id'] ?? null;

        if (!$notificationId) {
            echo json_encode(['error' => 'Missing notification ID']);
            return;
        }

        try {
            $notificationModel->markAsRead($notificationId, $userId);
            $unreadCount = $notificationModel->getUnreadCount($userId);
            
            echo json_encode([
                'success' => true, 
                'unread_count' => $unreadCount
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark all as read (AJAX)
     * Route: /admin/notifications/mark-all-read
     */
    public function notificationsMarkAllRead()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        require_once APP_PATH . '/models/Notification.php';
        $notificationModel = new Notification();

        $userId = Session::getUserId();

        try {
            $notificationModel->markAllAsRead($userId);
            
            echo json_encode([
                'success' => true,
                'unread_count' => 0
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get Customer Details (AJAX)
     * Used for modal in various admin pages (Customers, Notifications)
     * Route: /admin/customer-details/{id}
     */
    public function customerDetails($id)
    {
        header('Content-Type: application/json');

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing ID']);
            return;
        }

        require_once APP_PATH . '/models/User.php';
        require_once APP_PATH . '/models/Order.php';

        $userModel = new User();
        $orderModel = new Order();

        $customer = $userModel->find($id);

        if (!$customer) {
            http_response_code(404);
            echo json_encode(['error' => 'Customer not found']);
            return;
        }

        // Construct full name if not present
        if (!isset($customer['name'])) {
            $customer['name'] = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
        }

        // Augment with stats
        $stats = $userModel->query("
            SELECT 
                COUNT(*) as order_count,
                SUM(final_amount) as total_spend,
                MAX(created_at) as last_order_date
            FROM orders 
            WHERE user_id = ?
        ", [$id])->fetch();

        $customer['order_count'] = $stats['order_count'] ?? 0;
        $customer['total_spend'] = $stats['total_spend'] ?? 0;
        $customer['last_order_date'] = $stats['last_order_date'] ?? null;

        // Ensure sensitive data is not sent or is sanitized if strictly needed
        // (password is already hashed, but good practice to unset)
        unset($customer['password']);
        unset($customer['reset_token']);

        echo json_encode(['success' => true, 'customer' => $customer]);
    }

    /**
     * Delete notification
     * Route: /admin/notifications/delete/{id}
     */
    public function notificationsDelete($id)
    {
        require_once APP_PATH . '/models/Notification.php';
        $notificationModel = new Notification();
        
        $userId = Session::getUserId();
        $notificationModel->deleteNotification($id, $userId);
        
        // Redirect back
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    /**
     * Delete all notifications (AJAX)
     * Route: /admin/notifications/delete-all
     */
    public function notificationsDeleteAll()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        require_once APP_PATH . '/models/Notification.php';
        $notificationModel = new Notification();
        
        $userId = Session::getUserId();

        try {
            $notificationModel->clearAll($userId);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle notification click (Mark as read & Redirect)
     * Route: /admin/notifications/click/{id}
     */
    public function notificationsClick($id = null)
    {
        if (!$id) {
            header('Location: ' . SITE_URL . '/admin/notifications');
            exit;
        }

        require_once APP_PATH . '/models/Notification.php';
        $notificationModel = new Notification();
        
        $userId = Session::getUserId();
        
        // Find notification to ensure ownership and get link
        // We can use a simple query or existing method if available
        // For now, let's just mark it read and assume it exists/belongs to user to be safe, 
        // but ideally we should fetch it first.
        
        // Fetch specific notification (we need a findOne method or similar in model, or use query)
        $notification = $notificationModel->query(
            "SELECT * FROM notifications WHERE notification_id = ? AND user_id = ?", 
            [$id, $userId]
        )->fetch();
        
        if ($notification) {
            // Mark as read
            $notificationModel->markAsRead($id, $userId);

            // Determine redirection target
            $redirectUrl = SITE_URL . '/admin/notifications'; // Default fallback

            // 1. Order Notifications
            if ($notification['type'] === 'order') {
                if (!empty($notification['related_id'])) {
                     $redirectUrl = SITE_URL . '/admin/orders/' . $notification['related_id'];
                } elseif (!empty($notification['link'])) {
                    $redirectUrl = SITE_URL . $notification['link']; // Fallback to link if related_id missing
                } else {
                    $redirectUrl = SITE_URL . '/admin/orders';
                }
            } 
            // 2. User/Customer Notifications
            elseif ($notification['type'] === 'user') {
                if (!empty($notification['related_id'])) {
                    // Redirect to customers page with ID param to trigger modal/highlight
                    $redirectUrl = SITE_URL . '/admin/customers?customer_id=' . $notification['related_id'];
                } else {
                    $redirectUrl = SITE_URL . '/admin/customers';
                }
            }
            // 3. Product Notifications
            elseif ($notification['type'] === 'product') {
                if (!empty($notification['related_id'])) {
                    // Check if it's a review event
                    if ($notification['event_name'] === 'review_submitted') {
                        $redirectUrl = SITE_URL . '/admin/products/reviews/view/' . $notification['related_id'];
                    } else {
                        $redirectUrl = SITE_URL . '/admin/product/edit/' . $notification['related_id'];
                    }
                } else {
                    $redirectUrl = SITE_URL . '/admin/products';
                }
            }
            // 4. System Notifications
            elseif ($notification['type'] === 'system') {
                 // Explicit check for Support Tickets
                if ($notification['event_name'] === 'ticket_created' && !empty($notification['related_id'])) {
                     $redirectUrl = SITE_URL . '/admin/support/view/' . $notification['related_id'];
                }
                 // If generic link exists and is NOT just the list itself, use it
                elseif (!empty($notification['link']) && 
                    $notification['link'] !== '#' && 
                    $notification['link'] !== '/admin/notifications' &&
                    $notification['link'] !== '/admin/settings') {
                     $redirectUrl = SITE_URL . $notification['link'];
                } else {
                     // Fallback to detail view to see full message
                     $redirectUrl = SITE_URL . '/admin/notifications/view/' . $id;
                }
            }
            // 5. Explicit Link Fallback (for legacy or custom types)
            elseif (!empty($notification['link']) && $notification['link'] !== '#') {
                $redirectUrl = SITE_URL . $notification['link'];
            }

            // Fix common path issues in legacy links (just in case)
            if (strpos($redirectUrl, '/admin/orders/detail/') !== false) {
                $redirectUrl = str_replace('/admin/orders/detail/', '/admin/orders/', $redirectUrl);
            }
            if (strpos($redirectUrl, '/admin/users') !== false) {
                 $redirectUrl = str_replace('/admin/users', '/admin/customers', $redirectUrl);
            }

            header('Location: ' . $redirectUrl);
            exit;
        }
        
        // Default redirect if not found or no link
        header('Location: ' . SITE_URL . '/admin/notifications');
        exit;
    }

    /**
     * View Single Notification (Fallback for items without links)
     */
    public function notificationsView($id)
    {
        require_once APP_PATH . '/models/Notification.php';
        $notificationModel = new Notification();
        $notification = $notificationModel->find($id);

        if (!$notification) {
            header('Location: ' . SITE_URL . '/admin/notifications');
            exit;
        }

        $this->render('admin/notifications/view', [
            'pageTitle' => 'Notification Details',
            'notification' => $notification
        ]);
    }

    /**
     * Render view
     */
    protected function render($view, $data = [])
    {
        // extract($data);
        extract($data, EXTR_SKIP);

        require_once VIEW_PATH . '/layouts/admin_header.php';
        require_once VIEW_PATH . '/' . $view . '.php';
        require_once VIEW_PATH . '/layouts/admin_footer.php';
    }
}

