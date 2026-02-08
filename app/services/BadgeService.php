<?php
/**
 * BadgeService
 * Handles product badge logic, priorities, and conflict resolution
 */

class BadgeService {
    private $db;
    private $settingsModel;
    
    // Default Priorities (Lower = Higher Priority)
    const PRIORITY_TOP_SELLING = 1;
    const PRIORITY_DISCOUNT = 2;
    const PRIORITY_RATING = 3;
    const PRIORITY_LOW_STOCK = 4;
    const PRIORITY_NEW = 5;

    // Badge Types
    const TYPE_PERFORMANCE = 'performance';
    const TYPE_PRICING = 'pricing';
    const TYPE_TRUST = 'trust';
    const TYPE_URGENCY = 'urgency';
    const TYPE_TIME = 'time';

    public function __construct() {
        $this->db = Database::getInstance();
        
        // Load Settings Model
        if (file_exists(APP_PATH . '/models/Settings.php')) {
            require_once APP_PATH . '/models/Settings.php';
            $this->settingsModel = new Settings();
        }
    }

    /**
     * Get resolved badges for a product
     * 
     * @param array $product Product data
     * @return array List of badges to display
     */
    public function getBadges($product) {
        // 1. Collect Eligible Badges
        $eligibleBadges = $this->getEligibleBadges($product);

        // 2. Sort by Priority
        $sortedBadges = $this->sortByPriority($eligibleBadges);

        // 3. Apply Conflict Resolution
        $finalBadges = $this->resolveConflicts($sortedBadges);

        return $finalBadges;
    }

    /**
     * Determine which badges are eligible for the product
     */
    private function getEligibleBadges($product) {
        $badges = [];
        $priorities = $this->getBadgePriorities();

        // 1. Top Selling (Performance)
        // Rule: Min Orders OR Manual Flag
        $minOrders = $this->getTopSellingMinOrders();
        $soldQty = isset($product['sold_qty']) ? intval($product['sold_qty']) : 0;
        
        if ($soldQty >= $minOrders && $minOrders > 0) {
             // Check if enabled in settings (default true)
            if ($this->isBadgeEnabled('top_selling')) {
                $badges[] = [
                    'id' => 'top_selling',
                    'label' => 'Top Selling',
                    'icon' => '🔥',
                    'type' => self::TYPE_PERFORMANCE,
                    'priority' => $priorities['top_selling'] ?? self::PRIORITY_TOP_SELLING,
                    'class' => 'bg-gradient-to-r from-orange-500 to-red-500 text-white'
                ];
            }
        }

        // 2. Discount (Pricing)
        $price = $product['sale_price'] ?? $product['price'];
        $originalPrice = $product['price'];
        $hasDiscount = !empty($product['sale_price']) && $product['sale_price'] < $product['price'];
        
        if ($hasDiscount) {
            $discountPercent = round((($originalPrice - $price) / $originalPrice) * 100);
            $highDiscountThreshold = $this->getHighDiscountThreshold();
            
            // Only show badge if it meets the High Discount Threshold
            if ($discountPercent >= $highDiscountThreshold && $this->isBadgeEnabled('discount')) {
                $badges[] = [
                    'id' => 'discount',
                    'label' => $discountPercent . '% OFF',
                    'value' => $discountPercent,
                    'icon' => '💸',
                    'type' => self::TYPE_PRICING,
                    'priority' => $priorities['discount'] ?? self::PRIORITY_DISCOUNT,
                    'class' => 'bg-gradient-to-r from-red-500 to-pink-500 text-white'
                ];
            }
        }

        // 3. Rating (Trust)
        // Rule: Min Rating AND Min Review Count
        $rating = isset($product['rating']) ? floatval($product['rating']) : 0;
        $reviewCount = isset($product['review_count']) ? intval($product['review_count']) : 0;
        
        $minRating = $this->getRatingMinVal();
        $minReviews = $this->getRatingMinCount();
        
        if ($rating >= $minRating && $reviewCount >= $minReviews && $this->isBadgeEnabled('rating')) {
            $badges[] = [
                'id' => 'rating',
                'label' => number_format($rating, 1),
                'icon' => '⭐',
                'type' => self::TYPE_TRUST,
                'priority' => $priorities['rating'] ?? self::PRIORITY_RATING,
                'class' => 'bg-yellow-100 text-yellow-800 border border-yellow-200'
            ];
        }

        // 4. Low Stock (Urgency)
        // Rule: Stock <= Threshold
        $stock = isset($product['stock_quantity']) ? intval($product['stock_quantity']) : 0;
        $lowStockThreshold = $this->getLowStockThreshold();
        
        if ($stock > 0 && $stock <= $lowStockThreshold && $this->isBadgeEnabled('low_stock')) {
             $badges[] = [
                'id' => 'low_stock',
                'label' => 'Low Stock',
                'icon' => '⚠️',
                'type' => self::TYPE_URGENCY,
                'priority' => $priorities['low_stock'] ?? self::PRIORITY_LOW_STOCK,
                'class' => 'bg-red-100 text-red-800 border border-red-200'
            ];
        }

        // 5. New Arrival (Time-based)
        // Check both specific flag and date (e.g., last 14 days)
        $isNew = !empty($product['is_new']) || !empty($product['is_new_arrival']);
        if (!$isNew && isset($product['created_at'])) {
            $daysOld = (time() - strtotime($product['created_at'])) / (60 * 60 * 24);
            if ($daysOld <= 14) { // Default 14 days
                $isNew = true;
            }
        }
        
        if ($isNew && $this->isBadgeEnabled('new')) {
             $badges[] = [
                'id' => 'new',
                'label' => 'New',
                'icon' => '🆕',
                'type' => self::TYPE_TIME,
                'priority' => $priorities['new'] ?? self::PRIORITY_NEW,
                'class' => 'bg-blue-600 text-white'
            ];
        }

        return $badges;
    }

    /**
     * Sort badges by priority (Lower number = Higher priority)
     */
    private function sortByPriority($badges) {
        usort($badges, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
        return $badges;
    }

    /**
     * Resolve conflicts based on business rules
     */
    private function resolveConflicts($badges) {
        $finalBadges = [];
        $maxBadges = $this->getMaxBadges();
        
        // Flags to track what we have admitted
        $hasTopSelling = false;
        $hasRating = false;
        $highDiscountThreshold = $this->getHighDiscountThreshold();

        foreach ($badges as $badge) {
            // Stop if we reached max badges
            if (count($finalBadges) >= $maxBadges) {
                break;
            }

            // --- Rule B: Pricing vs Performance ---
            // "If Top Selling exists → it always wins" 
            // This is handled by Sort Order (Top Selling is Priority 1 vs Discount Priority 2)
            // But we need to ensure they can coexist if space permits.
            
            // --- Rule C: Trust vs Urgency ---
            // "Rating > Low Stock. Low stock shows only if rating is not shown"
            if ($badge['id'] === 'low_stock' && $hasRating) {
                // Skip Low Stock if Rating is already serving as the Trust/Status indicator
                continue;
            }

            // --- Rule D: Discount Dominance ---
            // "If discount >= threshold (e.g. 40%), % OFF can override Rating"
            // Since Discount (2) is higher priority than Rating (3), it naturally comes before.
            // But if we have [Top Selling, Discount, Rating] and max=2:
            // 1. Top Selling -> Added.
            // 2. Discount -> Added.
            // 3. Rating -> Dropped (Max reached).
            // This satisfies the rule "Discount is shown... Rating dropped".
            
            // Special Case: If for some reason Rating was higher priority, check override.
            // With current priorities, Discount always beats Rating.

            // Additional Check: "New + Top Selling (New gets dropped)"
            // Since Priority(TopSelling) < Priority(New), TopSelling comes first.
            // If Max=2, and we have [Top Selling, Discount, New], New is dropped. Correct.

            $finalBadges[] = $badge;

            if ($badge['id'] === 'top_selling') $hasTopSelling = true;
            if ($badge['id'] === 'rating') $hasRating = true;
        }

        return $finalBadges;
    }

    // --- Helpers for Settings ---

    private function getBadgePriorities() {
        $default = [
            'top_selling' => self::PRIORITY_TOP_SELLING,
            'discount' => self::PRIORITY_DISCOUNT,
            'rating' => self::PRIORITY_RATING,
            'low_stock' => self::PRIORITY_LOW_STOCK,
            'new' => self::PRIORITY_NEW
        ];
        
        if ($this->settingsModel) {
            $stored = $this->settingsModel->get('badge_priorities');
            if ($stored) {
                $decoded = is_string($stored) ? json_decode($stored, true) : $stored;
                if (is_array($decoded)) {
                    return array_merge($default, $decoded);
                }
            }
        }
        return $default;
    }

    private function isBadgeEnabled($badgeId) {
        if ($this->settingsModel) {
            $disabledBadges = $this->settingsModel->get('disabled_badges'); // Stored as array or comma-list
            if ($disabledBadges) {
                $disabled = is_array($disabledBadges) ? $disabledBadges : json_decode($disabledBadges, true);
                if (is_array($disabled) && in_array($badgeId, $disabled)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function getMaxBadges() {
        if ($this->settingsModel) {
            return (int) $this->settingsModel->get('badge_max_count', 2);
        }
        return 2;
    }

    private function getHighDiscountThreshold() {
        if ($this->settingsModel) {
            return (int) $this->settingsModel->get('badge_high_discount_threshold', 40);
        }
        return 40;
    }
    
    private function getTopSellingMinOrders() {
        if ($this->settingsModel) {
            return (int) $this->settingsModel->get('badge_top_selling_min_orders', 50);
        }
        return 50;
    }

    private function getRatingMinVal() {
        if ($this->settingsModel) {
            return (float) $this->settingsModel->get('badge_rating_min_val', 4.0);
        }
        return 4.0;
    }

    private function getRatingMinCount() {
        if ($this->settingsModel) {
            return (int) $this->settingsModel->get('badge_rating_min_count', 10);
        }
        return 10;
    }

    private function getLowStockThreshold() {
        if ($this->settingsModel) {
            return (int) $this->settingsModel->get('badge_low_stock_threshold', 5);
        }
        return 5;
    }
}
