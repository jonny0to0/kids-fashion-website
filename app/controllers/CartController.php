<?php
/**
 * Cart Controller
 * Handles shopping cart operations
 */

class CartController {
    private $cartModel;
    private $productModel;
    
    public function __construct() {
        $this->cartModel = new Cart();
        $this->productModel = new Product();
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
        
        $productId = (int)($_POST['product_id'] ?? 0);
        $variantId = isset($_POST['variant_id']) && $_POST['variant_id'] ? (int)$_POST['variant_id'] : null;
        $quantity = (int)($_POST['quantity'] ?? 1);
        
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
        
        $cart = $this->getCart();
        $this->cartModel->addItem($cart['cart_id'], $productId, $variantId, $quantity, $price);
        
        echo json_encode(['success' => true, 'message' => 'Item added to cart']);
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
        
        echo json_encode([
            'success' => true,
            'total' => $total['total'] ?? 0,
            'item_count' => $total['item_count'] ?? 0
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
        
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
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

