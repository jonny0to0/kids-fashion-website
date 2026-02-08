<?php
/**
 * ProductVariant Model
 * Handles product variants and their attributes
 */

class ProductVariant extends Model
{
    protected $table = 'product_variants';
    protected $primaryKey = 'variant_id';

    /**
     * Get variants for a product with attributes
     */
    public function getByProductId($productId, $activeOnly = false)
    {
        $sql = "SELECT pv.*, 
                GROUP_CONCAT(CONCAT(va.attribute_name, ': ', va.attribute_value) SEPARATOR ', ') as attributes_text
                FROM {$this->table} pv
                LEFT JOIN variant_attributes va ON pv.variant_id = va.variant_id
                WHERE pv.product_id = ?";

        if ($activeOnly) {
            $sql .= " AND pv.is_active = 1";
        }

        $sql .= " GROUP BY pv.variant_id ORDER BY pv.is_default DESC, pv.variant_id ASC";
        
        $stmt = $this->query($sql, [$productId]);
        $variants = $stmt->fetchAll();

        // Process attributes into a structured array
        foreach ($variants as &$variant) {
            $variant['attributes'] = $this->getAttributes($variant['variant_id']);
        }

        return $variants;
    }

    /**
     * Get formatted name (e.g., "Size: M, Color: Red")
     */
    public function getAttributes($variantId)
    {
        $sql = "SELECT attribute_name, attribute_value FROM variant_attributes WHERE variant_id = ?";
        $stmt = $this->query($sql, [$variantId]);
        $rows = $stmt->fetchAll();

        $attributes = [];
        foreach ($rows as $row) {
            $attributes[$row['attribute_name']] = $row['attribute_value'];
        }
        return $attributes;
    }

    /**
     * Get single variant with details
     */
    public function getVariant($variantId)
    {
        $variant = $this->find($variantId);
        if ($variant) {
            $variant['attributes'] = $this->getAttributes($variantId);
        }
        return $variant;
    }

    /**
     * Decrement stock
     */
    public function decrementStock($variantId, $quantity)
    {
        $sql = "UPDATE {$this->table} SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE variant_id = ?";
        return $this->query($sql, [$quantity, $variantId]);
    }
    /**
     * Set a variant as default and unset others for the same product
     */
    public function setDefaultVariant($productId, $variantId)
    {
        // First reset all to 0
        $sql = "UPDATE {$this->table} SET is_default = 0 WHERE product_id = ?";
        $this->query($sql, [$productId]);
        
        // Then set the selected one to 1
        $sql = "UPDATE {$this->table} SET is_default = 1 WHERE variant_id = ?";
        return $this->query($sql, [$variantId]);
    }
}
