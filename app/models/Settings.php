<?php
/**
 * Settings Model
 * Handles application settings with encryption support
 */

class Settings extends Model {
    protected $table = 'settings';
    
    // Encryption key (should be stored in config in production)
    private $encryptionKey;
    
    public function __construct() {
        parent::__construct();
        // Use a default key or get from config
        $this->encryptionKey = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : 'default_key_change_in_production';
        
        // Verify table exists (non-blocking - errors will be caught in individual methods)
        // Don't throw exception here as it prevents model instantiation
        $this->verifyTableExists();
    }
    
    /**
     * Verify that the settings table exists
     * Returns true if table exists, false otherwise
     */
    private function verifyTableExists() {
        try {
            // Check if database connection is valid
            if (!$this->db) {
                error_log("Settings model error: Database connection is null");
                return false;
            }
            
            $stmt = $this->db->query("SHOW TABLES LIKE '{$this->table}'");
            $tableExists = $stmt->rowCount() > 0;
            
            if (!$tableExists) {
                error_log("Settings model error: Table '{$this->table}' does not exist. Please run the settings migration.");
                return false;
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Settings model error: Failed to verify table existence: " . $e->getMessage());
            error_log("Settings model error: PDO Error Code: " . $e->getCode());
            // Don't throw here - let individual methods handle errors
            return false;
        } catch (Exception $e) {
            error_log("Settings model error: Exception in verifyTableExists: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get setting value by key
     */
    public function get($key, $default = null) {
        $setting = $this->findOne(['key' => $key]);
        
        if (!$setting) {
            return $default;
        }
        
        $value = $setting['value'];
        
        // Decrypt if needed
        if ($setting['is_encrypted'] && !empty($value)) {
            $value = $this->decrypt($value);
        }
        
        // Convert based on type
        return $this->convertValue($value, $setting['type']);
    }
    
    /**
     * Set setting value
     * Returns the ID of the created/updated record, or false on failure
     */
    public function set($key, $value, $group = 'general', $type = 'text', $isEncrypted = false, $description = null) {
        try {
            // Encrypt if needed
            if ($isEncrypted && !empty($value)) {
                $value = $this->encrypt($value);
            }
            
            // Check if setting exists
            $existing = $this->findOne(['key' => $key]);
            
            if ($existing) {
                // Update existing
                $result = $this->update($existing['id'], [
                    'value' => $value,
                    'group' => $group,
                    'type' => $type,
                    'is_encrypted' => $isEncrypted ? 1 : 0,
                    'description' => $description
                ]);
                return $result ? $existing['id'] : false;
            } else {
                // Create new
                return $this->create([
                    'key' => $key,
                    'value' => $value,
                    'group' => $group,
                    'type' => $type,
                    'is_encrypted' => $isEncrypted ? 1 : 0,
                    'description' => $description
                ]);
            }
        } catch (PDOException $e) {
            error_log("Settings::set - PDOException for key '{$key}': " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Settings::set - Exception for key '{$key}': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all settings by group
     */
    public function getByGroup($group) {
        try {
            // Check database connection first
            if (!$this->db) {
                throw new Exception("Database connection is not available. Please check your database configuration.");
            }
            
            $settings = $this->findAll(['group' => $group]);
            $result = [];
            
            foreach ($settings as $setting) {
                $value = $setting['value'];
                
                // Decrypt if needed
                if ($setting['is_encrypted'] && !empty($value)) {
                    $value = $this->decrypt($value);
                }
                
                $result[$setting['key']] = [
                    'value' => $this->convertValue($value, $setting['type']),
                    'type' => $setting['type'],
                    'is_encrypted' => (bool)$setting['is_encrypted'],
                    'description' => $setting['description']
                ];
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Settings::getByGroup - PDOException for group '{$group}': " . $e->getMessage());
            error_log("Settings::getByGroup - PDO Error Code: " . $e->getCode());
            error_log("Settings::getByGroup - SQL State: " . $e->getCode());
            
            // Check for specific error types
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            // Connection errors (2002, 2003, 1045, 1049)
            if (in_array($errorCode, ['2002', '2003', '1045', '1049']) || 
                strpos($errorMessage, 'Connection') !== false ||
                strpos($errorMessage, 'Access denied') !== false ||
                strpos($errorMessage, 'Unknown database') !== false) {
                throw new Exception("Database connection error. Please check your database configuration. Error: " . (ENVIRONMENT === 'development' ? $errorMessage : 'Check database credentials and connection'));
            }
            
            // Table doesn't exist errors (1146)
            if ($errorCode == '42S02' || strpos($errorMessage, "doesn't exist") !== false || strpos($errorMessage, "Unknown table") !== false) {
                throw new Exception("Settings table does not exist. Please run the database migration: database/add_settings_system.sql");
            }
            
            // Re-throw with more context
            throw new Exception("Database error while loading settings: " . (ENVIRONMENT === 'development' ? $errorMessage : 'Please check database configuration'));
        } catch (Exception $e) {
            error_log("Settings::getByGroup - Exception for group '{$group}': " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all settings
     */
    public function getAll() {
        $settings = $this->findAll([], '`group` ASC, `key` ASC');
        $result = [];
        
        foreach ($settings as $setting) {
            $value = $setting['value'];
            
            // Decrypt if needed
            if ($setting['is_encrypted'] && !empty($value)) {
                $value = $this->decrypt($value);
            }
            
            $result[$setting['key']] = [
                'value' => $this->convertValue($value, $setting['type']),
                'type' => $setting['type'],
                'group' => $setting['group'],
                'is_encrypted' => (bool)$setting['is_encrypted'],
                'description' => $setting['description']
            ];
        }
        
        return $result;
    }
    
    /**
     * Update multiple settings at once
     * Returns array with 'success', 'message', and 'error' keys
     */
    public function updateBatch($settings) {
        $errors = [];
        $successCount = 0;
        $totalCount = count($settings);
        
        foreach ($settings as $key => $data) {
            try {
                $value = $data['value'] ?? '';
                $group = $data['group'] ?? 'general';
                $type = $data['type'] ?? 'text';
                $isEncrypted = $data['is_encrypted'] ?? false;
                $description = $data['description'] ?? null;
                
                $result = $this->set($key, $value, $group, $type, $isEncrypted, $description);
                
                if ($result === false || $result === null) {
                    $errors[] = "Failed to save setting '{$key}'";
                    error_log("Settings::updateBatch - Failed to save setting '{$key}': " . print_r($data, true));
                } else {
                    $successCount++;
                }
            } catch (PDOException $e) {
                $errorMsg = "Database error saving setting '{$key}': " . $e->getMessage();
                $errors[] = $errorMsg;
                error_log("Settings::updateBatch - PDOException: " . $errorMsg);
            } catch (Exception $e) {
                $errorMsg = "Error saving setting '{$key}': " . $e->getMessage();
                $errors[] = $errorMsg;
                error_log("Settings::updateBatch - Exception: " . $errorMsg);
            }
        }
        
        if (empty($errors)) {
            return [
                'success' => true,
                'message' => "Successfully saved {$successCount} setting(s)",
                'saved' => $successCount,
                'total' => $totalCount
            ];
        } else {
            return [
                'success' => false,
                'message' => "Failed to save some settings. " . implode('; ', array_slice($errors, 0, 3)) . ($totalCount > 3 ? '...' : ''),
                'error' => implode('; ', $errors),
                'saved' => $successCount,
                'total' => $totalCount,
                'errors' => $errors
            ];
        }
    }
    
    /**
     * Convert value based on type
     */
    private function convertValue($value, $type) {
        if ($value === null || $value === '') {
            return '';
        }
        
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (float)$value : 0;
            case 'checkbox':
                return $value === '1' || $value === 1 || $value === true || $value === 'true';
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
    
    /**
     * Encrypt value
     */
    private function encrypt($value) {
        if (empty($value)) {
            return '';
        }
        
        $method = 'AES-256-CBC';
        $ivLength = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($value, $method, $this->encryptionKey, 0, $iv);
        
        return base64_encode($encrypted . '::' . $iv);
    }
    
    /**
     * Decrypt value
     */
    private function decrypt($value) {
        if (empty($value)) {
            return '';
        }
        
        try {
            $method = 'AES-256-CBC';
            $data = base64_decode($value);
            list($encrypted, $iv) = explode('::', $data, 2);
            
            return openssl_decrypt($encrypted, $method, $this->encryptionKey, 0, $iv);
        } catch (Exception $e) {
            return '';
        }
    }
    
    /**
     * Get setting with masked value for display (for encrypted fields)
     */
    public function getMasked($key, $default = null) {
        $setting = $this->findOne(['key' => $key]);
        
        if (!$setting) {
            return $default;
        }
        
        if ($setting['is_encrypted'] && !empty($setting['value'])) {
            return '••••••••••••';
        }
        
        return $this->get($key, $default);
    }
}

