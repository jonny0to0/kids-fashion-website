<?php
/**
 * Checkout Controller
 * Handles checkout process
 */

class CheckoutController {
    private $cartModel;
    private $orderModel;
    private $addressModel;
    private $zoneMatcher;
    private $deliveryMethodModel;
    
    public function __construct() {
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->addressModel = new Address();
        
        // Initialize Shipping Services
        require_once APP_PATH . '/services/ZoneMatcher.php';
        $this->zoneMatcher = new ZoneMatcher();
        $this->deliveryMethodModel = new DeliveryMethod();
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

        // Determine active address (Default or First)
        $activeAddress = null;
        foreach ($addresses as $addr) {
            if ($addr['is_default']) {
                $activeAddress = $addr;
                break;
            }
        }
        if (!$activeAddress && !empty($addresses)) {
            $activeAddress = $addresses[0];
        }

        // Get Shipping Methods for Active Address
        $shippingMethods = [];
        $matchedZone = null;
        
        $cartWeight = $this->calculateCartWeight($items);
        
        if ($activeAddress) {
            $matchedZone = $this->zoneMatcher->match($activeAddress);
            if ($matchedZone) {
                // DEBUG LOGGING
                if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                    error_log("Checkout: Matched Zone [{$matchedZone['id']}] {$matchedZone['zone_name']} for Address ID " . ($activeAddress['address_id'] ?? $activeAddress['id'] ?? '?'));
                }
                
                $allMethods = $this->deliveryMethodModel->getMethodsByZone($matchedZone['id']);
                
                if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                    error_log("Checkout: Fetched " . count($allMethods) . " active methods for Zone [{$matchedZone['id']}]");
                }

                $shippingMethods = $this->filterMethods($allMethods, $cartWeight, $total['total']);
                
                if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                    error_log("Checkout: " . count($shippingMethods) . " methods remaining after filter for Weight: $cartWeight, Total: {$total['total']}");
                }
            } else {
                if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                    error_log("Checkout: No Zone matched for Address ID " . ($activeAddress['address_id'] ?? $activeAddress['id'] ?? '?'));
                }
                // Fallback if no zone matched
                // Trigger Zone Mismatch Event
                if (file_exists(APP_PATH . '/services/EventService.php')) {
                    require_once APP_PATH . '/services/EventService.php';
                    (new EventService())->dispatch(EventService::EVENT_SHIPPING_ZONE_MISMATCH, [
                        'user_id' => $userId,
                        'user_name' => Session::get('user_name'),
                        'pincode' => $activeAddress['pincode'] ?? 'Unknown'
                    ]);
                }
            }
        }
        
        $this->render('checkout/index', [
            'items' => $items,
            'total' => $total,
            'addresses' => $addresses,
            'shippingMethods' => $shippingMethods,
            'matchedZone' => $matchedZone
        ]);
    }

    /**
     * API: Get shipping methods for an address
     */
    public function getShippingMethods() {
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $addressId = $_GET['address_id'] ?? 0;
        if (!$addressId) {
            http_response_code(400);
            echo json_encode(['error' => 'Address ID required']);
            exit;
        }

        $userId = Session::getUserId();
        // Verify address belongs to user
        $address = $this->addressModel->find($addressId);
        if (!$address || $address['user_id'] != $userId) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid address']);
            exit;
        }

        // Get Cart Context
        $cart = $this->cartModel->getOrCreateCart($userId, null);
        $items = $this->cartModel->getItems($cart['cart_id']);
        $totalData = $this->cartModel->getTotal($cart['cart_id']);
        $cartTotal = $totalData['total'] ?? 0;
        $cartWeight = $this->calculateCartWeight($items);

        $matchedZone = $this->zoneMatcher->match($address);
        $methods = [];

        if ($matchedZone) {
            $allMethods = $this->deliveryMethodModel->getMethodsByZone($matchedZone['id']);
            $methods = $this->filterMethods($allMethods, $cartWeight, $cartTotal);
        }

        header('Content-Type: application/json');
        echo json_encode(['methods' => array_values($methods)]);
        exit;
    }
    
    /**
     * Calculate total weight of cart items
     */
    private function calculateCartWeight($items) {
        $totalWeight = 0;
        foreach ($items as $item) {
            // weight is in kg (decimal 8,2)
            $weight = (float)($item['weight'] ?? 0);
            $qty = (int)$item['quantity'];
            $totalWeight += ($weight * $qty);
        }
        return $totalWeight;
    }

    /**
     * Filter shipping methods based on rules (Rule 3.2)
     */
    private function filterMethods($methods, $weight, $total) {
        $validMethods = [];
        
        foreach ($methods as &$method) {
            // Check Weight Limits
            if (!empty($method['min_weight']) && $weight < (float)$method['min_weight']) {
                if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                    error_log("Checkout Filter: Excluded '{$method['name']}' - Weight $weight < Min {$method['min_weight']}");
                }
                continue;
            }
            if (!empty($method['max_weight']) && (float)$method['max_weight'] > 0 && $weight > (float)$method['max_weight']) {
                if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                    error_log("Checkout Filter: Excluded '{$method['name']}' - Weight $weight > Max {$method['max_weight']}");
                }
                continue;
            }

            // Check Price Condition Limits
            $type = $method['type'] ?? 'flat_rate';
            
            if ($type === 'price_based' || $type === 'free_shipping') {
                 if (!empty($method['condition_min']) && $total < (float)$method['condition_min']) {
                     continue;
                 }
                 if (!empty($method['condition_max']) && (float)$method['condition_max'] > 0 && $total > (float)$method['condition_max']) {
                     continue;
                 }
            } elseif ($type === 'weight_based') {
                 if (!empty($method['condition_min']) && $weight < (float)$method['condition_min']) {
                     continue;
                 }
                 if (!empty($method['condition_max']) && (float)$method['condition_max'] > 0 && $weight > (float)$method['condition_max']) {
                     continue;
                 }
            }

            // Parse COD Settings
            $method['cod_enabled'] = true; // Default
            $method['cod_limit'] = 0; // 0 means no limit

            if (!empty($method['cod_settings'])) {
                $codSettings = is_string($method['cod_settings']) 
                    ? json_decode($method['cod_settings'], true) 
                    : $method['cod_settings'];
                
                if (is_array($codSettings)) {
                    // Check if strictly disabled
                    if (isset($codSettings['enable_cod']) && (int)$codSettings['enable_cod'] === 0) {
                        $method['cod_enabled'] = false;
                    }
                    
                    // Check max amount
                    if (!empty($codSettings['max_cod_amount'])) {
                        $method['cod_limit'] = (float)$codSettings['max_cod_amount'];
                    }
                }
            }

            $validMethods[] = $method;
        }

        return $validMethods;
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
        
        // Validate and Calculate Shipping
        $shippingMethodId = (int)($_POST['shipping_method_id'] ?? 0);
        $shippingCost = 0;
        $shippingMethodName = 'Standard Delivery'; // Default name

        // Match Zone for the selected address
        require_once APP_PATH . '/services/ZoneMatcher.php';
        $zoneMatcher = new ZoneMatcher();
        $deliveryMethodModel = new DeliveryMethod();

        $matchedZone = $zoneMatcher->match($shippingAddress);
        
        // CRITICAL: Block order if no zone matches
        if (!$matchedZone) {
            Session::setFlash('error', 'Shipping is not available for this location. Please update your shipping address.');
            header('Location: ' . SITE_URL . '/checkout');
            exit;
        }
        
        $validMethods = $deliveryMethodModel->getMethodsByZone($matchedZone['id']);
        
        // CRITICAL: Block order if zone has no delivery methods configured
        if (empty($validMethods)) {
            Session::setFlash('error', 'No shipping methods are currently available for your location. Please contact support.');
            header('Location: ' . SITE_URL . '/checkout');
            exit;
        }
        
        $selectedMethod = null;
        
        // If user selected a method, verify it exists in valid methods
        if ($shippingMethodId > 0) {
            foreach ($validMethods as $method) {
                if ($method['id'] == $shippingMethodId) {
                    $selectedMethod = $method;
                    break;
                }
            }
            
            // CRITICAL: Block if selected method not in valid list (possible tampering)
            if (!$selectedMethod) {
                Session::setFlash('error', 'Invalid shipping method selected. Please choose a valid shipping option.');
                header('Location: ' . SITE_URL . '/checkout');
                exit;
            }
        } else {
            // No method selected - block order (don't auto-select)
            Session::setFlash('error', 'Please select a shipping method.');
            header('Location: ' . SITE_URL . '/checkout');
            exit;
        }
        
        $shippingCost = (float)$selectedMethod['cost'];
        $shippingMethodName = $selectedMethod['name'];
        
        // Logic for Free Shipping (if pricing_type is free)
        if (($selectedMethod['pricing_type'] ?? '') === 'free') {
            $shippingCost = 0;
        }
        
        // Validate Payment Method against Shipping Method Rules
        $paymentMethod = $_POST['payment_method'] ?? PAYMENT_METHOD_COD;
        
        if ($paymentMethod === PAYMENT_METHOD_COD) {
            // 1. Check Global Setting
            $settingsModel = new Settings();
            $globalCodEnabled = $settingsModel->get('payment_cod_enabled', 1);
            
            if (!$globalCodEnabled) {
                Session::setFlash('error', 'Cash on Delivery is currently disabled.');
                header('Location: ' . SITE_URL . '/checkout');
                exit;
            }

            // 2. Check Shipping Method Rules
            $codSettings = [];
            if (!empty($selectedMethod['cod_settings'])) {
                $codSettings = is_string($selectedMethod['cod_settings']) 
                    ? json_decode($selectedMethod['cod_settings'], true) 
                    : $selectedMethod['cod_settings'];
            }
            
            // Check if COD disabled for this method
            if (isset($codSettings['enable_cod']) && (int)$codSettings['enable_cod'] === 0) {
                Session::setFlash('error', 'Cash on Delivery is not available for the selected shipping method.');
                header('Location: ' . SITE_URL . '/checkout');
                exit;
            }
            
            // Check Max COD Amount
            $orderTotal = ($total['total'] ?? 0) + $shippingCost;
            if (!empty($codSettings['max_cod_amount'])) {
                $maxCod = (float)$codSettings['max_cod_amount'];
                if ($maxCod > 0 && $orderTotal > $maxCod) {
                    Session::setFlash('error', 'Order total exceeds the maximum limit (₹' . number_format($maxCod) . ') for Cash on Delivery via ' . $shippingMethodName);
                    header('Location: ' . SITE_URL . '/checkout');
                    exit;
                }
            }
        }

        $finalAmount = ($total['total'] ?? 0) + $shippingCost;
        
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
            
            // Send Notification to Admins via Event Service
            if (file_exists(APP_PATH . '/services/EventService.php')) {
                require_once APP_PATH . '/services/EventService.php';
                $eventService = new EventService();
                
                // Order Placed
                $eventService->dispatch(EventService::EVENT_ORDER_PLACED, [
                    'user_id' => $userId,
                    'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                    'order_id' => $result['order_id'],
                    'order_number' => $result['order_number'],
                    'amount' => $finalAmount,
                    'payment_method' => $orderData['payment_method'] // Pass payment context
                ]);
            }

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

