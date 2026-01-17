<?php
/**
 * Review Model
 */

class Review extends Model
{
    protected $table = 'reviews';
    protected $primaryKey = 'review_id';

    // Review Status Constants
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_HIDDEN = 'HIDDEN';

    /**
     * Get product reviews with sorting and filters
     */
    public function getProductReviews($productId, $filters = [])
    {
        $sort = $filters['sort'] ?? 'recent'; // recent, top_rated, lowest_rated, verified
        $limit = $filters['limit'] ?? 10;
        $page = $filters['page'] ?? 1;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT r.*, u.first_name, u.last_name, u.profile_image
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.user_id
                WHERE r.product_id = ? AND r.status = 'APPROVED'";

        // Sorting logic
        switch ($sort) {
            case 'top_rated':
                $sql .= " ORDER BY r.rating DESC, r.created_at DESC";
                break;
            case 'lowest_rated':
                $sql .= " ORDER BY r.rating ASC, r.created_at DESC";
                break;
            case 'verified':
                // Prioritize verified, then recent
                $sql .= " ORDER BY r.is_verified_purchase DESC, r.created_at DESC";
                break;
            case 'recent':
            default:
                $sql .= " ORDER BY r.created_at DESC";
                break;
        }

        $sql .= " LIMIT ? OFFSET ?";

        $stmt = $this->query($sql, [$productId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Get detailed rating statistics for a product
     */
    public function getProductRatingStats($productId)
    {
        // Average and Total
        $sqlBasic = "SELECT 
                        ROUND(AVG(rating), 1) as avg_rating, 
                        COUNT(*) as total_reviews
                     FROM {$this->table}
                     WHERE product_id = ? AND status = 'APPROVED'";
        $basic = $this->query($sqlBasic, [$productId])->fetch();

        // Star Distribution (Histogram)
        $sqlDist = "SELECT rating, COUNT(*) as count
                    FROM {$this->table}
                    WHERE product_id = ? AND status = 'APPROVED'
                    GROUP BY rating
                    ORDER BY rating DESC";
        $distStmt = $this->query($sqlDist, [$productId]);
        $distribution = $distStmt->fetchAll();

        // Format distribution array (ensure keys 5 to 1 exist)
        $distFormatted = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0
        ];
        foreach ($distribution as $row) {
            $distFormatted[$row['rating']] = $row['count'];
        }

        return [
            'avg_rating' => $basic['avg_rating'] ?? 0,
            'total_reviews' => $basic['total_reviews'] ?? 0,
            'distribution' => $distFormatted
        ];
    }

    /**
     * Check if user is eligible to review
     */
    /**
     * Check if user is eligible to review
     */
    public function checkEligibility($userId, $productId)
    {
        // 1. Check if user already reviewed this product
        $existing = $this->findOne([
            'user_id' => $userId,
            'product_id' => $productId
        ]);

        if ($existing) {
            return ['eligible' => false, 'reason' => 'already_reviewed', 'review' => $existing];
        }

        // 2. Check for delivered order containing this product
        // We need to check the 'orders' and 'order_items' tables
        // STRICT: Only 'delivered' status allowed
        $sql = "SELECT o.order_id, o.delivered_at 
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.user_id = ? 
                  AND oi.product_id = ? 
                  AND (LOWER(TRIM(o.order_status)) = 'delivered' OR LOWER(TRIM(o.order_status)) = 'completed')
                ORDER BY o.created_at DESC
                LIMIT 1";

        $stmt = $this->query($sql, [$userId, $productId]);
        $order = $stmt->fetch();

        if ($order) {
            return ['eligible' => true, 'order_id' => $order['order_id']];
        }

        return ['eligible' => false, 'reason' => 'no_delivered_order'];
    }

    /**
     * Check detailed eligibility status (Granular)
     */
    public function checkDetailedEligibility($userId, $productId)
    {
        // 1. Check if already reviewed
        $existing = $this->findOne([
            'user_id' => $userId,
            'product_id' => $productId
        ]);

        if ($existing) {
            return ['status' => 'already_reviewed', 'eligible' => false];
        }

        // 2. Check for ANY order containing this product
        $sql = "SELECT o.order_id, o.order_status
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.user_id = ? 
                  AND oi.product_id = ?
                ORDER BY o.created_at DESC";

        $stmt = $this->query($sql, [$userId, $productId]);
        $orders = $stmt->fetchAll();

        if (empty($orders)) {
            return ['status' => 'not_purchased', 'eligible' => false];
        }

        // 3. Check if any of those orders are DELIVERED
        $hasDelivered = false;
        $deliveredOrderId = null;

        foreach ($orders as $order) {
            $status = strtoupper(trim($order['order_status']));
            if ($status === 'DELIVERED' || $status === 'COMPLETED') {
                $hasDelivered = true;
                $deliveredOrderId = $order['order_id'];
                break;
            }
        }

        if (!$hasDelivered) {
            // Find the most relevant status to show
            $displayStatus = 'PENDING'; // Default
            if (!empty($orders)) {
                $displayStatus = strtoupper($orders[0]['order_status']);
            }
            return [
                'status' => 'not_delivered',
                'eligible' => false,
                'current_status' => $displayStatus
            ];
        }

        return [
            'status' => 'eligible',
            'eligible' => true,
            'order_id' => $deliveredOrderId,
            'current_status' => 'DELIVERED'
        ];
    }

    /**
     * Submit a new review
     */
    public function submitReview($data)
    {
        // Sanitize and Prepare
        $reviewData = [
            'product_id' => $data['product_id'],
            'user_id' => $data['user_id'],
            'order_id' => $data['order_id'] ?? null,
            'rating' => $data['rating'],
            'title' => strip_tags($data['title']),
            'review_text' => strip_tags($data['review_text']),
            'media_json' => isset($data['media']) ? json_encode($data['media']) : null,
            'is_verified_purchase' => $data['is_verified_purchase'] ?? 0,
            'status' => $this->autoModerate($data),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($reviewData);
    }

    /**
     * Auto-moderation logic
     */
    private function autoModerate($data)
    {
        // 1. Blocklist check (simplified)
        $badWords = ['spam', 'fake', 'abuse', 'http://', 'https://'];
        $text = strtolower($data['title'] . ' ' . $data['review_text']);

        foreach ($badWords as $word) {
            if (strpos($text, $word) !== false) {
                return self::STATUS_REJECTED;
            }
        }

        // 2. Low rating check (needs admin eyes)
        if ($data['rating'] == 1) {
            return self::STATUS_PENDING;
        }

        // 3. Verified Purchase Auto-Approve (if 4-5 stars and clean text)
        if (!empty($data['is_verified_purchase']) && $data['rating'] >= 4) {
            return self::STATUS_APPROVED;
        }

        // Default to Pending for safety
        return self::STATUS_PENDING;
    }

    /**
     * Admin: Update Review Status
     */
    public function updateStatus($reviewId, $status, $adminId, $reason = null)
    {
        $data = [
            'status' => $status,
            'is_approved' => ($status === 'APPROVED' ? 1 : 0)
        ];
        $this->update($reviewId, $data);

        // Log action
        $this->logAdminAction($reviewId, $adminId, strtoupper($status), $reason);
        return true;
    }

    /**
     * Admin: Reply to Review
     */
    public function adminReply($reviewId, $replyText, $adminId)
    {
        $this->update($reviewId, ['admin_reply' => $replyText]);
        $this->logAdminAction($reviewId, $adminId, 'REPLY', 'Added reply');
        return true;
    }

    private function logAdminAction($reviewId, $adminId, $action, $reason)
    {
        $sql = "INSERT INTO review_admin_logs (review_id, admin_id, action, reason) VALUES (?, ?, ?, ?)";
        $this->query($sql, [$reviewId, $adminId, $action, $reason]);
    }

    /**
     * Report a review
     */
    public function reportReview($reviewId, $userId, $reason)
    {
        // Check if already reported
        $existing = $this->query(
            "SELECT report_id FROM review_reports WHERE review_id = ? AND user_id = ?",
            [$reviewId, $userId]
        )->fetch();

        if ($existing) {
            return false; // Already reported
        }

        $sql = "INSERT INTO review_reports (review_id, user_id, reason, created_at) VALUES (?, ?, ?, NOW())";
        return $this->query($sql, [$reviewId, $userId, $reason]);
    }

    /**
     * Get all reviews with advanced filtering (Admin)
     */
    public function getAllReviews($filters = [], $limit = 15, $offset = 0)
    {
        $sql = "SELECT r.*, p.name as product_name, p.slug as product_slug, 
                u.first_name, u.last_name, u.email,
                (SELECT COUNT(*) FROM review_reports WHERE review_id = r.review_id) as report_count
                FROM {$this->table} r
                LEFT JOIN products p ON r.product_id = p.product_id
                LEFT JOIN users u ON r.user_id = u.user_id
                WHERE 1=1";

        $params = [];

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND r.status = ?";
            $params[] = strtoupper($filters['status']);
        }

        if (isset($filters['rating']) && $filters['rating']) {
            $sql .= " AND r.rating = ?";
            $params[] = $filters['rating'];
        }

        if (isset($filters['search']) && $filters['search']) {
            $sql .= " AND (r.title LIKE ? OR r.review_text LIKE ? OR u.email LIKE ?)";
            $term = "%{$filters['search']}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        // Sort by pending first, then reports, then date
        $sql .= " ORDER BY (r.status = 'PENDING') DESC, report_count DESC, r.created_at DESC";
        $sql .= " LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get total count of reviews matching filters (Admin)
     */
    public function getReviewsCount($filters = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} r 
                LEFT JOIN products p ON r.product_id = p.product_id
                LEFT JOIN users u ON r.user_id = u.user_id 
                WHERE 1=1";

        $params = [];

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND r.status = ?";
            $params[] = strtoupper($filters['status']);
        }

        if (isset($filters['rating']) && $filters['rating']) {
            $sql .= " AND r.rating = ?";
            $params[] = $filters['rating'];
        }

        if (isset($filters['search']) && $filters['search']) {
            $sql .= " AND (r.title LIKE ? OR r.review_text LIKE ? OR u.email LIKE ?)";
            $term = "%{$filters['search']}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $stmt = $this->query($sql, $params);
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getProductRating($productId)
    {
        $stats = $this->getProductRatingStats($productId);
        return ['avg_rating' => $stats['avg_rating'], 'total_reviews' => $stats['total_reviews']];
    }
}

