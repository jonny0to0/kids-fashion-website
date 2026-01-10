<?php
/**
 * Attribute Model
 * Handles category attribute-related database operations
 */
require_once APP_PATH . '/models/Model.php';

class CategoryAttribute extends Model {
    protected $table = 'category_attributes';
    protected $primaryKey = 'attribute_id';
    
    /**
     * Get all attributes for a category (LEGACY - for backward compatibility)
     * @param int $categoryId
     * @param bool $activeOnly Whether to return only active attributes
     * @return array    
     */
    public function getByCategory($categoryId, $activeOnly = true) {
        $attributes = [];
        $attributeIds = []; // Track IDs to avoid duplicates
        
        // Try new group-based method first
        try {
            $groupAttributes = $this->getByCategoryWithInheritance($categoryId, $activeOnly);
            if (!empty($groupAttributes) && is_array($groupAttributes)) {
                foreach ($groupAttributes as $attr) {
                    if (!in_array($attr['attribute_id'], $attributeIds)) {
                        $attributes[] = $attr;
                        $attributeIds[] = $attr['attribute_id'];
                    }
                }
            }
        } catch (Exception $e) {
            // If group method fails, continue to legacy method
            error_log('getByCategoryWithInheritance error: ' . $e->getMessage());
        }
        
        // Always try legacy direct category method as fallback or supplement
        try {
            $sql = "SELECT * FROM {$this->table} WHERE category_id = ?";
            $params = [$categoryId];
            
            if ($activeOnly) {
                $sql .= " AND is_active = 1";
            }
            
            $sql .= " ORDER BY display_order ASC, attribute_name ASC";
            
            $stmt = $this->query($sql, $params);
            $legacyAttributes = $stmt->fetchAll();
            
            // Add legacy attributes that aren't already in the list
            foreach ($legacyAttributes as $attr) {
                if (!in_array($attr['attribute_id'], $attributeIds)) {
                    $attributes[] = $attr;
                    $attributeIds[] = $attr['attribute_id'];
                }
            }
        } catch (Exception $e) {
            error_log('Legacy getByCategory error: ' . $e->getMessage());
        }
        
        // Decode JSON options for select type attributes and dependency rules
        foreach ($attributes as &$attribute) {
            if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                $decoded = json_decode($attribute['attribute_options'], true);
                $attribute['options'] = (is_array($decoded)) ? $decoded : [];
            } else {
                $attribute['options'] = [];
            }
            
            // Decode show_when condition if exists
            if (!empty($attribute['show_when'])) {
                $decoded = json_decode($attribute['show_when'], true);
                $attribute['show_when_decoded'] = (is_array($decoded)) ? $decoded : null;
            } else {
                $attribute['show_when_decoded'] = null;
            }
            
            // Ensure boolean fields are properly cast
            $attribute['is_filterable'] = (bool)($attribute['is_filterable'] ?? false);
            $attribute['is_variant'] = (bool)($attribute['is_variant'] ?? false);
        }
        unset($attribute);
        
        return $attributes;
    }
    
    /**
     * Get all attributes for a category with inheritance (NEW - Group-based system)
     * This method gets attributes from all attribute groups assigned to the category
     * and its parent categories (inheritance)
     * 
     * @param int $categoryId The category ID to get attributes for
     * @param bool $activeOnly Whether to return only active attributes
     * @return array Array of attributes with their group information
     */
    public function getByCategoryWithInheritance($categoryId, $activeOnly = true) {
        require_once APP_PATH . '/models/AttributeGroup.php';
        require_once APP_PATH . '/models/Category.php';
        
        $categoryModel = new Category();
        $attributeGroupModel = new AttributeGroup();
        
        // Get all group IDs for this category (including inherited from parents)
        $groupIds = $this->getCategoryGroupIdsWithInheritance($categoryId);
        
        if (empty($groupIds)) {
            return [];
        }
        
        // Get all attributes from these groups
        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $sql = "SELECT ca.*, ag.group_name, ag.group_id
                FROM {$this->table} ca
                INNER JOIN attribute_groups ag ON ca.group_id = ag.group_id
                WHERE ca.group_id IN ({$placeholders})";
        
        $params = $groupIds;
        
        if ($activeOnly) {
            $sql .= " AND ca.is_active = 1 AND ag.is_active = 1";
        }
        
        $sql .= " ORDER BY ag.display_order ASC, ca.display_order ASC, ca.attribute_name ASC";
        
        $stmt = $this->query($sql, $params);
        $attributes = $stmt->fetchAll();
        
        // Decode JSON options for select type attributes and dependency rules
        foreach ($attributes as &$attribute) {
            if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                $decoded = json_decode($attribute['attribute_options'], true);
                $attribute['options'] = (is_array($decoded)) ? $decoded : [];
            } else {
                $attribute['options'] = [];
            }
            
            // Decode show_when condition if exists
            if (!empty($attribute['show_when'])) {
                $decoded = json_decode($attribute['show_when'], true);
                $attribute['show_when_decoded'] = (is_array($decoded)) ? $decoded : null;
            } else {
                $attribute['show_when_decoded'] = null;
            }
            
            // Ensure boolean fields are properly cast
            $attribute['is_filterable'] = (bool)($attribute['is_filterable'] ?? false);
            $attribute['is_variant'] = (bool)($attribute['is_variant'] ?? false);
        }
        unset($attribute);
        
        return $attributes;
    }
    
    /**
     * Get all group IDs for a category including inherited from parents
     * @param int $categoryId
     * @return array Array of group IDs
     */
    public function getCategoryGroupIdsWithInheritance($categoryId) {
        require_once APP_PATH . '/models/Category.php';
        $categoryModel = new Category();
        
        $groupIds = [];
        $processedCategories = [];
        $currentCategoryId = $categoryId;
        
        // Traverse up the category tree to collect all groups
        // We only get direct assignments (is_inherited = 0) to avoid duplicates
        // Inherited groups are computed by traversing up the tree
        while ($currentCategoryId) {
            // Prevent infinite loops
            if (in_array($currentCategoryId, $processedCategories)) {
                break;
            }
            $processedCategories[] = $currentCategoryId;
            
            // Get groups directly assigned to this category (not inherited)
            // This ensures we don't get duplicates from stored inherited groups
            $categoryGroups = $this->query(
                "SELECT group_id FROM category_attribute_groups WHERE category_id = ? AND is_inherited = 0",
                [$currentCategoryId]
            )->fetchAll();
            
            foreach ($categoryGroups as $group) {
                $groupIds[] = $group['group_id'];
            }
            
            // Move to parent category
            $category = $categoryModel->getById($currentCategoryId);
            if (!$category) {
                break;
            }
            $currentCategoryId = $category['parent_id'] ?? null;
        }
        
        // Remove duplicates and return
        return array_unique($groupIds);
    }
            
    /**
     * Get common attributes (attributes that should appear for all products regardless of category)
     * Common attributes are:
     * 1. Attributes with category_id = NULL and is_active = 1
     * 2. Attributes in a group named "Common" (case-insensitive) with is_active = 1
     * 
     * @param bool $activeOnly Whether to return only active attributes (default: true)
     * @return array Array of common attributes
     */
    public function getCommonAttributes($activeOnly = true)
    {
        // Get all common attributes - attributes that should appear for all products regardless of category
        // Common attributes are identified by:
        // 1. Attributes in a group named "Common" (case-insensitive) - PRIMARY method (new system)
        // 2. Attributes with category_id = NULL (legacy method for backward compatibility)
        $sql = "SELECT DISTINCT ca.*, ag.group_name, ag.group_id, ag.display_order as group_display_order
                FROM {$this->table} ca
                LEFT JOIN attribute_groups ag ON ca.group_id = ag.group_id
                WHERE (
                    LOWER(TRIM(ag.group_name)) = 'common'
                    OR 
                    (ca.category_id IS NULL AND ca.group_id IS NULL)
                )";
        
        $params = [];
        
        if ($activeOnly) {
            $sql .= " AND ca.is_active = 1";
            // For attributes in the Common group, also require the group to be active
            // For legacy attributes (category_id IS NULL), we only check attribute status
            $sql .= " AND (
                (ca.group_id IS NOT NULL AND ag.is_active = 1)
                OR 
                ca.group_id IS NULL
            )";
        }
        
        $sql .= " ORDER BY 
                COALESCE(ag.display_order, 999) ASC, 
                ca.display_order ASC, 
                ca.attribute_name ASC";
        
        $stmt = $this->query($sql, $params);
        $attributes = $stmt->fetchAll();
        
        // Decode JSON options for select type attributes and dependency rules
        foreach ($attributes as &$attribute) {
            if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                $decoded = json_decode($attribute['attribute_options'], true);
                $attribute['options'] = (is_array($decoded)) ? $decoded : [];
            } else {
                $attribute['options'] = [];
            }
            
            // Decode show_when condition if exists
            if (!empty($attribute['show_when'])) {
                $decoded = json_decode($attribute['show_when'], true);
                $attribute['show_when_decoded'] = (is_array($decoded)) ? $decoded : null;
            } else {
                $attribute['show_when_decoded'] = null;
            }
            
            // Ensure boolean fields are properly cast
            $attribute['is_filterable'] = (bool)($attribute['is_filterable'] ?? false);
            $attribute['is_variant'] = (bool)($attribute['is_variant'] ?? false);
        }
        unset($attribute);
        
        return $attributes;
    }
            
            /**
             * Get attributes by group ID
                 * @param int $groupId
                 * @param bool $activeOnly Whether to return only active attributes
                 * @return array
                 */
            public function getByGroup($groupId, $activeOnly = true) {
                    $sql = "SELECT ca.*, ag.group_name
                            FROM {$this->table} ca
                            INNER JOIN attribute_groups ag ON ca.group_id = ag.group_id
                            WHERE ca.group_id = ?";
                    $params = [$groupId];
                    
                    if ($activeOnly) {
                        $sql .= " AND ca.is_active = 1 AND ag.is_active = 1";
                    }
                    
                    $sql .= " ORDER BY ca.display_order ASC, ca.attribute_name ASC";
                    
                    $stmt = $this->query($sql, $params);
                    $attributes = $stmt->fetchAll();
                    
                    // Decode JSON options for select type attributes
                    foreach ($attributes as &$attribute) {
                        if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
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
                 * Get attribute by ID
                 * @param int $attributeId
                 * @return array|null
                 */
            public function getById($attributeId) {
                    $attribute = $this->find($attributeId);
                    
                    if ($attribute) {
                        if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                            $decoded = json_decode($attribute['attribute_options'], true);
                            $attribute['options'] = (is_array($decoded)) ? $decoded : [];
                        } else {
                            $attribute['options'] = [];
                        }
                        
                        // Decode show_when condition if exists
                        if (!empty($attribute['show_when'])) {
                            $decoded = json_decode($attribute['show_when'], true);
                            $attribute['show_when_decoded'] = (is_array($decoded)) ? $decoded : null;
                        } else {
                            $attribute['show_when_decoded'] = null;
                        }
                        
                        // Ensure boolean fields are properly cast
                        $attribute['is_filterable'] = (bool)($attribute['is_filterable'] ?? false);
                        $attribute['is_variant'] = (bool)($attribute['is_variant'] ?? false);
                    }
                    
                    return $attribute;
                }
                
                /**
                 * Check if an attribute should be shown based on parent attribute value
                 * @param array $attribute The dependent attribute
                 * @param string|null $parentValue The value of the parent attribute
                 * @return bool True if attribute should be shown
                 */
            public function shouldShowAttribute($attribute, $parentValue = null) {
                    // If no dependency, always show
                    if (empty($attribute['depends_on']) || empty($attribute['show_when_decoded'])) {
                        return true;
                    }
                    
                    // If parent value is not set, hide
                    if ($parentValue === null || $parentValue === '') {
                        return false;
                    }
                    
                    $condition = $attribute['show_when_decoded'];
                    
                    // Simple value match: {"value": "Sports"}
                    if (isset($condition['value'])) {
                        return trim($parentValue) === trim($condition['value']);
                    }
                    
                    // Array match: {"operator": "in", "values": ["Sports", "Formal"]}
                    if (isset($condition['operator']) && $condition['operator'] === 'in' && isset($condition['values'])) {
                        return in_array(trim($parentValue), array_map('trim', $condition['values']));
                    }
                    
                    // Not in: {"operator": "not_in", "values": ["Casual"]}
                    if (isset($condition['operator']) && $condition['operator'] === 'not_in' && isset($condition['values'])) {
                        return !in_array(trim($parentValue), array_map('trim', $condition['values']));
                    }
                    
                    // Default: show if condition doesn't match any known pattern
                    return true;
                }
                
                /**
                 * Get all attributes that depend on a specific attribute
                 * @param int $parentAttributeId
                 * @return array
                 */
            public function getDependentAttributes($parentAttributeId) {
                    $sql = "SELECT * FROM {$this->table} WHERE depends_on = ? AND is_active = 1 ORDER BY display_order ASC";
                    $stmt = $this->query($sql, [$parentAttributeId]);
                    $attributes = $stmt->fetchAll();
                    
                    // Decode JSON options and conditions
                    foreach ($attributes as &$attribute) {
                        if ($attribute['attribute_type'] === 'select' && !empty($attribute['attribute_options'])) {
                            $decoded = json_decode($attribute['attribute_options'], true);
                            $attribute['options'] = (is_array($decoded)) ? $decoded : [];
                        } else {
                            $attribute['options'] = [];
                        }
                        
                        if (!empty($attribute['show_when'])) {
                            $decoded = json_decode($attribute['show_when'], true);
                            $attribute['show_when_decoded'] = (is_array($decoded)) ? $decoded : null;
                        } else {
                            $attribute['show_when_decoded'] = null;
                        }
                        
                        $attribute['is_filterable'] = (bool)($attribute['is_filterable'] ?? false);
                        $attribute['is_variant'] = (bool)($attribute['is_variant'] ?? false);
                    }
                    unset($attribute);
                    
                    return $attributes;
                }
                
                /**
                 * Search attributes by name
                 * @param string $name The attribute name to search for (case-insensitive exact match)
                 * @param int|null $groupId Optional: filter by group_id
                 * @param int|null $categoryId Optional: filter by category_id
                 * @param int|null $excludeAttributeId Optional: exclude this attribute ID (useful when updating)
                 * @return array Array of matching attributes
                 */
            public function searchByName($name, $groupId = null, $categoryId = null, $excludeAttributeId = null) {
                    $sql = "SELECT ca.*, ag.group_name
                            FROM {$this->table} ca
                            LEFT JOIN attribute_groups ag ON ca.group_id = ag.group_id
                            WHERE LOWER(TRIM(ca.attribute_name)) = LOWER(TRIM(?))";
                    $params = [$name];
                    
                    if ($groupId !== null) {
                        $sql .= " AND ca.group_id = ?";
                        $params[] = $groupId;
                    }
                    
                    if ($categoryId !== null) {
                        $sql .= " AND ca.category_id = ?";
                        $params[] = $categoryId;
                    }
                    
                    if ($excludeAttributeId !== null) {
                        $sql .= " AND ca.attribute_id != ?";
                        $params[] = $excludeAttributeId;
                    }
                    
                    $sql .= " ORDER BY ca.attribute_name ASC";
                    
                    $stmt = $this->query($sql, $params);
                    return $stmt->fetchAll();
                }
                
                /**
                 * Check if attribute name already exists
                 * @param string $name The attribute name to check
                 * @param int|null $groupId Optional: check within specific group (if provided)
                 * @param int|null $categoryId Optional: check within specific category (if group not provided)
                 * @param int|null $excludeAttributeId Optional: exclude this attribute ID (useful when updating)
                 * @return bool True if name exists, false otherwise
                 */
            public function nameExists($name, $groupId = null, $categoryId = null, $excludeAttributeId = null) {
                    // If group_id is provided, check within that group (most specific)
                    // If only category_id is provided, check within that category
                    // If neither is provided, check globally
                    $attributes = $this->searchByName($name, $groupId, $categoryId, $excludeAttributeId);
                    return !empty($attributes);
                }
                
                /**
                 * Get all attributes
                 * @param bool $activeOnly Whether to return only active attributes
                 * @return array Array of all attributes
                 */
            public function getAll($activeOnly = false) {
                    $sql = "SELECT ca.*, ag.group_name, ag.group_id, c.name as category_name
                            FROM {$this->table} ca
                            LEFT JOIN attribute_groups ag ON ca.group_id = ag.group_id
                            LEFT JOIN categories c ON ca.category_id = c.category_id
                            WHERE 1=1";
                    $params = [];
                    
                    if ($activeOnly) {
                        $sql .= " AND ca.is_active = 1";
                        $sql .= " AND (ag.is_active = 1 OR ag.group_id IS NULL)";
                    }
                    
                    $sql .= " ORDER BY ca.attribute_name ASC";
                    
                    $stmt = $this->query($sql, $params);
                    return $stmt->fetchAll();
                }
                
                /**
                 * Create a new attribute
                 * @param array $data
                 * @return int|false
                 */
            public function createAttribute($data) {
                    // Encode options if it's an array
                    if (isset($data['attribute_options']) && is_array($data['attribute_options'])) {
                        $data['attribute_options'] = json_encode($data['attribute_options']);
                    }
                    
                    // Build SQL and execute directly
                    $fields = array_keys($data);
                    $placeholders = array_fill(0, count($fields), '?');
                    
                    $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute(array_values($data));
                    
                    return $this->db->lastInsertId();
                }
                
                /**
                 * Get the last insert ID from the database connection
                 * @return string
                 */
            public function getLastInsertId() {
                    return $this->db->lastInsertId();
                }
                
                /**
                 * Update an attribute
                 * @param int $attributeId
                 * @param array $data
                 * @return bool
                 */
            public function updateAttribute($attributeId, $data) {
                    // Encode options if it's an array
                    if (isset($data['attribute_options']) && is_array($data['attribute_options'])) {
                        $data['attribute_options'] = json_encode($data['attribute_options']);
                    }
                    
                    return $this->update($attributeId, $data);
                }
                
                /**
                 * Get product attribute values with group information
                 * Returns all applicable attributes for the product's category (including Common attributes),
                 * organized by attribute groups. Attributes with values in product_attributes are included,
                 * and attributes without values are still shown if they're part of the category's attribute groups.
                 * 
                 * @param int $productId
                 * @return array Array of attributes with group information, organized by group_id
                 */
            public function getProductAttributes($productId) {
                    // First, get the product to find its category
                    require_once APP_PATH . '/models/Product.php';
                    $productModel = new Product();
                    $product = $productModel->find($productId);
                    
                    if (!$product || empty($product['category_id'])) {
                        return [];
                    }
                    
                    $categoryId = $product['category_id'];
                    
                    // Get all applicable attributes for this category (including inherited and Common)
                    $categoryAttributes = [];
                    try {
                        $categoryAttributes = $this->getByCategoryWithInheritance($categoryId, true);
                        if (!is_array($categoryAttributes)) {
                            $categoryAttributes = [];
                        }
                    } catch (Exception $e) {
                        error_log('Error fetching category attributes: ' . $e->getMessage());
                        $categoryAttributes = [];
                    }
                    
                    // Get common attributes (from "Common" group or with category_id = NULL)
                    $commonAttributes = [];
                    try {
                        $commonAttributes = $this->getCommonAttributes(true);
                        if (!is_array($commonAttributes)) {
                            $commonAttributes = [];
                        }
                    } catch (Exception $e) {
                        error_log('Error fetching common attributes: ' . $e->getMessage());
                        $commonAttributes = [];
                    }
                    
                    // Merge category and common attributes, avoiding duplicates
                    $allAttributes = [];
                    $attributeIds = [];
                    
                    foreach ($categoryAttributes as $attr) {
                        if (!in_array($attr['attribute_id'], $attributeIds)) {
                            $allAttributes[] = $attr;
                            $attributeIds[] = $attr['attribute_id'];
                        }
                    }
                    
                    foreach ($commonAttributes as $attr) {
                        if (!in_array($attr['attribute_id'], $attributeIds)) {
                            $allAttributes[] = $attr;
                            $attributeIds[] = $attr['attribute_id'];
                        }
                    }
                    
                    if (empty($allAttributes)) {
                        return [];
                    }
                    
                    // Get product attribute values
                    $productAttrIds = array_column($allAttributes, 'attribute_id');
                    $valueMap = [];
                    
                    if (!empty($productAttrIds)) {
                        $placeholders = implode(',', array_fill(0, count($productAttrIds), '?'));
                        $sql = "SELECT pa.attribute_id, pa.attribute_value 
                                FROM product_attributes pa
                                WHERE pa.product_id = ? AND pa.attribute_id IN ({$placeholders})";
                        
                        $params = array_merge([$productId], $productAttrIds);
                        $stmt = $this->query($sql, $params);
                        $productValues = $stmt->fetchAll();
                        
                        // Create a map of attribute_id => attribute_value for quick lookup
                        foreach ($productValues as $pv) {
                            $valueMap[$pv['attribute_id']] = $pv['attribute_value'];
                        }
                    }
                    
                    // Combine attributes with their values
                    $result = [];
                    foreach ($allAttributes as $attr) {
                        $attrData = [
                            'attribute_id' => $attr['attribute_id'],
                            'attribute_name' => $attr['attribute_name'],
                            'attribute_type' => $attr['attribute_type'],
                            'attribute_options' => $attr['attribute_options'],
                            'attribute_value' => $valueMap[$attr['attribute_id']] ?? null,
                            'is_variant' => (bool)($attr['is_variant'] ?? false),
                            'display_order' => $attr['display_order'] ?? 0,
                            'group_id' => $attr['group_id'] ?? null,
                            'group_name' => $attr['group_name'] ?? 'Other',
                            'group_display_order' => $attr['group_display_order'] ?? 999
                        ];
                        
                        // Decode JSON options for select type attributes
                        if ($attrData['attribute_type'] === 'select' && !empty($attrData['attribute_options'])) {
                            $decoded = json_decode($attrData['attribute_options'], true);
                            $attrData['options'] = (is_array($decoded)) ? $decoded : [];
                        } else {
                            $attrData['options'] = [];
                        }
                        
                        $result[] = $attrData;
                    }
                    
                    return $result;
                }
                
                /**
                 * Save product attribute value
                 * @param int $productId
                 * @param int $attributeId
                 * @param string $value
                 * @return bool
                 */
            public function saveProductAttribute($productId, $attributeId, $value) {
                    // Check if attribute already exists
                    $existing = $this->query(
                        "SELECT product_attribute_id FROM product_attributes WHERE product_id = ? AND attribute_id = ?",
                        [$productId, $attributeId]
                    )->fetch();
                    
                    if ($existing) {
                        // Update existing
                        return $this->query(
                            "UPDATE product_attributes SET attribute_value = ?, updated_at = ? WHERE product_attribute_id = ?",
                            [$value, date('Y-m-d H:i:s'), $existing['product_attribute_id']]
                        ) !== false;
                    } else {
                        // Insert new
                        return $this->query(
                            "INSERT INTO product_attributes (product_id, attribute_id, attribute_value, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
                            [$productId, $attributeId, $value, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
                        ) !== false;
                    }
                }
                
                /**
                 * Delete product attribute
                 * @param int $productId
                 * @param int $attributeId
                 * @return bool
                 */
            public function deleteProductAttribute($productId, $attributeId) {
                    return $this->query(
                        "DELETE FROM product_attributes WHERE product_id = ? AND attribute_id = ?",
                        [$productId, $attributeId]
                    ) !== false;
                }
                
                /**
                 * Delete all attributes for a product
                 * @param int $productId
                 * @return bool
                 */
            public function deleteAllProductAttributes($productId) {
                    return $this->query(
                        "DELETE FROM product_attributes WHERE product_id = ?",
                        [$productId]
                    ) !== false;
                }
}

