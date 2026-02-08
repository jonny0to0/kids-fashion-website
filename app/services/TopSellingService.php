<?php

class TopSellingService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Recalculate sales stats for a specific product
     * Rule: Last 30 days, Completed/Delivered/Paid orders only
     */
    public function updateProductSales($productId) {
        // Clear existing entry for this product and period (we focus on 30_days default)
        // actually we should upsert.
        
        // Calculate Quantity and Revenue
        $sql = "SELECT 
                    SUM(oi.quantity) as total_qty, 
                    SUM(oi.total) as total_rev,
                    MAX(o.created_at) as last_sale
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                WHERE oi.product_id = :product_id
                AND o.order_status IN ('confirmed', 'processing', 'shipped', 'delivered', 'completed')
                AND (o.payment_status = 'paid' OR o.payment_method = 'cod')
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalQty = $result['total_qty'] ?? 0;
        $totalRev = $result['total_rev'] ?? 0.00;
        $lastSale = $result['last_sale'];

        // Upsert into summary table
        $upsertSql = "INSERT INTO product_sales_summary 
                      (product_id, total_quantity, total_revenue, last_sale_date, calculation_period)
                      VALUES (:pid, :qty, :rev, :last_sale, '30_days')
                      ON DUPLICATE KEY UPDATE 
                      total_quantity = VALUES(total_quantity),
                      total_revenue = VALUES(total_revenue),
                      last_sale_date = VALUES(last_sale_date),
                      last_updated = CURRENT_TIMESTAMP";
        
        $stmtUpsert = $this->db->prepare($upsertSql);
        $stmtUpsert->execute([
            ':pid' => $productId,
            ':qty' => $totalQty,
            ':rev' => $totalRev,
            ':last_sale' => $lastSale
        ]);
        
        return true;
    }

    /**
     * Trigger update for all products in an order
     */
    public function updateForOrder($orderId) {
        $stmt = $this->db->prepare("SELECT DISTINCT product_id FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $products = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($products as $productId) {
            $this->updateProductSales($productId);
        }
    }

    /**
     * Recalculate ALL products (Admin action)
     * Limit to active products to save resources
     */
    public function recalculateAll() {
        // Get all active product IDs
        $stmt = $this->db->query("SELECT product_id FROM products WHERE status = 'active'");
        $products = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $count = 0;
        foreach ($products as $productId) {
            $this->updateProductSales($productId);
            $count++;
        }
        return $count;
    }

    /**
     * Get Top Selling Products
     * Rules: Total Quantity Desc, Revenue Desc, Latest Sale Desc
     */
    public function getTopSellingProducts($limit = 8) {
        $sql = "SELECT p.*, s.total_quantity, s.total_revenue,
                    (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                    (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as rating,
                    (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as review_count
                FROM products p
                JOIN product_sales_summary s ON p.product_id = s.product_id
                WHERE p.status = 'active'
                AND s.is_excluded = 0
                AND s.total_quantity > 0
                ORDER BY 
                    s.is_pinned DESC,
                    s.manual_rank ASC,
                    s.total_quantity DESC, 
                    s.total_revenue DESC, 
                    s.last_sale_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Admin: Exclude Product
     */
    public function setExcluded($productId, $excluded = true) {
        // Ensure record exists
        $this->ensureSummaryRecord($productId);
        
        $sql = "UPDATE product_sales_summary SET is_excluded = :excluded WHERE product_id = :pid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':excluded' => $excluded ? 1 : 0, ':pid' => $productId]);
    }
    
    /**
     * Ensure a record exists in summary table (for pinning/excluding even if no sales)
     */
    private function ensureSummaryRecord($productId) {
        $sql = "INSERT IGNORE INTO product_sales_summary (product_id) VALUES (:pid)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pid' => $productId]);
    }
}
