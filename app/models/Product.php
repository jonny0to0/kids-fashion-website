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
        
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as review_count
                FROM {$this->table} p";
        
        // Add JOIN if filtering by category
        if (!empty($filters['category'])) {
            $sql .= " INNER JOIN categories c ON p.category_id = c.category_id";
        }
        
        $sql .= " WHERE p.status = ?";
        $params = [PRODUCT_STATUS_ACTIVE];
        
        // Add category filter - ensure category is active
        if (!empty($filters['category'])) {
            $sql .= " AND c.slug = ? AND c.is_active = 1";
            $params[] = $filters['category'];
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
        $sql = "SELECT p.* 
                FROM {$this->table} p
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
     * @param int $productId
     * @param bool $activeOnly Whether to return only active variants
     * @return array
     */
    public function getVariants($productId, $activeOnly = false) {
        $sql = "SELECT * FROM product_variants WHERE product_id = ?";
        $params = [$productId];
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY size, color";
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Save product variant
     * @param int $productId
     * @param array $variantData
     * @return int|false Variant ID on success, false on failure
     */
    public function saveVariant($productId, $variantData) {
        $data = [
            'product_id' => $productId,
            'size' => $variantData['size'] ?? '',
            'color' => $variantData['color'] ?? null,
            'color_code' => $variantData['color_code'] ?? null,
            'additional_price' => isset($variantData['additional_price']) ? (float)$variantData['additional_price'] : 0.00,
            'stock_quantity' => isset($variantData['stock_quantity']) ? (int)$variantData['stock_quantity'] : 0,
            'sku' => $variantData['sku'] ?? null,
            'is_active' => isset($variantData['is_active']) ? (int)$variantData['is_active'] : 1
        ];
        
        if (isset($variantData['variant_id']) && !empty($variantData['variant_id'])) {
            // Update existing variant
            $variantId = (int)$variantData['variant_id'];
            unset($data['product_id']); // Don't update product_id
            if ($this->query(
                "UPDATE product_variants SET size = ?, color = ?, color_code = ?, additional_price = ?, stock_quantity = ?, sku = ?, is_active = ? WHERE variant_id = ?",
                [$data['size'], $data['color'], $data['color_code'], $data['additional_price'], $data['stock_quantity'], $data['sku'], $data['is_active'], $variantId]
            ) !== false) {
                return $variantId;
            }
            return false;
        } else {
            // Insert new variant
            $sql = "INSERT INTO product_variants (product_id, size, color, color_code, additional_price, stock_quantity, sku, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            if ($this->query($sql, [
                $data['product_id'], $data['size'], $data['color'], $data['color_code'], 
                $data['additional_price'], $data['stock_quantity'], $data['sku'], $data['is_active']
            ]) !== false) {
                return $this->getLastInsertId();
            }
            return false;
        }
    }
    
    /**
     * Delete product variant
     * @param int $variantId
     * @return bool
     */
    public function deleteVariant($variantId) {
        return $this->query(
            "DELETE FROM product_variants WHERE variant_id = ?",
            [$variantId]
        ) !== false;
    }
    
    /**
     * Delete all variants for a product
     * @param int $productId
     * @return bool
     */
    public function deleteAllVariants($productId) {
        return $this->query(
            "DELETE FROM product_variants WHERE product_id = ?",
            [$productId]
        ) !== false;
    }
    
    /**
     * Get product attributes using Attribute model
     * @param int $productId
     * @return array
     */
    public function getAttributes($productId) {
        try {
            // Try to use Attribute model if available
            if (class_exists('Attribute')) {
                $attributeModel = new Attribute();
                if (method_exists($attributeModel, 'getProductAttributes')) {
                    return $attributeModel->getProductAttributes($productId);
                }
            }
        } catch (Exception $e) {
            // Fall through to direct query
        }
        
        // Fallback: direct query if Attribute model method not available
        $sql = "SELECT pa.*, ca.attribute_name, ca.attribute_type, ca.attribute_options
                FROM product_attributes pa
                INNER JOIN category_attributes ca ON pa.attribute_id = ca.attribute_id
                WHERE pa.product_id = ?
                ORDER BY ca.display_order ASC";
        $stmt = $this->query($sql, [$productId]);
        $attributes = $stmt->fetchAll();
        
        // Decode JSON options for select type attributes
        foreach ($attributes as &$attribute) {
            if (!empty($attribute['attribute_type']) && $attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                $decoded = json_decode($attribute['attribute_options'], true);
                $attribute['options'] = (is_array($decoded)) ? $decoded : [];
            } else {
                $attribute['options'] = [];
            }
        }
        unset($attribute);
        
        return $attributes;
    }
    
    /**
     * Get last insert ID
     * @return int
     */
    private function getLastInsertId() {
        return $this->db->lastInsertId();
    }
    
    /**
     * Get featured products
     */
    public function getFeatured($limit = 8) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as review_count
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
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as review_count
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
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as review_count
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
    public function getRelated($productId, $limit = 4) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as review_count
                FROM {$this->table} p
                WHERE p.product_id != ? AND p.status = ?
                ORDER BY p.created_at DESC
                LIMIT ?";
        
        $stmt = $this->query($sql, [$productId, PRODUCT_STATUS_ACTIVE, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get best deals (products with sale price)
     */
    public function getBestDeals($limit = 8) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as review_count
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
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as review_count
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

