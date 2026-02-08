<?php
/**
 * Product Controller
 * Handles product listing, detail, and search
 */

class ProductController
{
    private $productModel;
    private $reviewModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->reviewModel = new Review();
    }

    /**
     * Product listing
     */
    public function index()
    {
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

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
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
    public function detail($slug)
    {
        $product = $this->productModel->findBySlug($slug);

        if (!$product) {
            http_response_code(404);
            require_once VIEW_PATH . '/errors/404.php';
            return;
        }

        $images = $this->productModel->getImages($product['product_id']);
        $variants = $this->productModel->getVariants($product['product_id'], true);
        $reviews = $this->reviewModel->getProductReviews($product['product_id'], ['limit' => 5]);
        $rating = $this->reviewModel->getProductRatingStats($product['product_id']);

        // Check review eligibility
        $reviewEligibility = ['eligible' => false];
        if (Session::isLoggedIn()) {
            $reviewEligibility = $this->reviewModel->checkEligibility(Session::getUserId(), $product['product_id']);
        }

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
        uasort($groups, function ($a, $b) {
            if ($a['display_order'] == $b['display_order']) {
                return strcmp($a['group_name'], $b['group_name']);
            }
            return $a['display_order'] <=> $b['display_order'];
        });

        // Sort attributes within each group by display_order
        foreach ($attributesByGroup as $groupId => &$attrs) {
            usort($attrs, function ($a, $b) {
                return ($a['display_order'] ?? 0) <=> ($b['display_order'] ?? 0);
            });
        }
        unset($attrs);

        // Fetch related products
        $relatedProducts = [];
        if (!empty($product['category_slug'])) {
            // Fetch a few more to account for filtering current product
            $related = $this->productModel->getProducts(['category' => $product['category_slug']], 1, 5);
            foreach ($related as $p) {
                if ($p['product_id'] != $product['product_id']) {
                    $relatedProducts[] = $p;
                }
                if (count($relatedProducts) >= 4)
                    break;
            }
        }


        $this->render('products/detail', [
            'product' => $product,
            'images' => $images,
            'variants' => $variants,
            'reviews' => $reviews,
            'rating' => $rating,
            'reviewEligibility' => $reviewEligibility,
            'relatedProducts' => $relatedProducts,
            'productAttributes' => $allAttributes, // Keep flat list for backward compatibility
            'attributesByGroup' => $attributesByGroup, // New: organized by groups
            'attributeGroups' => $groups // Group metadata
        ]);
    }

    /**
     * Search products
     */
    public function search()
    {
        $query = Validator::sanitize($_GET['q'] ?? '');
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

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
    private function getTotalProducts($filters)
    {
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
     * Handle review submission
     */
    public function submit_review()
    {
        // Detect AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            http_response_code(405);
            return;
        }

        if (!Session::isLoggedIn()) {
            if ($isAjax) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a review.']);
                exit;
            }
            Session::setFlash('error', 'You must be logged in to submit a review.');
            header('Location: ' . SITE_URL . '/login');
            exit;
        }

        $userId = Session::getUserId();
        $productId = (int) $_POST['product_id'];

        // Prevent submission if not eligible (backend check)
        $eligibility = $this->reviewModel->checkEligibility($userId, $productId);

        // Strict Check: Must be eligible (Delivered order exists, not already reviewed)
        if (!$eligibility['eligible']) {
            $msg = 'You are not eligible to review this product. ' .
                ($eligibility['reason'] === 'already_reviewed' ? 'You have already reviewed it.' : 'Purchase and delivery required.');

            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }

            Session::setFlash('error', $msg);
            header('Location: ' . SITE_URL . '/product/' . $this->productModel->find($productId)['slug']);
            exit;
        }

        // Double check the submitted order_id if valid
        $submittedOrderId = $_POST['order_id'] ?? null;
        if ($submittedOrderId && $submittedOrderId != $eligibility['order_id']) {
            $submittedOrderId = $eligibility['order_id'];
        }

        // Ensure we have a valid order ID to attach
        if (!$submittedOrderId) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'No valid order found for this review.']);
                exit;
            }
            Session::setFlash('error', 'No valid order found for this review.');
            header('Location: ' . SITE_URL . '/product/' . $this->productModel->find($productId)['slug']);
            exit;
        }

        // Handle Image Uploads
        $media = [];
        if (!empty($_FILES['review_images']['name'][0])) {
            $uploadDir = UPLOAD_PATH . '/reviews/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['review_images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['review_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = uniqid() . '_' . basename($_FILES['review_images']['name'][$key]);
                    $targetPath = $uploadDir . $fileName;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $media[] = '/assets/uploads/reviews/' . $fileName;
                    }
                }
            }
        }

        $data = [
            'product_id' => $productId,
            'user_id' => $userId,
            'order_id' => $submittedOrderId, // Use strict validated ID
            'rating' => (int) $_POST['rating'],
            'title' => $_POST['title'] ?? '',
            'review_text' => $_POST['review_text'],
            'media' => $media,
            'is_verified_purchase' => isset($_POST['is_verified_purchase']) ? 1 : 0
        ];

        try {
            $this->reviewModel->submitReview($data);

            if ($isAjax) {
                echo json_encode(['success' => true, 'message' => 'Review submitted successfully! It will be visible after approval.']);
                exit;
            }

            Session::setFlash('success', 'Review submitted successfully! It will be visible after approval.');
        } catch (Exception $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Failed to submit review.']);
                exit;
            }
            Session::setFlash('error', 'Failed to submit review.');
        }

        // Redirect back to product page
        $slug = $this->productModel->find($productId)['slug'];
        header('Location: ' . SITE_URL . '/product/' . $slug);
        exit;
    }

    /**
     * Handle review reporting (AJAX)
     */
    public function report_review()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Login required']);
            return;
        }

        $reviewId = (int) $_POST['review_id'];
        $reason = $_POST['reason'] ?? 'Inappropriate content';
        $userId = Session::getUserId();

        try {
            $result = $this->reviewModel->reportReview($reviewId, $userId, $reason);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Already reported']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }

    /**
     * Render view
     */
    private function render($view, $data = [])
    {
        extract($data);
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/' . $view . '.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }
    /**
     * API: Check Review Eligibility
     */
    public function check_eligibility()
    {
        header('Content-Type: application/json');

        // Check Login
        if (!Session::isLoggedIn()) {
            echo json_encode([
                'logged_in' => false,
                'purchased' => false,
                'delivered' => false,
                'eligible' => false,
                'order_status' => null
            ]);
            exit;
        }

        $userId = Session::getUserId();
        $productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

        if (!$productId) {
            echo json_encode(['error' => 'Product ID required']);
            exit;
        }

        // Get detailed status from Model
        $status = $this->reviewModel->checkDetailedEligibility($userId, $productId);

        $response = [
            'logged_in' => true,
            'purchased' => false, // Default unsafe
            'delivered' => false, // Default unsafe
            'eligible' => false,  // Default unsafe
            'order_id' => $status['order_id'] ?? null,
            'order_status' => $status['current_status'] ?? null // Critical for frontend msg
        ];

        if ($status['status'] === 'eligible') {
            $response['purchased'] = true;
            $response['delivered'] = true;
            $response['eligible'] = true;
        } elseif ($status['status'] === 'not_purchased') {
            $response['purchased'] = false;
            $response['delivered'] = false;
        } elseif ($status['status'] === 'not_delivered') {
            $response['purchased'] = true;
            $response['delivered'] = false;
        } elseif ($status['status'] === 'already_reviewed') {
            $response['purchased'] = true;
            $response['delivered'] = true; // Assuming past delivery
            $response['eligible'] = false;
            $response['already_reviewed'] = true;
        }

        echo json_encode($response);
        exit;
    }
}

