<?php
/**
 * Review Model
 */

class Review extends Model {
    protected $table = 'reviews';
    protected $primaryKey = 'review_id';
    
    public function getProductReviews($productId, $approvedOnly = true) {
        $sql = "SELECT r.*, u.first_name, u.last_name, u.profile_image
                FROM {$this->table} r
                JOIN users u ON r.user_id = u.user_id
                WHERE r.product_id = ?";
        
        $params = [$productId];
        
        if ($approvedOnly) {
            $sql .= " AND r.is_approved = 1";
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function getProductRating($productId) {
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
                FROM {$this->table}
                WHERE product_id = ? AND is_approved = 1";
        
        $stmt = $this->query($sql, [$productId]);
        return $stmt->fetch();
    }
    
    public function canReview($userId, $productId, $orderId = null) {
        // Check if user already reviewed
        $existing = $this->findOne(['user_id' => $userId, 'product_id' => $productId]);
        
        if ($existing) {
            return false;
        }
        
        // If order_id provided, verify it belongs to user and contains the product
        if ($orderId) {
            $sql = "SELECT COUNT(*) as total FROM order_items 
                    WHERE order_id = ? AND product_id = ?";
            $stmt = $this->query($sql, [$orderId, $productId]);
            $result = $stmt->fetch();
            
            return ($result['total'] ?? 0) > 0;
        }
        
        return true;
    }
}

