<?php
/**
 * Order Model
 * Handles order-related database operations
 */

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';

    /**
     * Create new order
     */
    public function createOrder($data)
    {
        $orderNumber = $this->generateOrderNumber();

        $orderData = [
            'user_id' => $data['user_id'],
            'order_number' => $orderNumber,
            'total_amount' => $data['total_amount'],
            'discount_amount' => $data['discount_amount'] ?? 0,
            'shipping_amount' => $data['shipping_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'final_amount' => $data['final_amount'],
            'payment_method' => $data['payment_method'],
            'payment_status' => $data['payment_status'] ?? PAYMENT_STATUS_PENDING,
            'order_status' => $data['order_status'] ?? ORDER_STATUS_PENDING,
            'shipping_address_id' => $data['shipping_address_id'],
            'billing_address_id' => $data['billing_address_id'],
            'notes' => $data['notes'] ?? null
        ];

        $orderId = $this->create($orderData);

        // Add order items
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->addOrderItem($orderId, $item);
            }
        }

        return ['order_id' => $orderId, 'order_number' => $orderNumber];
    }

    /**
     * Add order item
     */
    public function addOrderItem($orderId, $item)
    {
        $sql = "INSERT INTO order_items (order_id, product_id, variant_id, product_name, product_image, quantity, price, discount, tax, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->query($sql, [
            $orderId,
            $item['product_id'],
            $item['variant_id'] ?? null,
            $item['product_name'],
            $item['product_image'] ?? null,
            $item['quantity'],
            $item['price'],
            $item['discount'] ?? 0,
            $item['tax'] ?? 0,
            $item['total']
        ]);
    }

    /**
     * Get order by order number
     */
    public function findByOrderNumber($orderNumber)
    {
        return $this->findOne(['order_number' => $orderNumber]);
    }

    /**
     * Get user orders
     */
    /**
     * Get user orders
     */
    public function getUserOrders($userId, $page = 1, $perPage = 10)
    {
        return $this->getUserOrdersFiltered($userId, [], $page, $perPage);
    }

    /**
     * Get user orders with filters
     */
    public function getUserOrdersFiltered($userId, $filters = [], $page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        $params = [$userId];
        $where = ["o.user_id = ?"];

        // Join necessary for product search
        $join = "LEFT JOIN order_items oi ON o.order_id = oi.order_id";

        // Search Filter
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = "(o.order_number LIKE ? OR oi.product_name LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Status Filter
        if (!empty($filters['status']) && is_array($filters['status'])) {
            $statusPlaceholders = implode(',', array_fill(0, count($filters['status']), '?'));
            $where[] = "o.order_status IN ($statusPlaceholders)";
            foreach ($filters['status'] as $status) {
                $params[] = $status;
            }
        }

        // Time Filter
        if (!empty($filters['time'])) {
            switch ($filters['time']) {
                case 'last_30_days':
                    $where[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case '2025':
                    $where[] = "YEAR(o.created_at) = 2025";
                    break;
                case '2024':
                    $where[] = "YEAR(o.created_at) = 2024";
                    break;
                case 'older':
                    $where[] = "YEAR(o.created_at) < 2024";
                    break;
            }
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT DISTINCT o.* FROM {$this->table} o 
                $join 
                WHERE $whereClause 
                ORDER BY o.created_at DESC 
                LIMIT ? OFFSET ?";

        // Add pagination params
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get count of user orders with filters
     */
    public function getUserOrdersFilteredCount($userId, $filters = [])
    {
        $params = [$userId];
        $where = ["o.user_id = ?"];

        // Join necessary for product search
        $join = "LEFT JOIN order_items oi ON o.order_id = oi.order_id";

        // Search Filter
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = "(o.order_number LIKE ? OR oi.product_name LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Status Filter
        if (!empty($filters['status']) && is_array($filters['status'])) {
            $statusPlaceholders = implode(',', array_fill(0, count($filters['status']), '?'));
            $where[] = "o.order_status IN ($statusPlaceholders)";
            foreach ($filters['status'] as $status) {
                $params[] = $status;
            }
        }

        // Time Filter
        if (!empty($filters['time'])) {
            switch ($filters['time']) {
                case 'last_30_days':
                    $where[] = "o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case '2025':
                    $where[] = "YEAR(o.created_at) = 2025";
                    break;
                case '2024':
                    $where[] = "YEAR(o.created_at) = 2024";
                    break;
                case 'older':
                    $where[] = "YEAR(o.created_at) < 2024";
                    break;
            }
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(DISTINCT o.order_id) as total FROM {$this->table} o 
                $join 
                WHERE $whereClause";

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get order items
     */
    public function getOrderItems($orderId)
    {
        $sql = "SELECT oi.*, p.slug 
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = ?
                ORDER BY oi.order_item_id ASC";

        try {
            $stmt = $this->query($sql, [$orderId]);
            $results = $stmt->fetchAll();
            return $results !== false ? $results : [];
        } catch (Exception $e) {
            error_log("Error fetching order items: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update order status
     */
    public function updateStatus($orderId, $status)
    {
        $data = ['order_status' => $status];

        if ($status === ORDER_STATUS_DELIVERED) {
            $data['delivered_at'] = date('Y-m-d H:i:s');
        }

        if ($status === ORDER_STATUS_CANCELLED) {
            $data['cancelled_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($orderId, $data);
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders($limit = 10)
    {
        $sql = "SELECT o.*, u.first_name, u.last_name, u.email 
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.user_id
                ORDER BY o.created_at DESC 
                LIMIT ?";

        $stmt = $this->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        do {
            $prefix = defined('ORDER_PREFIX') ? ORDER_PREFIX : 'ORD';
            $orderNumber = $prefix . date('Ymd') . strtoupper(substr(uniqid(), -6));
            $exists = $this->findOne(['order_number' => $orderNumber]);
        } while ($exists);

        return $orderNumber;
    }

    /**
     * Get all orders for admin (with filters, search, pagination)
     */
    public function getAllOrders($filters = [], $page = 1, $perPage = 15)
    {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        // Search by Order ID/Number
        if (!empty($filters['search'])) {
            $where[] = "(o.order_number LIKE ? OR o.order_id = ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = (int) $filters['search'];
        }

        // Filter by Order Status
        if (!empty($filters['order_status'])) {
            $where[] = "o.order_status = ?";
            $params[] = $filters['order_status'];
        }

        // Filter by Payment Status
        if (!empty($filters['payment_status'])) {
            $where[] = "o.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        // Filter by Date Range
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT o.*, 
                u.first_name, u.last_name, u.email, u.phone,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.user_id
                {$whereClause}
                ORDER BY o.created_at DESC 
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get total count of orders (for pagination)
     */
    public function getAllOrdersCount($filters = [])
    {
        $where = [];
        $params = [];

        // Search by Order ID/Number
        if (!empty($filters['search'])) {
            $where[] = "(o.order_number LIKE ? OR o.order_id = ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = (int) $filters['search'];
        }

        // Filter by Order Status
        if (!empty($filters['order_status'])) {
            $where[] = "o.order_status = ?";
            $params[] = $filters['order_status'];
        }

        // Filter by Payment Status
        if (!empty($filters['payment_status'])) {
            $where[] = "o.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        // Filter by Date Range
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} o
                {$whereClause}";

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get order with full details (for admin)
     */
    public function getOrderWithDetails($orderId)
    {
        $sql = "SELECT o.*, 
                u.first_name, u.last_name, u.email, u.phone,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                sa.address_line1 as shipping_address_line1,
                sa.address_line2 as shipping_address_line2,
                sa.city as shipping_city,
                sa.state as shipping_state,
                sa.pincode as shipping_pincode,
                sa.country as shipping_country,
                sa.full_name as shipping_full_name,
                sa.phone as shipping_phone,
                ba.address_line1 as billing_address_line1,
                ba.address_line2 as billing_address_line2,
                ba.city as billing_city,
                ba.state as billing_state,
                ba.pincode as billing_pincode,
                ba.country as billing_country,
                ba.full_name as billing_full_name,
                ba.phone as billing_phone
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN addresses sa ON o.shipping_address_id = sa.address_id
                LEFT JOIN addresses ba ON o.billing_address_id = ba.address_id
                WHERE o.order_id = ?";

        $stmt = $this->query($sql, [$orderId]);
        return $stmt->fetch();
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($orderId, $status, $changedBy = null)
    {
        $order = $this->find($orderId);
        if (!$order) {
            return false;
        }

        $oldStatus = $order['payment_status'];
        $data = ['payment_status' => $status];

        $result = $this->update($orderId, $data);

        if ($result && $oldStatus !== $status) {
            $this->logStatusChange($orderId, $changedBy, null, null, $oldStatus, $status);
        }

        return $result;
    }

    /**
     * Update order status with logging
     */
    public function updateOrderStatus($orderId, $status, $changedBy = null, $notes = null)
    {
        $order = $this->find($orderId);
        if (!$order) {
            return false;
        }

        $oldStatus = $order['order_status'];
        $data = ['order_status' => $status];

        if ($status === ORDER_STATUS_DELIVERED) {
            $data['delivered_at'] = date('Y-m-d H:i:s');
        }

        if ($status === ORDER_STATUS_CANCELLED) {
            $data['cancelled_at'] = date('Y-m-d H:i:s');
        }

        $result = $this->update($orderId, $data);

        if ($result && $oldStatus !== $status) {
            $this->logStatusChange($orderId, $changedBy, $oldStatus, $status, null, null, $notes);
        }

        return $result;
    }

    /**
     * Log status change
     */
    public function logStatusChange($orderId, $changedBy, $oldOrderStatus, $newOrderStatus, $oldPaymentStatus = null, $newPaymentStatus = null, $notes = null)
    {
        $sql = "INSERT INTO order_status_logs 
                (order_id, changed_by, old_status, new_status, old_payment_status, new_payment_status, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $this->query($sql, [
            $orderId,
            $changedBy,
            $oldOrderStatus,
            $newOrderStatus,
            $oldPaymentStatus,
            $newPaymentStatus,
            $notes
        ]);
    }

    /**
     * Get order status history
     */
    public function getStatusHistory($orderId)
    {
        $sql = "SELECT osl.*, 
                u.first_name, u.last_name, u.email,
                CONCAT(u.first_name, ' ', u.last_name) as changed_by_name
                FROM order_status_logs osl
                LEFT JOIN users u ON osl.changed_by = u.user_id
                WHERE osl.order_id = ?
                ORDER BY osl.created_at DESC";

        $stmt = $this->query($sql, [$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if status transition is valid
     */
    public function isValidStatusTransition($currentStatus, $newStatus)
    {
        $validTransitions = [
            ORDER_STATUS_PENDING => [ORDER_STATUS_CONFIRMED, ORDER_STATUS_CANCELLED],
            ORDER_STATUS_CONFIRMED => [ORDER_STATUS_PROCESSING, ORDER_STATUS_CANCELLED],
            ORDER_STATUS_PROCESSING => [ORDER_STATUS_SHIPPED, ORDER_STATUS_CANCELLED],
            ORDER_STATUS_SHIPPED => [ORDER_STATUS_DELIVERED, ORDER_STATUS_RETURNED],
            ORDER_STATUS_DELIVERED => [ORDER_STATUS_RETURNED],
            ORDER_STATUS_CANCELLED => [],
            ORDER_STATUS_RETURNED => []
        ];

        return isset($validTransitions[$currentStatus]) &&
            in_array($newStatus, $validTransitions[$currentStatus]);
    }

    /**
     * Get order analytics summary
     */
    public function getOrderAnalytics($filters = [])
    {
        $where = [];
        $params = [];

        // Apply same filters as getAllOrders
        if (!empty($filters['search'])) {
            $where[] = "(o.order_number LIKE ? OR o.order_id = ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = (int) $filters['search'];
        }

        if (!empty($filters['order_status'])) {
            $where[] = "o.order_status = ?";
            $params[] = $filters['order_status'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "o.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get totals for today, this week, this month
        $todayStart = date('Y-m-d 00:00:00');
        $weekStart = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $monthStart = date('Y-m-01 00:00:00');

        $sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN DATE(o.created_at) = CURDATE() THEN 1 ELSE 0 END) as orders_today,
                SUM(CASE WHEN DATE(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as orders_week,
                SUM(CASE WHEN DATE(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as orders_month,
                SUM(CASE WHEN o.order_status = ? THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN o.order_status = ? THEN 1 ELSE 0 END) as shipped_orders,
                SUM(CASE WHEN o.order_status = ? THEN 1 ELSE 0 END) as delivered_orders,
                SUM(CASE WHEN o.order_status IN (?, ?) THEN 1 ELSE 0 END) as cancelled_returned_orders,
                SUM(o.final_amount) as total_revenue,
                SUM(CASE WHEN DATE(o.created_at) = CURDATE() THEN o.final_amount ELSE 0 END) as revenue_today,
                SUM(CASE WHEN DATE(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN o.final_amount ELSE 0 END) as revenue_week,
                SUM(CASE WHEN DATE(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN o.final_amount ELSE 0 END) as revenue_month
                FROM {$this->table} o
                {$whereClause}";

        $analyticsParams = array_merge([
            ORDER_STATUS_PENDING,
            ORDER_STATUS_SHIPPED,
            ORDER_STATUS_DELIVERED,
            ORDER_STATUS_CANCELLED,
            ORDER_STATUS_RETURNED
        ], $params);

        $stmt = $this->query($sql, $analyticsParams);
        return $stmt->fetch();
    }

    /**
     * Get order items summary for an order
     */
    public function getOrderItemsSummary($orderId)
    {
        $sql = "SELECT COUNT(*) as item_count,
                GROUP_CONCAT(product_name SEPARATOR ', ') as product_names
                FROM order_items
                WHERE order_id = ?";

        $stmt = $this->query($sql, [$orderId]);
        return $stmt->fetch();
    }

    /**
     * Update shipping details
     */
    public function updateShippingDetails($orderId, $data)
    {
        $updateData = [];

        if (isset($data['tracking_id'])) {
            $updateData['tracking_id'] = $data['tracking_id'];
        }

        if (isset($data['courier_partner'])) {
            $updateData['courier_partner'] = $data['courier_partner'];
        }

        if (isset($data['estimated_delivery'])) {
            $updateData['estimated_delivery'] = $data['estimated_delivery'];
        }

        if (isset($data['delivery_type'])) {
            $updateData['delivery_type'] = $data['delivery_type'];
        }

        if (empty($updateData)) {
            return false;
        }

        return $this->update($orderId, $updateData);
    }

    /**
     * Get orders with enhanced filters (customer name, mobile, price range, delivery type)
     */
    public function getAllOrdersEnhanced($filters = [], $page = 1, $perPage = 15)
    {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        // Search by Order ID/Number
        if (!empty($filters['search'])) {
            $where[] = "(o.order_number LIKE ? OR o.order_id = ? OR o.tracking_id LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = (int) $filters['search'];
            $params[] = $searchTerm;
        }

        // Search by customer name
        if (!empty($filters['customer_name'])) {
            $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
            $nameTerm = '%' . $filters['customer_name'] . '%';
            $params[] = $nameTerm;
            $params[] = $nameTerm;
            $params[] = $nameTerm;
        }

        // Search by mobile
        if (!empty($filters['mobile'])) {
            $where[] = "u.phone LIKE ?";
            $params[] = '%' . $filters['mobile'] . '%';
        }

        // Filter by Order Status
        if (!empty($filters['order_status'])) {
            $where[] = "o.order_status = ?";
            $params[] = $filters['order_status'];
        }

        // Filter by Payment Status
        if (!empty($filters['payment_status'])) {
            $where[] = "o.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        // Filter by Payment Method
        if (!empty($filters['payment_method'])) {
            $where[] = "o.payment_method = ?";
            $params[] = $filters['payment_method'];
        }

        // Filter by Delivery Type
        if (!empty($filters['delivery_type'])) {
            $where[] = "o.delivery_type = ?";
            $params[] = $filters['delivery_type'];
        }

        // Filter by Price Range
        if (!empty($filters['price_min'])) {
            $where[] = "o.final_amount >= ?";
            $params[] = (float) $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $where[] = "o.final_amount <= ?";
            $params[] = (float) $filters['price_max'];
        }

        // Filter by Date Range
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get order items count for each order
        $sql = "SELECT o.*, 
                u.first_name, u.last_name, u.email, u.phone,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.user_id
                {$whereClause}
                ORDER BY o.created_at DESC 
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get total count with enhanced filters
     */
    public function getAllOrdersCountEnhanced($filters = [])
    {
        $where = [];
        $params = [];

        // Same filters as getAllOrdersEnhanced
        if (!empty($filters['search'])) {
            $where[] = "(o.order_number LIKE ? OR o.order_id = ? OR o.tracking_id LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = (int) $filters['search'];
            $params[] = $searchTerm;
        }

        if (!empty($filters['customer_name'])) {
            $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
            $nameTerm = '%' . $filters['customer_name'] . '%';
            $params[] = $nameTerm;
            $params[] = $nameTerm;
            $params[] = $nameTerm;
        }

        if (!empty($filters['mobile'])) {
            $where[] = "u.phone LIKE ?";
            $params[] = '%' . $filters['mobile'] . '%';
        }

        if (!empty($filters['order_status'])) {
            $where[] = "o.order_status = ?";
            $params[] = $filters['order_status'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "o.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['payment_method'])) {
            $where[] = "o.payment_method = ?";
            $params[] = $filters['payment_method'];
        }

        if (!empty($filters['delivery_type'])) {
            $where[] = "o.delivery_type = ?";
            $params[] = $filters['delivery_type'];
        }

        if (!empty($filters['price_min'])) {
            $where[] = "o.final_amount >= ?";
            $params[] = (float) $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $where[] = "o.final_amount <= ?";
            $params[] = (float) $filters['price_max'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.user_id
                {$whereClause}";

        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}

