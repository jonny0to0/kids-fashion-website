<?php
/**
 * Checkout Controller
 * Handles checkout process
 */

class CheckoutController {
    private $cartModel;
    private $orderModel;
    private $addressModel;
    
    public function __construct() {
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->addressModel = new Address();
    }
    
    /**
     * Show checkout page
     */
    public function index() {
        if (!Session::isLoggedIn()) {
            Session::set('redirect_after_login', $_SERVER['REQUEST_URI']);
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }

        // Check if user is suspended
        if (Session::get('user_status') === USER_STATUS_SUSPENDED) {
            Session::setFlash('error', 'Your account is suspended. You cannot checkout at this time.');
            header('Location: ' . SITE_URL . '/cart');
            exit;
        }
        
        $userId = Session::getUserId();
        $cart = $this->cartModel->getOrCreateCart($userId, null);
        $items = $this->cartModel->getItems($cart['cart_id']);
        $total = $this->cartModel->getTotal($cart['cart_id']);
        $addresses = $this->addressModel->getUserAddresses($userId);
        
        if (empty($items)) {
            Session::setFlash('error', 'Your cart is empty');
            header('Location: ' . SITE_URL . '/cart');
            exit;
        }
        
        $this->render('checkout/index', [
            'items' => $items,
            'total' => $total,
            'addresses' => $addresses
        ]);
    }
    
    /**
     * Process order
     */
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . SITE_URL . '/checkout');
            exit;
        }
        
        if (!Session::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }

        // Check if user is suspended
        if (Session::get('user_status') === USER_STATUS_SUSPENDED) {
            Session::setFlash('error', 'Your account is suspended. You cannot place orders at this time.');
            header('Location: ' . SITE_URL . '/cart');
            exit;
        }
        
        $userId = Session::getUserId();
        $cart = $this->cartModel->getOrCreateCart($userId, null);
        $items = $this->cartModel->getItems($cart['cart_id']);
        $total = $this->cartModel->getTotal($cart['cart_id']);
        
        // Get or create addresses
        $shippingAddressId = (int)($_POST['shipping_address_id'] ?? 0);
        $billingAddressId = (int)($_POST['billing_address_id'] ?? $shippingAddressId);
        
        // Validate shipping address
        if ($shippingAddressId <= 0) {
            Session::setFlash('error', 'Please select a shipping address.');
            header('Location: ' . SITE_URL . '/checkout');
            exit;
        }
        
        // Verify shipping address exists and belongs to user
        $shippingAddress = $this->addressModel->find($shippingAddressId);
        if (!$shippingAddress || $shippingAddress['user_id'] != $userId) {
            Session::setFlash('error', 'Invalid shipping address selected.');
            header('Location: ' . SITE_URL . '/checkout');
            exit;
        }
        
        // Validate billing address if different from shipping
        if ($billingAddressId != $shippingAddressId) {
            $billingAddress = $this->addressModel->find($billingAddressId);
            if (!$billingAddress || $billingAddress['user_id'] != $userId) {
                Session::setFlash('error', 'Invalid billing address selected.');
                header('Location: ' . SITE_URL . '/checkout');
                exit;
            }
        }
        
        // Calculate shipping
        $shippingAmount = ($total['total'] ?? 0) >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
        $finalAmount = ($total['total'] ?? 0) + $shippingAmount;
        
        // Prepare order data
        $orderData = [
            'user_id' => $userId,
            'total_amount' => $total['total'] ?? 0,
            'shipping_amount' => $shippingAmount,
            'final_amount' => $finalAmount,
            'payment_method' => $_POST['payment_method'] ?? PAYMENT_METHOD_COD,
            'payment_status' => ($_POST['payment_method'] ?? PAYMENT_METHOD_COD) === PAYMENT_METHOD_COD 
                ? PAYMENT_STATUS_PENDING 
                : PAYMENT_STATUS_PAID,
            'shipping_address_id' => $shippingAddressId,
            'billing_address_id' => $billingAddressId,
            'items' => []
        ];
        
        // Prepare order items
        foreach ($items as $item) {
            $orderData['items'][] = [
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'product_name' => $item['name'],
                'product_image' => $item['image'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['quantity'] * $item['price']
            ];
        }
        
        // Create order
        $result = $this->orderModel->createOrder($orderData);
        
        if ($result) {
            // Clear cart
            $this->cartModel->clear($cart['cart_id']);
            
            // Send confirmation email
            $user = (new User())->find($userId);
            Email::sendOrderConfirmation($user['email'], $result['order_number'], $orderData);
            
            Session::setFlash('success', 'Order placed successfully! Order Number: ' . $result['order_number']);
            header('Location: ' . SITE_URL . '/order/confirmation/' . $result['order_number']);
            exit;
        } else {
            Session::setFlash('error', 'Failed to place order. Please try again.');
            header('Location: ' . SITE_URL . '/checkout');
            exit;
        }
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

