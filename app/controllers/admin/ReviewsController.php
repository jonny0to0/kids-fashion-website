<?php
/**
 * Admin Reviews Controller
 * Handles review moderation and management
 */

class ReviewsController extends AdminController
{
    private $reviewModel;

    public function __construct()
    {
        parent::__construct();
        $this->reviewModel = new Review();
    }

    /**
     * List all reviews with filtering
     */
    public function index()
    {
        // Handle filter parameters
        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'product_id' => $_GET['product_id'] ?? null,
            'rating' => $_GET['rating'] ?? null,
            'search' => $_GET['search'] ?? null,
            'report_status' => $_GET['report_status'] ?? null
        ];

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        // Use Review Model to fetch data
        $reviews = $this->reviewModel->getAllReviews($filters, $limit, $offset);
        $totalReviews = $this->reviewModel->getReviewsCount($filters);
        $totalPages = ceil($totalReviews / $limit);

        // Render View
        $this->render('admin/reviews/index', [
            'reviews' => $reviews,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalReviews' => $totalReviews
        ]);
    }

    /**
     * Update review status (AJAX)
     */
    public function update_status()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $reviewId = $_POST['review_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $reason = $_POST['reason'] ?? null;

        if (!$reviewId || !$status) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        $allowedStatuses = ['APPROVED', 'REJECTED', 'HIDDEN', 'PENDING'];
        if (!in_array($status, $allowedStatuses)) {
            echo json_encode(['error' => 'Invalid status']);
            return;
        }

        try {
            $this->reviewModel->updateStatus($reviewId, $status, Session::getUserId(), $reason);

            // If rejected/hidden, maybe also resolve reports?

            echo json_encode(['success' => true, 'message' => "Review updated to $status"]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reply to a review (AJAX)
     */
    public function reply()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        $reviewId = $_POST['review_id'];
        $replyText = $_POST['reply_text'];

        if (empty($replyText)) {
            echo json_encode(['error' => 'Reply text cannot be empty']);
            return;
        }

        $this->reviewModel->adminReply($reviewId, $replyText, Session::getUserId());
        echo json_encode(['success' => true]);
    }
}
