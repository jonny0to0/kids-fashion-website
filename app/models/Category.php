<?php
/**
 * Category Model
 * Handles category-related database operations
 */

class Category extends Model {
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    
    /**
     * Get all active categories
     */
    public function getAllActive() {
        return $this->findAll(['is_active' => true], 'display_order ASC');
    }
    
    /**
     * Get category by slug
     */
    public function findBySlug($slug) {
        return $this->findOne(['slug' => $slug, 'is_active' => true]);
    }
    
    /**
     * Get categories with product count
     */
    public function getWithProductCount() {
        $sql = "SELECT c.*, COUNT(p.product_id) as product_count
                FROM {$this->table} c
                LEFT JOIN products p ON c.category_id = p.category_id AND p.status = ?
                WHERE c.is_active = 1
                GROUP BY c.category_id
                ORDER BY c.display_order ASC";
        
        $stmt = $this->query($sql, [PRODUCT_STATUS_ACTIVE]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get parent categories
     */
    public function getParents() {
        return $this->findAll(['parent_id' => null, 'is_active' => true], 'display_order ASC');
    }
    
    /**
     * Get child categories
     */
    public function getChildren($parentId) {
        return $this->findAll(['parent_id' => $parentId, 'is_active' => true], 'display_order ASC');
    }
}

