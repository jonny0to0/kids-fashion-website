<?php
/**
 * Category Model
 * Handles category-related database operations
 */

class Category extends Model {
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    
    /**
     * Get all categories
     */
    public function getAll($includeInactive = false) {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM categories WHERE parent_id = c.category_id) as child_count,
                (SELECT COUNT(*) FROM products WHERE category_id = c.category_id) as product_count
                FROM {$this->table} c";
        
        if (!$includeInactive) {
            $sql .= " WHERE c.is_active = 1";
        }
        
        $sql .= " ORDER BY c.display_order ASC, c.name ASC";
        
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all active categories
     */
    public function getAllActive() {
        return $this->getAll(false);
    }
    
    /**
     * Get category by ID
     */
    public function getById($id) {
        return $this->find($id);
    }
    
    /**
     * Get category by slug
     */
    public function findBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ? AND is_active = 1 LIMIT 1";
        $stmt = $this->query($sql, [$slug]);
        return $stmt->fetch();
    }
    
    /**
     * Get parent categories only
     * @param bool $includeInactive Whether to include inactive categories (for admin use)
     */
    public function getParentCategories($includeInactive = false) {
        $sql = "SELECT * FROM {$this->table} WHERE parent_id IS NULL";
        if (!$includeInactive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY display_order ASC, name ASC";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get child categories by parent ID
     * @param int $parentId The parent category ID
     * @param bool $includeInactive Whether to include inactive categories (for admin use)
     */
    public function getChildCategories($parentId, $includeInactive = false) {
        $sql = "SELECT * FROM {$this->table} WHERE parent_id = ?";
        if (!$includeInactive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY display_order ASC, name ASC";
        $stmt = $this->query($sql, [$parentId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all categories directly from database (simple query, no subqueries)
     * Used as fallback if getAllForAdmin fails
     * 
     * @return array All categories
     */
    public function getAllDirect() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
            $stmt = $this->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Category::getAllDirect() Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all categories for admin (includes both active and inactive)
     * This is specifically for admin dashboard use
     * Fetches data directly from database - no caching
     * 
     * IMPORTANT: By default, this method returns ALL categories regardless of is_active value.
     * Only filters by status if explicitly requested via the 'status' filter.
     * 
     * @param array $filters Optional filters (search, status, orderBy)
     * @return array All categories with child_count and product_count
     */
    public function getAllForAdmin($filters = []) {
        // Direct database query - always fresh data
        // Admin should see ALL categories by default (no status filter unless specified)
        // Use simpler approach: get all categories first, then add counts
        $sql = "SELECT * FROM {$this->table}";

        $queryParams = [];
        $where = [];
        
        // Search filter
        if (!empty($filters['search'])) {
            $where[] = "(name LIKE ? OR slug LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
        }
        
        // Status filter (optional - if not set, shows ALL categories regardless of is_active value)
        // IMPORTANT: Do NOT filter by is_active unless explicitly requested via status filter
        // This ensures ALL categories (both active and inactive) are shown on admin page by default
        if (!empty($filters['status']) && ($filters['status'] === 'active' || $filters['status'] === 'inactive')) {
            if ($filters['status'] === 'active') {
                $where[] = "is_active = 1";
            } elseif ($filters['status'] === 'inactive') {
                $where[] = "is_active = 0";
            }
        }
        // If no status filter is provided, the query will return ALL categories (is_active = 0 AND is_active = 1)
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Order by - default to created_at DESC, but allow override
        $orderBy = $filters['orderBy'] ?? 'created_at DESC';
        // Split orderBy into column and direction for safety
        $orderParts = explode(' ', trim($orderBy));
        $orderColumn = $orderParts[0] ?? 'created_at';
        $orderDirection = isset($orderParts[1]) ? strtoupper($orderParts[1]) : 'DESC';
        
        // Validate column name (alphanumeric and underscore only) - allow common column names
        $allowedColumns = ['category_id', 'name', 'slug', 'created_at', 'updated_at', 'display_order', 'is_active'];
        if (in_array($orderColumn, $allowedColumns) || preg_match('/^[a-zA-Z0-9_]+$/', $orderColumn)) {
            // Validate direction
            if ($orderDirection !== 'ASC' && $orderDirection !== 'DESC') {
                $orderDirection = 'DESC';
            }
            $sql .= " ORDER BY {$orderColumn} {$orderDirection}";
        } else {
            // Fallback to safe default
            $sql .= " ORDER BY created_at DESC";
        }
        
        try {
            $stmt = $this->query($sql, $queryParams);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add child_count and product_count to each category
            foreach ($results as &$category) {
                // Get child count
                $childCountStmt = $this->query(
                    "SELECT COUNT(*) as count FROM {$this->table} WHERE parent_id = ?",
                    [$category['category_id']]
                );
                $childCountResult = $childCountStmt->fetch(PDO::FETCH_ASSOC);
                $category['child_count'] = $childCountResult['count'] ?? 0;
                
                // Get product count
                $productCountStmt = $this->query(
                    "SELECT COUNT(*) as count FROM products WHERE category_id = ?",
                    [$category['category_id']]
                );
                $productCountResult = $productCountStmt->fetch(PDO::FETCH_ASSOC);
                $category['product_count'] = $productCountResult['count'] ?? 0;
            }
            unset($category); // Unset reference
            
            return $results;
        } catch (PDOException $e) {
            // Log error with full details
            error_log("Category::getAllForAdmin() Error: " . $e->getMessage());
            error_log("Category::getAllForAdmin() SQL: " . $sql);
            error_log("Category::getAllForAdmin() Params: " . print_r($queryParams, true));
            return [];
        } catch (Exception $e) {
            // Catch any other exceptions
            error_log("Category::getAllForAdmin() Exception: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if category name exists (excluding current category)
     */
    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = ?";
        $params = [$name];
        
        if ($excludeId) {
            $sql .= " AND category_id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Check if category slug exists (excluding current category)
     */
    public function slugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND category_id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Activate category
     */
    public function activate($id) {
        $data = ['is_active' => 1];
        // Try to include updated_at, but handle gracefully if column doesn't exist
        try {
            $dataWithTimestamp = array_merge($data, ['updated_at' => date('Y-m-d H:i:s')]);
            return $this->update($id, $dataWithTimestamp);
        } catch (PDOException $e) {
            // If updated_at column doesn't exist, update without it
            if (strpos($e->getMessage(), 'updated_at') !== false) {
                return $this->update($id, $data);
            }
            throw $e; // Re-throw if it's a different error
        }
    }
    
    /**
     * Deactivate category
     */
    public function deactivate($id) {
        $data = ['is_active' => 0];
        // Try to include updated_at, but handle gracefully if column doesn't exist
        try {
            $dataWithTimestamp = array_merge($data, ['updated_at' => date('Y-m-d H:i:s')]);
            return $this->update($id, $dataWithTimestamp);
        } catch (PDOException $e) {
            // If updated_at column doesn't exist, update without it
            if (strpos($e->getMessage(), 'updated_at') !== false) {
                return $this->update($id, $data);
            }
            throw $e; // Re-throw if it's a different error
        }
    }
    
    /**
     * Generate slug from name
     * @param string $name The category name
     * @param int|null $excludeId Category ID to exclude from uniqueness check (for edit operations)
     * @return string Unique slug
     */
    public function generateSlug($name, $excludeId = null) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $baseSlug = $slug;
        $counter = 1;
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Get attributes for this category (LEGACY - for backward compatibility)
     * @param bool $activeOnly Whether to return only active attributes
     * @return array
     */
    public function getAttributes($categoryId, $activeOnly = true) {
        $attributeModel = new Attribute();
        return $attributeModel->getByCategory($categoryId, $activeOnly);
    }
    
    /**
     * Get attribute groups for this category with inheritance
     * Returns groups assigned directly to this category plus inherited from parents
     * @param int $categoryId
     * @param bool $includeInherited Whether to include inherited groups
     * @return array
     */
    public function getAttributeGroupsWithInheritance($categoryId, $includeInherited = true) {
        require_once APP_PATH . '/models/AttributeGroup.php';
        $attributeGroupModel = new AttributeGroup();
        
        $allGroups = [];
        $processedCategories = [];
        $currentCategoryId = $categoryId;
        
        // Traverse up the category tree to collect all groups
        while ($currentCategoryId) {
            // Prevent infinite loops
            if (in_array($currentCategoryId, $processedCategories)) {
                break;
            }
            $processedCategories[] = $currentCategoryId;
            
            // Get groups directly assigned to this category (not inherited)
            // We only get direct assignments to avoid duplicates
            $categoryGroups = $attributeGroupModel->getCategoryGroups($currentCategoryId, false);
            
            foreach ($categoryGroups as $group) {
                // Mark as inherited if not the original category
                if ($currentCategoryId != $categoryId) {
                    $group['is_inherited'] = true;
                }
                
                // Avoid duplicates
                $key = $group['group_id'];
                if (!isset($allGroups[$key])) {
                    $allGroups[$key] = $group;
                }
            }
            
            // Move to parent category
            $category = $this->getById($currentCategoryId);
            $currentCategoryId = $category['parent_id'] ?? null;
        }
        
        // Filter inherited if not wanted
        if (!$includeInherited) {
            $allGroups = array_filter($allGroups, function($group) {
                return !($group['is_inherited'] ?? false);
            });
        }
        
        return array_values($allGroups);
    }
    
    /**
     * Get category path (all parent categories up to root)
     * @param int $categoryId
     * @return array Array of category IDs from root to current
     */
    public function getCategoryPath($categoryId) {
        $path = [];
        $currentCategoryId = $categoryId;
        $processed = [];
        
        while ($currentCategoryId) {
            // Prevent infinite loops
            if (in_array($currentCategoryId, $processed)) {
                break;
            }
            $processed[] = $currentCategoryId;
            
            $category = $this->getById($currentCategoryId);
            if (!$category) {
                break;
            }
            
            array_unshift($path, $category);
            $currentCategoryId = $category['parent_id'] ?? null;
        }
        
        return $path;
    }
}

