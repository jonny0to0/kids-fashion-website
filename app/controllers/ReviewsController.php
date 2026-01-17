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
    public function submit()
    {
        // 1. Check if user is logged in
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'You must be logged in to submit a review.');
            $this->redirectBack();
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
            exit;
        }

        // 2. Validate Input
        $orderId = $_POST['order_id'] ?? null;
        $productId = $_POST['product_id'] ?? null;
        $rating = $_POST['rating'] ?? null;
        $reviewText = $_POST['review_text'] ?? '';
        $title = $_POST['title'] ?? ''; // Optional
        $userId = Session::getUserId();

        if (!$orderId || !$productId || !$rating) {
            Session::setFlash('error', 'Missing required information.');
            $this->redirectBack();
            exit;
        }

        // 3. Validate Order Status (BUSINESS RULE STRICT CHECK)
        $order = $this->orderModel->find($orderId);

        if (!$order) {
            Session::setFlash('error', 'Order reference not found.');
            $this->redirectBack();
            exit;
        }

        if ($order['user_id'] != $userId) {
            Session::setFlash('error', 'Unauthorized access to order.');
            $this->redirectBack();
            exit;
        }

        // RULE: Only DELIVERED orders can be reviewed
        if (strtoupper($order['order_status']) !== 'DELIVERED') {
            Session::setFlash('error', 'You can only review products from delivered orders.');
            $this->redirectBack();
            exit;
        }

        // 4. Validate Duplicate Review (BUSINESS RULE)
        $existingReview = $this->reviewModel->findOne([
            'user_id' => $userId,
            'product_id' => $productId,
            'order_id' => $orderId
        ]);

        if ($existingReview) {
            Session::setFlash('error', 'You have already reviewed this product for this order.');
            $this->redirectBack();
            exit;
        }

        // 5. Submit Review
        $data = [
            'user_id' => $userId,
            'product_id' => $productId,
            'order_id' => $orderId,
            'rating' => (int) $rating,
            'title' => $title,
            'review_text' => $reviewText,
            'is_verified_purchase' => 1 // It's linked to an order, so yes
        ];

        if ($this->reviewModel->submitReview($data)) {
            Session::setFlash('success', 'Thank you! Your review has been submitted for approval.');
        } else {
            Session::setFlash('error', 'Failed to submit review. Please try again.');
        }

        $this->redirectBack();
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
            'purchased' => true,
            'delivered' => true,
            'eligible' => $status['eligible'],
            'order_id' => $status['order_id'] ?? null
        ];

        if ($status['status'] === 'not_purchased') {
            $response['purchased'] = false;
            $response['delivered'] = false;
        } elseif ($status['status'] === 'not_delivered') {
            $response['purchased'] = true;
            $response['delivered'] = false;
        } elseif ($status['status'] === 'already_reviewed') {
            // Treat as eligible=false but technically purchased/delivered could be true.
            // For the frontend prompt logic:
            // "If not purchased -> Purchase Required"
            // "If not delivered -> Order Not Delivered"
            // "If already reviewed" -> (Implicitly delivered usually, but let's just send a flag if needed or handle as eligible=false)
            // The user request logic chart didn't explicitly handle "Already Reviewed" popup, but simpler to just say not eligible usually.
            // However, the user prompt logic flow is:
            // 1. Logged In?
            // 2. Purchased?
            // 3. Delivered?
            // -> Open Form.

            // If already reviewed, we might want to block or let them edit?
            // User didn't specify "Already Reviewed" popup behavior in "FINAL FLOW".
            // But existing Detail page handles it.
            // Let's add 'already_reviewed' to response for clarity.
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
