<?php
/**
 * Address Model
 */

class Address extends Model {
    protected $table = 'addresses';
    protected $primaryKey = 'address_id';
    
    public function getUserAddresses($userId) {
        return $this->findAll(['user_id' => $userId], 'is_default DESC, created_at DESC');
    }
    
    public function getDefaultAddress($userId) {
        return $this->findOne(['user_id' => $userId, 'is_default' => true]);
    }
    
    public function setDefault($addressId, $userId) {
        // Remove default from other addresses
        $this->query("UPDATE {$this->table} SET is_default = 0 WHERE user_id = ?", [$userId]);
        
        // Set this as default
        return $this->update($addressId, ['is_default' => true]);
    }
    public function belongsToUser($addressId, $userId) {
        $address = $this->find($addressId);
        return $address && $address['user_id'] == $userId;
    }
}

