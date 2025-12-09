<?php
/**
 * Order Model
 * Handles order-related database operations
 */

class Order extends Model {
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    
    /**
     * Create new order
     */
    public function createOrder($data) {
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
    public function addOrderItem($orderId, $item) {
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
    public function findByOrderNumber($orderNumber) {
        return $this->findOne(['order_number' => $orderNumber]);
    }
    
    /**
     * Get user orders
     */
    public function getUserOrders($userId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->query($sql, [$userId, $perPage, $offset]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get order items
     */
    public function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.slug 
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = ?";
        
        $stmt = $this->query($sql, [$orderId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update order status
     */
    public function updateStatus($orderId, $status) {
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
    public function getRecentOrders($limit = 10) {
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
    private function generateOrderNumber() {
        do {
            $prefix = defined('ORDER_PREFIX') ? ORDER_PREFIX : 'ORD';
            $orderNumber = $prefix . date('Ymd') . strtoupper(substr(uniqid(), -6));
            $exists = $this->findOne(['order_number' => $orderNumber]);
        } while ($exists);
        
        return $orderNumber;
    }
}

