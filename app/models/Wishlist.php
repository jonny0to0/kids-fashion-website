<?php
/**
 * Wishlist Model
 */

class Wishlist extends Model {
    protected $table = 'wishlist';
    protected $primaryKey = 'wishlist_id';
    
    public function add($userId, $productId) {
        try {
            // Check if already exists
            if ($this->isInWishlist($userId, $productId)) {
                return false;
            }
            return $this->create(['user_id' => $userId, 'product_id' => $productId]);
        } catch (PDOException $e) {
            // Item already exists or other database error
            error_log("Wishlist add error: " . $e->getMessage());
            return false;
        }
    }
    
    public function remove($userId, $productId) {
        try {
            $stmt = $this->query("DELETE FROM {$this->table} WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Wishlist remove error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserWishlist($userId) {
        $sql = "SELECT w.wishlist_id, w.added_at, 
                p.product_id, p.name, p.slug, p.description, p.price, p.sale_price, 
                p.status, p.sku, p.stock_quantity,
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM {$this->table} w
                JOIN products p ON w.product_id = p.product_id
                WHERE w.user_id = ? AND p.status = ?
                ORDER BY w.added_at DESC";
        
        $stmt = $this->query($sql, [$userId, PRODUCT_STATUS_ACTIVE]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function isInWishlist($userId, $productId) {
        $result = $this->findOne(['user_id' => $userId, 'product_id' => $productId]);
        return !empty($result);
    }
    
    public function getCount($userId) {
        $stmt = $this->query("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ?", [$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }
}

