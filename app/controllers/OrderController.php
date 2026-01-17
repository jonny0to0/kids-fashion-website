<?php
/**
 * Order Controller
 * Handles order management
 */

class OrderController
{
    private $orderModel;
    private $addressModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->addressModel = new Address();
    }

    /**
     * Order confirmation
     */
    public function confirmation($orderNumber)
    {
        if (!Session::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }

        // Restrict access to customers only
        if (Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. This feature is only available for customers.');
            header('Location: ' . SITE_URL . '/admin');
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
    public function index()
    {
        if (!Session::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }

        // Restrict access to customers only
        if (Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. This feature is only available for customers.');
            header('Location: ' . SITE_URL . '/admin');
            exit;
        }

        $userId = Session::getUserId();
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $orders = $this->orderModel->getUserOrders($userId, $page);

        $this->render('order/index', [
            'orders' => $orders
        ]);
    }

    /**
     * View order details
     */
    public function detail($orderNumber)
    {
        if (!Session::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }

        // Restrict access to customers only
        if (Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. This feature is only available for customers.');
            header('Location: ' . SITE_URL . '/admin');
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
        $shippingAddress = $this->addressModel->find($order['shipping_address_id']);
        $billingAddress = $this->addressModel->find($order['billing_address_id']);

        // Check for existing reviews
        require_once APP_PATH . '/models/Review.php';
        $reviewModel = new Review();

        foreach ($items as &$item) {
            $existingReview = $reviewModel->findOne([
                'user_id' => $userId,
                'product_id' => $item['product_id'],
                'order_id' => $order['order_id']
            ]);
            $item['has_reviewed'] = !empty($existingReview);
            $item['review_id'] = $existingReview['review_id'] ?? null;
        }
        unset($item); // Break reference

        $this->render('order/detail', [
            'order' => $order,
            'items' => $items,
            'shippingAddress' => $shippingAddress,
            'billingAddress' => $billingAddress
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel($orderNumber)
    {
        if (!Session::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }

        // Restrict access to customers only
        if (Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. This feature is only available for customers.');
            header('Location: ' . SITE_URL . '/admin');
            exit;
        }

        $userId = Session::getUserId();
        $order = $this->orderModel->findByOrderNumber($orderNumber);

        if (!$order || $order['user_id'] != $userId) {
            http_response_code(404);
            require_once VIEW_PATH . '/errors/404.php';
            return;
        }

        // Check if order can be cancelled (only pending or confirmed orders)
        if ($order['order_status'] !== ORDER_STATUS_PENDING && $order['order_status'] !== ORDER_STATUS_CONFIRMED) {
            Session::setFlash('error', 'This order cannot be cancelled. Only pending or confirmed orders can be cancelled.');
            header('Location: ' . SITE_URL . '/order');
            exit;
        }

        // Update order status to cancelled
        $this->orderModel->updateStatus($order['order_id'], ORDER_STATUS_CANCELLED);

        Session::setFlash('success', 'Order #' . $orderNumber . ' has been cancelled successfully.');
        header('Location: ' . SITE_URL . '/order');
        exit;
    }

    /**
     * Delete cancelled order
     */
    public function delete($orderNumber)
    {
        if (!Session::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/user/login');
            exit;
        }

        // Restrict access to customers only
        if (Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. This feature is only available for customers.');
            header('Location: ' . SITE_URL . '/admin');
            exit;
        }

        $userId = Session::getUserId();
        $order = $this->orderModel->findByOrderNumber($orderNumber);

        if (!$order || $order['user_id'] != $userId) {
            http_response_code(404);
            require_once VIEW_PATH . '/errors/404.php';
            return;
        }

        // Only allow deletion of cancelled orders
        if ($order['order_status'] !== ORDER_STATUS_CANCELLED) {
            Session::setFlash('error', 'Only cancelled orders can be permanently deleted.');
            header('Location: ' . SITE_URL . '/order');
            exit;
        }

        // Delete the order (order_items will be cascade deleted due to foreign key constraint)
        $this->orderModel->delete($order['order_id']);

        Session::setFlash('success', 'Order #' . $orderNumber . ' has been permanently deleted from your order history.');
        header('Location: ' . SITE_URL . '/order');
        exit;
    }

    /**
     * Render view
     */
    private function render($view, $data = [])
    {
        extract($data);
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/' . $view . '.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }
}

