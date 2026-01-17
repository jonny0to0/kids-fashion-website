<?php
/**
 * Reviews Controller
 * Handles public review submission and management
 */

class ReviewsController extends Controller
{
    private $reviewModel;
    private $orderModel;

    public function __construct()
    {
        // Ensure models are loaded
        require_once APP_PATH . '/models/Review.php';
        require_once APP_PATH . '/models/Order.php';

        $this->reviewModel = new Review();
        $this->orderModel = new Order();
    }

    /**
     * Submit a product review
     */
    /**
     * Submit a product review
     */
    public function submit()
    {
        $isJson = false;
        $inputData = $_POST;

        // Check if request is JSON
        // Check if request is JSON or AJAX (for FormData/Multipart)
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if (strpos($contentType, 'application/json') !== false) {
            $isJson = true;
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $inputData = $decoded;
            }
        } elseif (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $isJson = true; // Treat as JSON response even if input is $_POST (FormData)
        }

        try {
            // 1. Check if user is logged in
            if (!Session::isLoggedIn()) {
                throw new Exception('You must be logged in to submit a review.');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // 2. Validate Input
            $orderId = $inputData['order_id'] ?? null;
            $productId = $inputData['product_id'] ?? null;
            $rating = $inputData['rating'] ?? null;
            $reviewText = $inputData['comment'] ?? ($inputData['review_text'] ?? '');
            $title = $inputData['title'] ?? '';
            $userId = Session::getUserId();

            if (!$orderId || !$productId || !$rating) {
                throw new Exception('Missing required information.');
            }

            // 3. Validate Order Status (BUSINESS RULE STRICT CHECK)
            $order = $this->orderModel->find($orderId);

            if (!$order) {
                throw new Exception('Order reference not found.');
            }

            if ($order['user_id'] != $userId) {
                throw new Exception('Unauthorized access to order.');
            }

            // RULE: Only DELIVERED or COMPLETED orders can be reviewed
            $orderStatus = strtoupper($order['order_status']);
            $allowedStatuses = ['DELIVERED', 'COMPLETED'];

            if (!in_array($orderStatus, $allowedStatuses)) {
                throw new Exception('You can only review products from delivered or completed orders.');
            }

            // 4. Validate Duplicate Review (BUSINESS RULE)
            $existingReview = $this->reviewModel->findOne([
                'user_id' => $userId,
                'product_id' => $productId,
                'order_id' => $orderId
            ]);

            if ($existingReview) {
                throw new Exception('You have already reviewed this product for this order.');
            }

            // 5. Submit Review
            $data = [
                'user_id' => $userId,
                'product_id' => $productId,
                'order_id' => $orderId,
                'rating' => (int) $rating,
                'title' => $title,
                'review_text' => $reviewText,
                'is_verified_purchase' => 1
            ];

            if ($this->reviewModel->submitReview($data)) {
                $msg = 'Thank you! Your review has been submitted for approval.';
                if ($isJson) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $msg]);
                    exit;
                }
                Session::setFlash('success', $msg);
            } else {
                throw new Exception('Failed to submit review. Please try again.');
            }

        } catch (Throwable $e) {
            $msg = $e->getMessage();
            // Log the error for debugging
            error_log("Review Submission Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            if ($isJson) {
                header('Content-Type: application/json');
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            Session::setFlash('error', $msg);
        }

        $this->redirectBack();
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
                'delivered' => false
            ]);
            exit;
        }

        $userId = Session::getUserId();
        $productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

        if (!$productId) {
            echo json_encode(['error' => 'Product ID required']);
            exit;
        }

        // Get detailed status
        $status = $this->reviewModel->checkDetailedEligibility($userId, $productId);

        $response = [
            'logged_in' => true,
            'purchased' => false,
            'delivered' => false,
            'eligible' => false,
            'order_id' => $status['order_id'] ?? null,
            'order_status' => $status['current_status'] ?? null
        ];

        // Status alignment
        if ($status['status'] === 'eligible') {
            $response['purchased'] = true;
            $response['delivered'] = true;
            $response['eligible'] = true;
        } elseif ($status['status'] === 'not_purchased') {
            // Defaults are already false
        } elseif ($status['status'] === 'not_delivered') {
            $response['purchased'] = true;
            // delivered and eligible remain false
        } elseif ($status['status'] === 'already_reviewed') {
            $response['purchased'] = true;
            $response['delivered'] = true; // Technically delivered if reviewed, usually
            $response['already_reviewed'] = true;
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Helper to redirect back to previous page
     */
    private function redirectBack()
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: ' . SITE_URL);
        }
    }

    private function redirect($path)
    {
        header('Location: ' . SITE_URL . $path);
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
}
