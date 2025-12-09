<?php
/**
 * Wishlist Model
 */

class Wishlist extends Model {
    protected $table = 'wishlist';
    protected $primaryKey = 'wishlist_id';
    
    public function add($userId, $productId) {
        try {
            return $this->create(['user_id' => $userId, 'product_id' => $productId]);
        } catch (PDOException $e) {
            // Item already exists
            return false;
        }
    }
    
    public function remove($userId, $productId) {
        $this->query("DELETE FROM {$this->table} WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
    }
    
    public function getUserWishlist($userId) {
        $sql = "SELECT w.*, p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM {$this->table} w
                JOIN products p ON w.product_id = p.product_id
                WHERE w.user_id = ? AND p.status = ?
                ORDER BY w.added_at DESC";
        
        $stmt = $this->query($sql, [$userId, PRODUCT_STATUS_ACTIVE]);
        return $stmt->fetchAll();
    }
    
    public function isInWishlist($userId, $productId) {
        $result = $this->findOne(['user_id' => $userId, 'product_id' => $productId]);
        return !empty($result);
    }
}

