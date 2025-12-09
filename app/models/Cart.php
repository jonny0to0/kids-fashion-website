<?php
/**
 * Cart Model
 * Handles shopping cart operations
 */

class Cart extends Model {
    protected $table = 'cart';
    protected $primaryKey = 'cart_id';
    
    /**
     * Get or create cart for user/session
     */
    public function getOrCreateCart($userId = null, $sessionId = null) {
        if ($userId) {
            $cart = $this->findOne(['user_id' => $userId]);
        } else {
            $cart = $this->findOne(['session_id' => $sessionId]);
        }
        
        if (!$cart) {
            $cartId = $this->create([
                'user_id' => $userId,
                'session_id' => $sessionId
            ]);
            return $this->find($cartId);
        }
        
        return $cart;
    }
    
    /**
     * Add item to cart
     */
    public function addItem($cartId, $productId, $variantId = null, $quantity = 1, $price = 0) {
        // Check if item already exists
        $existingItem = $this->query(
            "SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ? AND variant_id " . ($variantId ? "= ?" : "IS NULL"),
            array_filter([$cartId, $productId, $variantId])
        )->fetch();
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            $this->query(
                "UPDATE cart_items SET quantity = ?, price = ? WHERE cart_item_id = ?",
                [$newQuantity, $price, $existingItem['cart_item_id']]
            );
            return $existingItem['cart_item_id'];
        } else {
            // Insert new item
            $this->query(
                "INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, price) VALUES (?, ?, ?, ?, ?)",
                [$cartId, $productId, $variantId, $quantity, $price]
            );
            return $this->db->lastInsertId();
        }
    }
    
    /**
     * Get cart items
     */
    public function getItems($cartId) {
        $sql = "SELECT ci.*, p.name, p.slug, p.sku,
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image,
                COALESCE(p.sale_price, p.price) as product_price,
                pv.size, pv.color, pv.color_code
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.product_id
                LEFT JOIN product_variants pv ON ci.variant_id = pv.variant_id
                WHERE ci.cart_id = ?
                ORDER BY ci.added_at DESC";
        
        $stmt = $this->query($sql, [$cartId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update item quantity
     */
    public function updateItemQuantity($cartItemId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }
        
        return $this->query(
            "UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?",
            [$quantity, $cartItemId]
        );
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem($cartItemId) {
        return $this->query("DELETE FROM cart_items WHERE cart_item_id = ?", [$cartItemId]);
    }
    
    /**
     * Clear cart
     */
    public function clear($cartId) {
        return $this->query("DELETE FROM cart_items WHERE cart_id = ?", [$cartId]);
    }
    
    /**
     * Get cart total
     */
    public function getTotal($cartId) {
        $sql = "SELECT SUM(ci.quantity * ci.price) as total, 
                       COUNT(*) as item_count,
                       COALESCE(SUM(ci.quantity), 0) as total_quantity
                FROM cart_items ci
                WHERE ci.cart_id = ?";
        
        $stmt = $this->query($sql, [$cartId]);
        $result = $stmt->fetch();
        // Use total_quantity for cart count display (total items including quantities)
        // Ensure count is always an integer, defaulting to 0 if null
        $result['count'] = (int)($result['total_quantity'] ?? 0);
        $result['total'] = (float)($result['total'] ?? 0);
        return $result;
    }
}

