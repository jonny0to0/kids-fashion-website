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
        // Allow both active and suspended users to login
        // Deactivated users will be blocked
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND status IN (?, ?)";
        $stmt = $this->query($sql, [$email, USER_STATUS_ACTIVE, USER_STATUS_SUSPENDED]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Check user status
     */
    public function checkStatus($userId) {
        $user = $this->find($userId);
        return $user ? $user['status'] : false;
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
    /**
     * Get customer growth data (new registrations and cumulative total over time)
     */
    public function getCustomerGrowth($filtersOrDays = 30) {
        try {
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $endDate = date('Y-m-d') . ' 23:59:59';
            
            if (is_array($filtersOrDays)) {
                if (!empty($filtersOrDays['date_from'])) {
                    $startDate = $filtersOrDays['date_from'];
                }
                if (!empty($filtersOrDays['date_to'])) {
                    $endDate = $filtersOrDays['date_to'] . ' 23:59:59';
                }
            } elseif (is_numeric($filtersOrDays)) {
                $startDate = date('Y-m-d', strtotime("-{$filtersOrDays} days"));
            }

            // 1. Get total customers count BEFORE the start date
            $sqlBase = "SELECT COUNT(*) as total FROM {$this->table} 
                        WHERE user_type = ? AND created_at < ?";
            $stmtBase = $this->query($sqlBase, [USER_TYPE_CUSTOMER, $startDate]);
            $baseTotal = $stmtBase->fetch(PDO::FETCH_OBJ)->total ?? 0;

            // 2. Get new customers per day within the range
            $sqlGrowth = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as new_customers
                    FROM {$this->table}
                    WHERE user_type = ? 
                    AND created_at >= ? AND created_at <= ?
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            
            $stmtGrowth = $this->query($sqlGrowth, [USER_TYPE_CUSTOMER, $startDate, $endDate]);
            $dailyGrowth = $stmtGrowth->fetchAll(PDO::FETCH_ASSOC);

            // 3. Calculate cumulative total execution
            $result = [];
            $currentTotal = $baseTotal;
            
            // Check if we have missing dates and fill them? 
            // For now, let's just stick to dates with activity or maybe better fill gaps?
            // To be robust, let's fill gaps if needed, but for now let's iterate.
            
            // Note: If no customers joined on a specific day, the line chart might look jumpy.
            // Better to fetch all days in range. However, for simplicity and common DB patterns:
            
            foreach ($dailyGrowth as $day) {
                $currentTotal += $day['new_customers'];
                $result[] = [
                    'date' => $day['date'],
                    'new_customers' => $day['new_customers'],
                    'total_customers' => $currentTotal
                ];
            }
            
            return $result;
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
    public function getRetentionMetrics($filtersOrDays = 30) {
        try {
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $endDate = date('Y-m-d') . ' 23:59:59';
            
            if (is_array($filtersOrDays)) {
                if (!empty($filtersOrDays['date_from'])) {
                    $startDate = $filtersOrDays['date_from'];
                }
                if (!empty($filtersOrDays['date_to'])) {
                    $endDate = $filtersOrDays['date_to'] . ' 23:59:59';
                }
            } elseif (is_numeric($filtersOrDays)) {
                $startDate = date('Y-m-d', strtotime("-{$filtersOrDays} days"));
            }
            // If it's a string "custom" with no array, we fallback to defaults (last 30 days) 
            // implicitly via initialization above, preventing SQL error.

            $sql = "SELECT 
                    COUNT(DISTINCT CASE WHEN order_count = 1 THEN user_id END) as one_time_buyers,
                    COUNT(DISTINCT CASE WHEN order_count > 1 THEN user_id END) as repeat_buyers,
                    COUNT(DISTINCT user_id) as total_customers
                    FROM (
                        SELECT u.user_id, COUNT(DISTINCT o.order_id) as order_count
                        FROM {$this->table} u
                        LEFT JOIN orders o ON u.user_id = o.user_id 
                            AND o.created_at >= ? AND o.created_at <= ?
                            AND o.order_status != ?
                        WHERE u.user_type = ?
                        GROUP BY u.user_id
                    ) as customer_orders";
            
            $stmt = $this->query($sql, [$startDate, $endDate, ORDER_STATUS_CANCELLED, USER_TYPE_CUSTOMER]);
            $result = $stmt->fetch();
            return $result !== false ? $result : ['one_time_buyers' => 0, 'repeat_buyers' => 0, 'total_customers' => 0];
        } catch (Exception $e) {
            error_log("Error in getRetentionMetrics: " . $e->getMessage());
            return ['one_time_buyers' => 0, 'repeat_buyers' => 0, 'total_customers' => 0];
        }
    }

    /**
     * Get retention trend data (New vs Returning orders over time)
     */
    public function getRetentionTrend($filtersOrDays = 30) {
        try {
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $endDate = date('Y-m-d') . ' 23:59:59';
            
            if (is_array($filtersOrDays)) {
                if (!empty($filtersOrDays['date_from'])) {
                    $startDate = $filtersOrDays['date_from'];
                }
                if (!empty($filtersOrDays['date_to'])) {
                    $endDate = $filtersOrDays['date_to'] . ' 23:59:59';
                }
            } elseif (is_numeric($filtersOrDays)) {
                $startDate = date('Y-m-d', strtotime("-{$filtersOrDays} days"));
            }

            $sql = "SELECT 
                    DATE(o.created_at) as date,
                    COUNT(DISTINCT CASE WHEN o.order_id = first_orders.first_order_id THEN o.order_id END) as new_customer_orders,
                    COUNT(DISTINCT CASE WHEN o.order_id != first_orders.first_order_id THEN o.order_id END) as returning_customer_orders
                    FROM orders o
                    JOIN (
                        SELECT user_id, MIN(order_id) as first_order_id
                        FROM orders
                        WHERE order_status != ?
                        GROUP BY user_id
                    ) first_orders ON o.user_id = first_orders.user_id
                    WHERE o.created_at >= ? AND o.created_at <= ?
                    AND o.order_status != ?
                    GROUP BY DATE(o.created_at)
                    ORDER BY date ASC";

            $stmt = $this->query($sql, [ORDER_STATUS_CANCELLED, $startDate, $endDate, ORDER_STATUS_CANCELLED]);
            $result = $stmt->fetchAll();
            return $result !== false ? $result : [];
        } catch (Exception $e) {
            error_log("Error in getRetentionTrend: " . $e->getMessage());
            return [];
        }
    }
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
                $where[] = "(u.created_at >= ? OR EXISTS (SELECT 1 FROM orders o WHERE o.user_id = u.user_id AND o.created_at >= ?))";
                $params[] = $filters['date_from'];
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where[] = "(u.created_at <= ? OR EXISTS (SELECT 1 FROM orders o WHERE o.user_id = u.user_id AND o.created_at <= ?))";
                $endDate = $filters['date_to'] . ' 23:59:59';
                $params[] = $endDate;
                $params[] = $endDate;
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
            
            // Fix: Merge params in correct order (JOIN params, then WHERE params, then LIMIT params)
            $joinParams = [ORDER_STATUS_CANCELLED];
            $limitParams = [$perPage, $offset];
            
            $allParams = array_merge($joinParams, $params, $limitParams);
            
            $stmt = $this->query($sql, $allParams);
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
                $where[] = "(u.created_at >= ? OR EXISTS (SELECT 1 FROM orders o WHERE o.user_id = u.user_id AND o.created_at >= ?))";
                $params[] = $filters['date_from'];
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $where[] = "(u.created_at <= ? OR EXISTS (SELECT 1 FROM orders o WHERE o.user_id = u.user_id AND o.created_at <= ?))";
                $endDate = $filters['date_to'] . ' 23:59:59';
                $params[] = $endDate;
                $params[] = $endDate;
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
    /**
     * Get status history for a user
     */
    public function getStatusHistory($userId) {
        $sql = "SELECT sh.*, 
                CONCAT(a.first_name, ' ', a.last_name) as admin_name
                FROM user_status_history sh
                LEFT JOIN users a ON sh.suspended_by_admin_id = a.user_id
                WHERE sh.user_id = ?
                ORDER BY sh.created_at DESC";
        
        $stmt = $this->query($sql, [$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Add status history record
     */
    public function addStatusHistory($data) {
        $sql = "INSERT INTO user_status_history (
            user_id, old_status, new_status, reason_code, 
            reason_text, suspended_by_admin_id, evidence_reference, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $data['user_id'],
            $data['old_status'],
            $data['new_status'],
            $data['reason_code'] ?? null,
            $data['reason_text'] ?? null,
            $data['suspended_by_admin_id'] ?? null,
            $data['evidence_reference'] ?? null
        ];
        
        return $this->query($sql, $params);
    }
}

