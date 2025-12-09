<?php
/**
 * User Model
 * Handles user-related database operations
 */

class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    
    /**
     * Register new user
     */
    public function register($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($email, $password) {
        $user = $this->findOne(['email' => $email, 'status' => USER_STATUS_ACTIVE]);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        // Don't allow password update through this method
        unset($data['password']);
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($userId, $data);
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeUserId) {
            $sql .= " AND user_id != ?";
            $params[] = $excludeUserId;
        }
        
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        
        return ($result['total'] ?? 0) > 0;
    }
    
    /**
     * Get user by email
     */
    public function findByEmail($email) {
        return $this->findOne(['email' => $email]);
    }
}

