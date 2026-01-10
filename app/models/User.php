<?php
/**
 * User Model
 * Handles user-related database operations
 */

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    
    /**
     * Register new user
     */
    public function register($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($email, $password) {
        $user = $this->findOne(['email' => $email, 'status' => USER_STATUS_ACTIVE]);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        // Don't allow password update through this method
        unset($data['password']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($userId, $data);
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }
        
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        
        return ($result['total'] ?? 0) > 0;
    }
    
    /**
     * Get user by email
     */
    public function findByEmail($email) {
        return $this->findOne(['email' => $email]);
    }
    
    /**
     * Get customer growth data (new registrations over time)
     */
    public function getCustomerGrowth($days = 30, $groupBy = 'day') {
        try {
            $interval = $groupBy === 'week' ? 'WEEK' : ($groupBy === 'month' ? 'MONTH' : 'DAY');
            
            $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as new_customers
                    FROM {$this->table}
                    WHERE user_type = ? 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            
            $stmt = $this->query($sql, [USER_TYPE_CUSTOMER, $days]);
            $result = $stmt->fetchAll();
            return $result !== false ? $result : [];
        } catch (Exception $e) {
            error_log("Error in getCustomerGrowth: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get customer segmentation data
     */
    public function getCustomerSegmentation($days = 30) {
        try {
            // New vs Returning customers
            $sql = "SELECT 
                    CASE 
                        WHEN order_count = 0 THEN 'No Orders'
                        WHEN order_count = 1 THEN 'New'
                        ELSE 'Returning'
                    END as customer_type,
                    COUNT(*) as customer_count
                    FROM (
                        SELECT u.user_id, COUNT(DISTINCT o.order_id) as order_count
                        FROM {$this->table} u
                        LEFT JOIN orders o ON u.user_id = o.user_id 
                            AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                            AND o.order_status != ?
                        WHERE u.user_type = ?
                        GROUP BY u.user_id
                    ) as customer_orders
                    GROUP BY customer_type";
            
            $stmt = $this->query($sql, [$days, ORDER_STATUS_CANCELLED, USER_TYPE_CUSTOMER]);
            $result = $stmt->fetchAll();
            return $result !== false ? $result : [];
        } catch (Exception $e) {
            error_log("Error in getCustomerSegmentation: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get customer value metrics (AOV, CLV)
     */
    public function getCustomerValueMetrics($days = 30) {
        try {
            $sql = "SELECT 
                    u.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                    u.email,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COALESCE(SUM(o.final_amount), 0) as total_spent,
                    COALESCE(AVG(o.final_amount), 0) as avg_order_value,
                    COALESCE(SUM(o.final_amount) / GREATEST(COUNT(DISTINCT o.order_id), 1), 0) as customer_lifetime_value,
                    MAX(o.created_at) as last_order_date
                    FROM {$this->table} u
                    LEFT JOIN orders o ON u.user_id = o.user_id 
                        AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                        AND o.order_status != ?
                    WHERE u.user_type = ?
                    GROUP BY u.user_id, u.first_name, u.last_name, u.email
                    HAVING total_orders > 0
                    ORDER BY total_spent DESC";
            
            $stmt = $this->query($sql, [$days, ORDER_STATUS_CANCELLED, USER_TYPE_CUSTOMER]);
            $result = $stmt->fetchAll();
            return $result !== false ? $result : [];
        } catch (Exception $e) {
            error_log("Error in getCustomerValueMetrics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get repeat purchase and retention data
     */
    public function getRetentionMetrics($days = 30) {
        try {
            $sql = "SELECT 
                    COUNT(DISTINCT CASE WHEN order_count = 1 THEN user_id END) as one_time_buyers,
                    COUNT(DISTINCT CASE WHEN order_count > 1 THEN user_id END) as repeat_buyers,
                    COUNT(DISTINCT user_id) as total_customers
                    FROM (
                        SELECT u.user_id, COUNT(DISTINCT o.order_id) as order_count
                        FROM {$this->table} u
                        LEFT JOIN orders o ON u.user_id = o.user_id 
                            AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                            AND o.order_status != ?
                        WHERE u.user_type = ?
                        GROUP BY u.user_id
                    ) as customer_orders";
            
            $stmt = $this->query($sql, [$days, ORDER_STATUS_CANCELLED, USER_TYPE_CUSTOMER]);
            $result = $stmt->fetch();
            return $result !== false ? $result : ['one_time_buyers' => 0, 'repeat_buyers' => 0, 'total_customers' => 0];
        } catch (Exception $e) {
            error_log("Error in getRetentionMetrics: " . $e->getMessage());
            return ['one_time_buyers' => 0, 'repeat_buyers' => 0, 'total_customers' => 0];
        }
    }
    
    /**
     * Get top customers by spend
     */
    public function getTopCustomers($limit = 20, $days = 30) {
        try {
            $sql = "SELECT 
                    u.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                    u.email,
                    u.phone,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COALESCE(SUM(o.final_amount), 0) as total_spent,
                    COALESCE(AVG(o.final_amount), 0) as avg_order_value,
                    MAX(o.created_at) as last_order_date,
                    MIN(o.created_at) as first_order_date,
                    u.status,
                    u.created_at as registered_at
                    FROM {$this->table} u
                    LEFT JOIN orders o ON u.user_id = o.user_id 
                        AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                        AND o.order_status != ?
                    WHERE u.user_type = ?
                    GROUP BY u.user_id, u.first_name, u.last_name, u.email, u.phone, u.status, u.created_at
                    HAVING total_orders > 0
                    ORDER BY total_spent DESC
                    LIMIT ?";
            
            $stmt = $this->query($sql, [$days, ORDER_STATUS_CANCELLED, USER_TYPE_CUSTOMER, $limit]);
            $result = $stmt->fetchAll();
            return $result !== false ? $result : [];
        } catch (Exception $e) {
            error_log("Error in getTopCustomers: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get customer summary metrics
     */
    public function getCustomerSummaryMetrics($days = 30) {
        try {
            $sql = "SELECT 
                    COUNT(DISTINCT u.user_id) as total_customers,
                    COUNT(DISTINCT CASE WHEN u.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) THEN u.user_id END) as new_customers,
                    COUNT(DISTINCT CASE WHEN u.status = ? THEN u.user_id END) as active_customers,
                    COUNT(DISTINCT CASE WHEN u.status = ? THEN u.user_id END) as inactive_customers,
                    COUNT(DISTINCT CASE WHEN o.order_id IS NOT NULL THEN u.user_id END) as customers_with_orders,
                    COUNT(DISTINCT CASE WHEN o.order_id IS NOT NULL AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) THEN u.user_id END) as returning_customers
                    FROM {$this->table} u
                    LEFT JOIN orders o ON u.user_id = o.user_id 
                        AND o.order_status != ?
                    WHERE u.user_type = ?";
            
            $stmt = $this->query($sql, [
                $days, 
                USER_STATUS_ACTIVE, 
                USER_STATUS_SUSPENDED, 
                $days, 
                ORDER_STATUS_CANCELLED, 
                USER_TYPE_CUSTOMER
            ]);
            $result = $stmt->fetch();
            return $result !== false ? $result : [
                'total_customers' => 0,
                'new_customers' => 0,
                'active_customers' => 0,
                'inactive_customers' => 0,
                'customers_with_orders' => 0,
                'returning_customers' => 0
            ];
        } catch (Exception $e) {
            error_log("Error in getCustomerSummaryMetrics: " . $e->getMessage());
            return [
                'total_customers' => 0,
                'new_customers' => 0,
                'active_customers' => 0,
                'inactive_customers' => 0,
                'customers_with_orders' => 0,
                'returning_customers' => 0
            ];
        }
    }
    
    /**
     * Get customers with filters for detailed table
     */
    public function getCustomersWithFilters($filters = [], $page = 1, $perPage = 20) {
        try {
            $offset = ($page - 1) * $perPage;
            $where = ["u.user_type = ?"];
            $params = [USER_TYPE_CUSTOMER];
            
            // Date range filter
            if (!empty($filters['date_from'])) {
                $where[] = "u.created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where[] = "u.created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
            }
            
            // Customer type filter (new vs returning)
            if (!empty($filters['customer_type'])) {
                if ($filters['customer_type'] === 'new') {
                    $where[] = "NOT EXISTS (SELECT 1 FROM orders o2 WHERE o2.user_id = u.user_id AND o2.order_status != ?)";
                    $params[] = ORDER_STATUS_CANCELLED;
                } elseif ($filters['customer_type'] === 'returning') {
                    $where[] = "EXISTS (SELECT 1 FROM orders o2 WHERE o2.user_id = u.user_id AND o2.order_status != ?)";
                    $params[] = ORDER_STATUS_CANCELLED;
                }
            }
            
            // Status filter
            if (!empty($filters['status'])) {
                $where[] = "u.status = ?";
                $params[] = $filters['status'];
            }
            
            // Search filter
            if (!empty($filters['search'])) {
                $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "SELECT 
                    u.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                    u.email,
                    u.phone,
                    u.status,
                    u.created_at as registered_at,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    COALESCE(SUM(o.final_amount), 0) as total_spent,
                    COALESCE(AVG(o.final_amount), 0) as avg_order_value,
                    MAX(o.created_at) as last_order_date
                    FROM {$this->table} u
                    LEFT JOIN orders o ON u.user_id = o.user_id 
                        AND o.order_status != ?
                    WHERE {$whereClause}
                    GROUP BY u.user_id, u.first_name, u.last_name, u.email, u.phone, u.status, u.created_at
                    ORDER BY total_spent DESC, u.created_at DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = ORDER_STATUS_CANCELLED;
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->query($sql, $params);
            $result = $stmt->fetchAll();
            return $result !== false ? $result : [];
        } catch (Exception $e) {
            error_log("Error in getCustomersWithFilters: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total count of customers with filters
     */
    public function getCustomersCountWithFilters($filters = []) {
        try {
            $where = ["u.user_type = ?"];
            $params = [USER_TYPE_CUSTOMER];
            
            // Same filters as getCustomersWithFilters
            if (!empty($filters['date_from'])) {
                $where[] = "u.created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where[] = "u.created_at <= ?";
                $params[] = $filters['date_to'] . ' 23:59:59';
            }
            
            if (!empty($filters['customer_type'])) {
                if ($filters['customer_type'] === 'new') {
                    $where[] = "NOT EXISTS (SELECT 1 FROM orders o2 WHERE o2.user_id = u.user_id AND o2.order_status != ?)";
                    $params[] = ORDER_STATUS_CANCELLED;
                } elseif ($filters['customer_type'] === 'returning') {
                    $where[] = "EXISTS (SELECT 1 FROM orders o2 WHERE o2.user_id = u.user_id AND o2.order_status != ?)";
                    $params[] = ORDER_STATUS_CANCELLED;
                }
            }
            
            if (!empty($filters['status'])) {
                $where[] = "u.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['search'])) {
                $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "SELECT COUNT(DISTINCT u.user_id) as total
                    FROM {$this->table} u
                    WHERE {$whereClause}";
            
            $stmt = $this->query($sql, $params);
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getCustomersCountWithFilters: " . $e->getMessage());
            return 0;
        }
    }
}

