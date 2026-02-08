<?php
/**
 * Product Model
 * Handles product-related database operations
 */

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    /**
     * Get products with pagination
     */
    public function getProducts($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, 
                COALESCE(s.total_quantity, 0) as sold_qty,
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND status = 'APPROVED') as rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND status = 'APPROVED') as review_count
                FROM {$this->table} p
                LEFT JOIN product_sales_summary s ON p.product_id = s.product_id";

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
    public function findBySlug($slug)
    {
        $sql = "SELECT p.* 
                FROM {$this->table} p
                WHERE p.slug = ? AND p.status = ?";

        $stmt = $this->query($sql, [$slug, PRODUCT_STATUS_ACTIVE]);
        return $stmt->fetch();
    }

    /**
     * Get product images
     */
    public function getImages($productId)
    {
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
    public function getVariants($productId, $activeOnly = false)
    {
        require_once APP_PATH . '/models/ProductVariant.php';
        $variantModel = new ProductVariant();
        return $variantModel->getByProductId($productId, $activeOnly);
    }

    /**
     * Save product variant
     * @param int $productId
     * @param array $variantData
     * @return int|false Variant ID on success, false on failure
     */
    public function saveVariant($productId, $variantData)
    {
        // Require models if not autoloaded
        require_once APP_PATH . '/models/ProductVariant.php';
        require_once APP_PATH . '/models/VariantAttribute.php';

        $variantModel = new ProductVariant();
        $attributeModel = new VariantAttribute();

        // Extract Size and Color for legacy/hybrid support
        $variantSize = $variantData['size'] ?? ($variantData['attributes']['Size'] ?? null);
        $variantColor = $variantData['color'] ?? ($variantData['attributes']['Color'] ?? null);

        $data = [
            'product_id' => $productId,
            'sku' => $variantData['sku'] ?? null,
            'size' => $variantSize,
            'color' => $variantColor,
            'price' => !empty($variantData['price']) ? (float) $variantData['price'] : 0.00,
            'sale_price' => !empty($variantData['sale_price']) ? (float) $variantData['sale_price'] : 0.00,
            'additional_price' => 0.00, // Deprecated but kept for schema compatibility if needed
            'stock_quantity' => isset($variantData['stock_quantity']) ? (int) $variantData['stock_quantity'] : 0,
            'image_url' => $variantData['image_url'] ?? null,
            'is_active' => isset($variantData['is_active']) ? (int) $variantData['is_active'] : 1
        ];

        $variantId = null;

        if (isset($variantData['variant_id']) && !empty($variantData['variant_id'])) {
            // Update
            $variantId = (int) $variantData['variant_id'];
            unset($data['product_id']); // Don't allow changing product_id
            $variantModel->update($variantId, $data);
        } else {
            // Insert
            $variantId = $variantModel->create($data);
        }

        if ($variantId) {
            // Save Attributes
            if (isset($variantData['attributes']) && is_array($variantData['attributes'])) {
                // Delete existing attributes for this variant (full replacement strategy)
                $attributeModel->deleteByVariantId($variantId);

                // Insert new names/values
                foreach ($variantData['attributes'] as $name => $value) {
                    if (!empty($value)) {
                        $attributeModel->create([
                            'variant_id' => $variantId,
                            'attribute_name' => $name,
                            'attribute_value' => $value
                        ]);
                    }
                }
            }
            return $variantId;
        }

        return false;
    }

    /**
     * Delete product variant
     * @param int $variantId
     * @return bool
     */
    public function deleteVariant($variantId)
    {
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
    public function deleteAllVariants($productId)
    {
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
    public function getAttributes($productId)
    {
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
    private function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    /**
     * Get featured products
     */
    public function getFeatured($limit = 8)
    {
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
    public function getNewArrivals($limit = 8)
    {
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
    public function getBestsellers($limit = 8)
    {
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
    public function getRelated($productId, $limit = 4)
    {
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
    public function getBestDeals($limit = 8)
    {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as review_count
                FROM {$this->table} p
                WHERE p.status = ? 
                AND (
                    -- Rule 1: Discount >= 25%
                    (p.sale_price IS NOT NULL AND p.sale_price < p.price AND ((p.price - p.sale_price) / p.price * 100) >= 25)
                    OR
                    -- Rule 2: Best seller + discount >= 5%
                    (p.is_bestseller = 1 AND p.sale_price IS NOT NULL AND p.sale_price < p.price AND ((p.price - p.sale_price) / p.price * 100) >= 5)
                    -- Future Rules: Flash deal, Clearance, Bundle (columns pending)
                )
                ORDER BY 
                    ((p.price - p.sale_price) / p.price * 100) DESC, -- Priority 1: Highest discount %
                    p.is_bestseller DESC,                            -- Priority 3: Best sellers
                    p.price DESC,                                    -- Priority 4: High-margin (proxy via price)
                    p.created_at DESC
                LIMIT ?";

        $stmt = $this->query($sql, [PRODUCT_STATUS_ACTIVE, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get top selling products (bestsellers ordered by creation date)
     */
    public function getTopSelling($limit = 8)
    {
        // Use the new summary table for accurate ranking based on quantity sold
        $sql = "SELECT p.*, 
                COALESCE(s.total_quantity, 0) as sold_qty,
                (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image,
                (SELECT AVG(rating) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as rating,
                (SELECT COUNT(*) FROM reviews WHERE product_id = p.product_id AND is_approved = 1) as review_count
                FROM {$this->table} p
                LEFT JOIN product_sales_summary s ON p.product_id = s.product_id
                WHERE p.status = ? 
                AND (s.is_excluded = 0 OR s.is_excluded IS NULL)
                AND (s.total_quantity > 0 OR p.is_bestseller = 1) -- Fallback to manual flag if no sales data yet, or use union
                ORDER BY 
                    COALESCE(s.is_pinned, 0) DESC,
                    COALESCE(s.total_quantity, 0) DESC,
                    COALESCE(s.total_revenue, 0) DESC,
                    p.is_bestseller DESC, -- Fallback tie-breaker
                    p.created_at DESC
                LIMIT ?";

        // Note: Modified logic to prioritize actual sales, but keep manually marked bestsellers as a fallback 
        // mixed in if they have high priority or if sales data is building up.
        // For strict adherence to the new rule "Quantity Sold", the primary sort is total_quantity.
        
        $stmt = $this->query($sql, [PRODUCT_STATUS_ACTIVE, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get product price (sale price if available, else regular price)
     */
    public function getPrice($product)
    {
        return !empty($product['sale_price']) && $product['sale_price'] < $product['price']
            ? $product['sale_price']
            : $product['price'];
    }
}

