<?php
/**
 * Base Model Class
 * Provides common database operations for all models
 */

require_once APP_PATH . '/config/database.php';

abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        try {
            $dbInstance = Database::getInstance();
            $this->db = $dbInstance->getConnection();
            
            // Verify connection is valid
            if (!$this->db) {
                error_log("Model::__construct - Database connection is null for table: " . ($this->table ?? 'unknown'));
                throw new Exception("Database connection failed. Please check your database configuration.");
            }
        } catch (PDOException $e) {
            error_log("Model::__construct - PDOException: " . $e->getMessage());
            error_log("Model::__construct - PDO Error Code: " . $e->getCode());
            throw new Exception("Database connection error: " . (ENVIRONMENT === 'development' ? $e->getMessage() : 'Please check database configuration'));
        } catch (Exception $e) {
            error_log("Model::__construct - Exception: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Find all records
     */
    public function findAll($conditions = [], $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM `{$this->table}`";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "`{$key}` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Find one record by conditions
     */
    public function findOne($conditions) {
        $where = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $where[] = "`{$key}` = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT * FROM `{$this->table}` WHERE " . implode(" AND ", $where) . " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Insert new record
     */
    public function create($data) {
        try {
            $fields = array_keys($data);
            $escapedFields = array_map(function($field) {
                return "`{$field}`";
            }, $fields);
            $placeholders = array_fill(0, count($fields), '?');
            
            $sql = "INSERT INTO `{$this->table}` (" . implode(', ', $escapedFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Model::create - PDOException for table '{$this->table}': " . $e->getMessage());
            error_log("Model::create - SQL: " . ($sql ?? 'N/A'));
            error_log("Model::create - Data: " . print_r($data, true));
            throw $e;
        }
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        try {
            if (empty($data)) {
                return false;
            }
            
            $fields = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                $fields[] = "`{$key}` = ?";
                $params[] = $value;
            }
            
            $params[] = $id;
            $sql = "UPDATE `{$this->table}` SET " . implode(', ', $fields) . " WHERE `{$this->primaryKey}` = ?";
            $stmt = $this->db->prepare($sql);
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Model::update - Update failed for table '{$this->table}', ID: {$id}");
                error_log("Model::update - SQL Error: " . ($errorInfo[2] ?? 'Unknown error'));
                error_log("Model::update - SQL: " . $sql);
                error_log("Model::update - Params: " . print_r($params, true));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Model::update - PDOException for table '{$this->table}', ID: {$id}: " . $e->getMessage());
            error_log("Model::update - SQL: " . ($sql ?? 'N/A'));
            error_log("Model::update - Data: " . print_r($data, true));
            throw $e;
        }
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Count records
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}`";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "`{$key}` = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Execute custom query
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

