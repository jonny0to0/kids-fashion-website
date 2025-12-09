<?php
/**
 * Product Model
 * Handles product-related database operations
 */

class Product extends Model {
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    
    /**
     * Get products with pagination
     */
    public function getProducts($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, c.name as category_name, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.status = ?";
        
        $params = [PRODUCT_STATUS_ACTIVE];
        
        // Apply filters
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['age_group'])) {
            $sql .= " AND p.age_group = ?";
            $params[] = $filters['age_group'];
        }
        
        if (!empty($filters['gender'])) {
            $sql .= " AND p.gender = ?";
            $params[] = $filters['gender'];
        }
        
        if (!empty($filters['brand'])) {
            $sql .= " AND p.brand = ?";
            $params[] = $filters['brand'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND (COALESCE(p.sale_price, p.price) >= ?)";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND (COALESCE(p.sale_price, p.price) <= ?)";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Order by
        $orderBy = 'p.created_at DESC';
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low':
                    $orderBy = 'COALESCE(p.sale_price, p.price) ASC';
                    break;
                case 'price_high':
                    $orderBy = 'COALESCE(p.sale_price, p.price) DESC';
                    break;
                case 'name':
                    $orderBy = 'p.name ASC';
                    break;
                case 'popularity':
                    $orderBy = 'p.is_bestseller DESC, p.created_at DESC';
                    break;
            }
        }
        
        $sql .= " ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get product by slug
     */
    public function findBySlug($slug) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.slug = ? AND p.status = ?";
        
        $stmt = $this->query($sql, [$slug, PRODUCT_STATUS_ACTIVE]);
        return $stmt->fetch();
    }
    
    /**
     * Get product images
     */
    public function getImages($productId) {
        $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY display_order, is_primary DESC";
        $stmt = $this->query($sql, [$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get product variants
     */
    public function getVariants($productId) {
        $sql = "SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY size, color";
        $stmt = $this->query($sql, [$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get featured products
     */
    public function getFeatured($limit = 8) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM {$this->table} p
                WHERE p.is_featured = 1 AND p.status = ?
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        $stmt = $this->query($sql, [PRODUCT_STATUS_ACTIVE, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get new arrivals
     */
    public function getNewArrivals($limit = 8) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM {$this->table} p
                WHERE p.is_new_arrival = 1 AND p.status = ?
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        $stmt = $this->query($sql, [PRODUCT_STATUS_ACTIVE, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get bestsellers
     */
    public function getBestsellers($limit = 8) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM {$this->table} p
                WHERE p.is_bestseller = 1 AND p.status = ?
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        $stmt = $this->query($sql, [PRODUCT_STATUS_ACTIVE, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get related products
     */
    public function getRelated($productId, $categoryId, $limit = 4) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM {$this->table} p
                WHERE p.category_id = ? AND p.product_id != ? AND p.status = ?
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        $stmt = $this->query($sql, [$categoryId, $productId, PRODUCT_STATUS_ACTIVE, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get best deals (products with sale price)
     */
    public function getBestDeals($limit = 8) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM {$this->table} p
                WHERE p.sale_price IS NOT NULL AND p.sale_price < p.price AND p.status = ?
                ORDER BY ((p.price - p.sale_price) / p.price * 100) DESC, p.created_at DESC
                LIMIT ?";
        
        $stmt = $this->query($sql, [PRODUCT_STATUS_ACTIVE, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get top selling products (bestsellers ordered by creation date)
     */
    public function getTopSelling($limit = 8) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM {$this->table} p
                WHERE p.is_bestseller = 1 AND p.status = ?
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        $stmt = $this->query($sql, [PRODUCT_STATUS_ACTIVE, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get product price (sale price if available, else regular price)
     */
    public function getPrice($product) {
        return !empty($product['sale_price']) && $product['sale_price'] < $product['price'] 
            ? $product['sale_price'] 
            : $product['price'];
    }
}

