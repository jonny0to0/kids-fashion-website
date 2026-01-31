<?php
/**
 * Hero Banner Model
 * Manages hero banner data and business logic
 */

class HeroBanner extends Model {
    protected $table = 'hero_banners';
    protected $primaryKey = 'banner_id';
    
    /**
     * Get active banners for frontend
     * Filters by status, date range, device type, and target
     */
    public function getActiveBanners($targetType = 'homepage', $targetId = null, $deviceType = 'both', $limit = 5) {
        $now = date('Y-m-d H:i:s');
        
        // Build the SQL query
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'active'
                AND (start_date IS NULL OR start_date <= ?)
                AND (end_date IS NULL OR end_date >= ?)
                AND target_type = ?";
        
        $params = [$now, $now, $targetType];
        
        // Handle device visibility - if deviceType is 'both', show all, otherwise filter by device or 'both'
        if ($deviceType !== 'both') {
            $sql .= " AND (device_visibility = ? OR device_visibility = 'both')";
            $params[] = $deviceType;
        }
        
        // Handle target_id
        if ($targetId !== null) {
            $sql .= " AND (target_id = ? OR target_id IS NULL)";
            $params[] = $targetId;
        } else {
            $sql .= " AND target_id IS NULL";
        }
        
        // Cast limit to int and insert directly (PDO doesn't support binding LIMIT in all MySQL configurations)
        $limit = (int)$limit;
        $sql .= " ORDER BY priority DESC, display_order ASC, created_at DESC LIMIT {$limit}";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log query results in development mode for debugging
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                error_log("HeroBanner::getActiveBanners() - Query executed. Found " . count($results) . " banners for target_type='{$targetType}', deviceType='{$deviceType}', targetId=" . ($targetId ?? 'NULL'));
                if (count($results) > 0) {
                    foreach ($results as $banner) {
                        error_log("  - Banner ID {$banner['banner_id']}: '{$banner['title']}' - desktop_image: " . ($banner['desktop_image'] ?? 'empty') . ", mobile_image: " . ($banner['mobile_image'] ?? 'empty'));
                    }
                }
            }
            
            // Ensure boolean fields are properly converted
            foreach ($results as &$banner) {
                if (isset($banner['auto_slide_enabled'])) {
                    $banner['auto_slide_enabled'] = (bool)$banner['auto_slide_enabled'];
                }
                if (isset($banner['slide_duration'])) {
                    $banner['slide_duration'] = (int)($banner['slide_duration'] ?? 5000);
                }
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("HeroBanner::getActiveBanners() Error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            return [];
        }
    }
    
    /**
     * Get all banners for admin
     */
    public function getAllBanners($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['target_type'])) {
            $sql .= " AND target_type = ?";
            $params[] = $filters['target_type'];
        }
        
        $sql .= " ORDER BY priority DESC, display_order ASC, created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Check for overlapping campaigns
     * Validates that banners don't overlap in date ranges with same priority
     */
    public function checkOverlap($bannerId, $startDate, $endDate, $priority, $targetType, $targetId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE banner_id != ? 
                AND status = 'active'
                AND priority = ?
                AND target_type = ?
                AND (
                    (start_date <= ? AND (end_date >= ? OR end_date IS NULL))
                    OR (start_date IS NULL AND end_date >= ?)
                    OR (start_date IS NULL AND end_date IS NULL)
                )";
        
        $params = [$bannerId, $priority, $targetType, $endDate, $startDate, $startDate];
        
        if ($targetId !== null) {
            $sql .= " AND (target_id = ? OR target_id IS NULL)";
            $params[] = $targetId;
        } else {
            $sql .= " AND target_id IS NULL";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    /**
     * Update display order (for drag-and-drop)
     */
    public function updateDisplayOrder($bannerId, $newOrder) {
        return $this->update($bannerId, ['display_order' => $newOrder]);
    }
    
    /**
     * Bulk update display orders
     */
    public function bulkUpdateDisplayOrders($orders) {
        $this->db->beginTransaction();
        try {
            foreach ($orders as $bannerId => $order) {
                $this->update($bannerId, ['display_order' => (int)$order]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Toggle banner status
     */
    public function toggleStatus($bannerId) {
        $banner = $this->find($bannerId);
        if (!$banner) {
            return false;
        }
        
        $newStatus = $banner['status'] === 'active' ? 'inactive' : 'active';
        return $this->update($bannerId, ['status' => $newStatus]);
    }
    
    /**
     * Validate banner data
     */
    public function validate($data, $bannerId = null) {
        $errors = [];
        
        // Required fields
        if (empty($data['title'])) {
            $errors[] = 'Title is required';
        }
        
        // Images are only required if image_enabled is true (default to true for backward compatibility)
        $imageEnabled = isset($data['image_enabled']) ? (bool)$data['image_enabled'] : true;
        if ($imageEnabled) {
            if (empty($data['desktop_image']) && empty($data['desktop_image_upload'])) {
                $errors[] = 'Desktop image is required when images are enabled';
            }
            
            if (empty($data['mobile_image']) && empty($data['mobile_image_upload'])) {
                $errors[] = 'Mobile image is required when images are enabled';
            }
        }
        
        // Date validation
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
                $errors[] = 'End date must be after start date';
            }
        }
        
        // Priority validation (0-100)
        if (isset($data['priority']) && ($data['priority'] < 0 || $data['priority'] > 100)) {
            $errors[] = 'Priority must be between 0 and 100';
        }
        
        // Slide duration validation (4000-6000ms)
        if (isset($data['slide_duration'])) {
            $duration = (int)$data['slide_duration'];
            if ($duration < 4000 || $duration > 6000) {
                $errors[] = 'Slide duration must be between 4000 and 6000 milliseconds';
            }
        }
        
        // CTA URL validation (accepts both relative and absolute URLs)
        if (!empty($data['cta_url'])) {
            $ctaUrl = trim($data['cta_url']);
            // Check if it's a relative URL (starts with /) or absolute URL (starts with http:// or https://)
            $isRelativeUrl = (strpos($ctaUrl, '/') === 0);
            $isAbsoluteUrl = (filter_var($ctaUrl, FILTER_VALIDATE_URL) !== false);
            
            // Allow relative URLs (starting with /) or valid absolute URLs
            if (!$isRelativeUrl && !$isAbsoluteUrl) {
                $errors[] = 'CTA URL must be a valid relative URL (starting with /) or absolute URL (http:// or https://)';
            }
        }
        
        // Check for overlapping campaigns
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $overlap = $this->checkOverlap(
                $bannerId ?? 0,
                $data['start_date'],
                $data['end_date'],
                $data['priority'] ?? 0,
                $data['target_type'] ?? 'homepage',
                $data['target_id'] ?? null
            );
            
            if ($overlap) {
                $errors[] = 'Banner overlaps with another active banner of the same priority';
            }
        }
        
        return $errors;
    }
    
    /**
     * Get banner count by status
     */
    public function getCountByStatus($status = 'active') {
        return $this->count(['status' => $status]);
    }
    
    /**
     * Get next display order
     */
    public function getNextDisplayOrder() {
        $stmt = $this->db->query("SELECT MAX(display_order) as max_order FROM {$this->table}");
        $result = $stmt->fetch();
        return ($result['max_order'] ?? 0) + 1;
    }
}

