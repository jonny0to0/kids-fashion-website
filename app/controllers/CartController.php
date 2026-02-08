<?php
/**
 * Cart Controller
 * Handles shopping cart operations
 */

class CartController {
    private $cartModel;
    private $productModel;
    private $wishlistModel;
    
    public function __construct() {
        $this->cartModel = new Cart();
        $this->productModel = new Product();
        $this->wishlistModel = new Wishlist();
    }
    
    /**
     * View cart
     */
    public function index() {
        $cart = $this->getCart();
        $items = $this->cartModel->getItems($cart['cart_id']);
        $total = $this->cartModel->getTotal($cart['cart_id']);
        
        $this->render('cart/index', [
            'items' => $items,
            'total' => $total
        ]);
    }
    
    /**
     * Add to cart (AJAX)
     */
    public function add() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        // Check if user is suspended
        if (Session::isLoggedIn() && Session::get('user_status') === USER_STATUS_SUSPENDED) {
            echo json_encode([
                'success' => false, 
                'message' => 'Your account is suspended. You cannot add items to cart.'
            ]);
            return;
        }
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $variantId = isset($_POST['variant_id']) && $_POST['variant_id'] ? (int)$_POST['variant_id'] : null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        $buyNow = isset($_POST['buy_now']) && $_POST['buy_now'] === '1';
        
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            return;
        }
        
        $product = $this->productModel->find($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        $price = $this->productModel->getPrice($product);
        
        // Check for variant price override
        if ($variantId) {
            require_once APP_PATH . '/models/ProductVariant.php';
            $variantModel = new ProductVariant();
            $variant = $variantModel->getVariant($variantId);
            
            if ($variant) {
                // Priority 1: Absolute Price Override
                // Check for sale price first
                if (!empty($variant['sale_price']) && $variant['sale_price'] > 0 && $variant['sale_price'] < $variant['price']) {
                    $price = $variant['sale_price'];
                } elseif (!empty($variant['price']) && $variant['price'] > 0) {
                    $price = $variant['price'];
                }
                // Priority 2: Price Adjustment (Additional Price) - Legacy support
                elseif (isset($variant['additional_price']) && is_numeric($variant['additional_price'])) {
                    $price += (float)$variant['additional_price'];
                }
            }
        }
        
        $cart = $this->getCart();
        
        // If buy now, clear existing cart items first
        if ($buyNow) {
            $this->cartModel->clear($cart['cart_id']);
        }
        
        $this->cartModel->addItem($cart['cart_id'], $productId, $variantId, $quantity, $price);
        
        // Get updated cart count
        $total = $this->cartModel->getTotal($cart['cart_id']);
        $count = $total['count'] ?? $total['total_quantity'] ?? $total['item_count'] ?? 0;
        
        echo json_encode([
            'success' => true, 
            'message' => $buyNow ? 'Proceeding to checkout' : 'Item added to cart',
            'count' => (int)$count,
            'buy_now' => $buyNow
        ]);
    }
    
    /**
     * Update cart item
     */
    public function update() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        $this->cartModel->updateItemQuantity($cartItemId, $quantity);
        
        $cart = $this->getCart();
        $total = $this->cartModel->getTotal($cart['cart_id']);
        $count = $total['count'] ?? $total['total_quantity'] ?? $total['item_count'] ?? 0;
        
        echo json_encode([
            'success' => true,
            'total' => $total['total'] ?? 0,
            'item_count' => $total['item_count'] ?? 0,
            'count' => (int)$count
        ]);
    }
    
    /**
     * Remove from cart
     */
    public function remove() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        $this->cartModel->removeItem($cartItemId);
        
        // Get updated cart count
        $cart = $this->getCart();
        $total = $this->cartModel->getTotal($cart['cart_id']);
        $count = $total['count'] ?? $total['total_quantity'] ?? $total['item_count'] ?? 0;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Item removed from cart',
            'count' => (int)$count
        ]);
    }
    
    /**
     * Save item for later (move to wishlist)
     */
    public function saveForLater() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        // Require authentication for wishlist
        if (!Session::isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Please login to save items for later']);
            return;
        }
        
        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        
        if (!$cartItemId) {
            echo json_encode(['success' => false, 'message' => 'Cart item ID is required']);
            return;
        }
        
        // Get cart item details
        $cartItem = $this->cartModel->query(
            "SELECT ci.*, ci.product_id FROM cart_items ci WHERE ci.cart_item_id = ?",
            [$cartItemId]
        )->fetch();
        
        if (!$cartItem) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            return;
        }
        
        // Add to wishlist
        $userId = Session::getUserId();
        
        if ($this->wishlistModel->add($userId, $cartItem['product_id'])) {
            // Remove from cart
            $this->cartModel->removeItem($cartItemId);
            
            // Get updated cart count
            $cart = $this->getCart();
            $total = $this->cartModel->getTotal($cart['cart_id']);
            $count = $total['count'] ?? $total['total_quantity'] ?? $total['item_count'] ?? 0;
            
            echo json_encode([
                'success' => true,
                'message' => 'Item saved for later',
                'count' => (int)$count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item already in wishlist or failed to save']);
        }
    }
    
    /**
     * Get cart count (AJAX)
     */
    public function getCount() {
        header('Content-Type: application/json');
        
        $cart = $this->getCart();
        $total = $this->cartModel->getTotal($cart['cart_id']);
        
        // Use count field which contains total quantity, fallback to item_count
        $count = $total['count'] ?? $total['total_quantity'] ?? $total['item_count'] ?? 0;
        
        echo json_encode([
            'success' => true,
            'count' => (int)$count
        ]);
    }
    
    /**
     * Get or create cart
     */
    private function getCart() {
        $userId = Session::isLoggedIn() ? Session::getUserId() : null;
        $sessionId = !$userId ? session_id() : null;
        
        return $this->cartModel->getOrCreateCart($userId, $sessionId);
    }
    
    /**
     * Render view
     */
    private function render($view, $data = []) {
        extract($data);
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/' . $view . '.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }
}

