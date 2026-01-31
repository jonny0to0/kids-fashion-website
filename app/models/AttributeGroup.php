<?php
/**
 * Attribute Group Model
 * Handles attribute group-related database operations
 */

require_once APP_PATH . '/models/Model.php';

class AttributeGroup extends Model {
    protected $table = 'attribute_groups';
    protected $primaryKey = 'group_id';
    
    /**
     * Get all active attribute groups
     * @param bool $activeOnly Whether to return only active groups
     * @return array
     */
    public function getAll($activeOnly = true) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY display_order ASC, group_name ASC";
        
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get attribute group by ID
     * @param int $groupId
     * @return array|null
     */
    public function getById($groupId) {
        return $this->find($groupId);
    }
    
    /**
     * Get attributes in a group
     * @param int $groupId
     * @param bool $activeOnly Whether to return only active attributes
     * @return array
     */
    public function getAttributes($groupId, $activeOnly = true) {
        $sql = "SELECT * FROM category_attributes WHERE group_id = ?";
        $params = [$groupId];
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY display_order ASC, attribute_name ASC";
        
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
     * Get groups assigned to a category (including inherited)
     * @param int $categoryId
     * @param bool $includeInherited Whether to include inherited groups
     * @return array Array of group IDs
     */
    public function getCategoryGroups($categoryId, $includeInherited = true) {
        $sql = "SELECT cag.group_id, cag.is_inherited, ag.group_name, ag.description
                FROM category_attribute_groups cag
                INNER JOIN attribute_groups ag ON cag.group_id = ag.group_id
                WHERE cag.category_id = ?";
        
        if (!$includeInherited) {
            $sql .= " AND cag.is_inherited = 0";
        }
        
        $sql .= " ORDER BY ag.display_order ASC, ag.group_name ASC";
        
        $stmt = $this->query($sql, [$categoryId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Assign attribute group to category
     * @param int $categoryId
     * @param int $groupId
     * @param bool $isInherited Whether this is inherited from parent
     * @return bool
     */
    public function assignToCategory($categoryId, $groupId, $isInherited = false) {
        // Check if already assigned
        $existing = $this->query(
            "SELECT mapping_id FROM category_attribute_groups WHERE category_id = ? AND group_id = ?",
            [$categoryId, $groupId]
        )->fetch();
        
        if ($existing) {
            // Update existing
            return $this->query(
                "UPDATE category_attribute_groups SET is_inherited = ? WHERE mapping_id = ?",
                [$isInherited ? 1 : 0, $existing['mapping_id']]
            ) !== false;
        } else {
            // Insert new
            return $this->query(
                "INSERT INTO category_attribute_groups (category_id, group_id, is_inherited) VALUES (?, ?, ?)",
                [$categoryId, $groupId, $isInherited ? 1 : 0]
            ) !== false;
        }
    }
    
    /**
     * Remove attribute group from category
     * @param int $categoryId
     * @param int $groupId
     * @return bool
     */
    public function removeFromCategory($categoryId, $groupId) {
        return $this->query(
            "DELETE FROM category_attribute_groups WHERE category_id = ? AND group_id = ?",
            [$categoryId, $groupId]
        ) !== false;
    }
    
    /**
     * Create a new attribute group
     * @param array $data
     * @return int|false
     */
    public function createGroup($data) {
        return $this->create($data);
    }
    
    /**
     * Update attribute group
     * @param int $groupId
     * @param array $data
     * @return bool
     */
    public function updateGroup($groupId, $data) {
        return $this->update($groupId, $data);
    }
    
    /**
     * Delete attribute group (will cascade delete attributes and category mappings)
     * @param int $groupId
     * @return bool
     */
    public function deleteGroup($groupId) {
        return $this->delete($groupId);
    }
}

