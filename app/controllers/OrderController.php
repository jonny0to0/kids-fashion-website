<?php
/**
 * Order Controller
 * Handles order management
 */

class OrderController {
    private $orderModel;
    
    public function __construct() {
        $this->orderModel = new Order();
    }
    
    /**
     * Order confirmation
     */
    public function confirmation($orderNumber) {
        if (!Session::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }
        
        $userId = Session::getUserId();
        $order = $this->orderModel->findByOrderNumber($orderNumber);
        
        if (!$order || $order['user_id'] != $userId) {
            http_response_code(404);
            require_once VIEW_PATH . '/errors/404.php';
            return;
        }
        
        $items = $this->orderModel->getOrderItems($order['order_id']);
        
        $this->render('order/confirmation', [
            'order' => $order,
            'items' => $items
        ]);
    }
    
    /**
     * User orders list
     */
    public function index() {
        if (!Session::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }
        
        $userId = Session::getUserId();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $orders = $this->orderModel->getUserOrders($userId, $page);
        
        $this->render('order/index', [
            'orders' => $orders
        ]);
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

