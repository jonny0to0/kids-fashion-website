<?php
/**
 * VariantAttribute Model
 * Handles individual attributes for product variants
 */

class VariantAttribute extends Model
{
    protected $table = 'variant_attributes';
    protected $primaryKey = 'id';

    /**
     * Get attributes for a variant
     */
    public function getByVariantId($variantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE variant_id = ?";
        $stmt = $this->query($sql, [$variantId]);
        return $stmt->fetchAll();
    }

    /**
     * Delete attributes for a variant
     */
    public function deleteByVariantId($variantId)
    {
        return $this->query("DELETE FROM {$this->table} WHERE variant_id = ?", [$variantId]);
    }
}
