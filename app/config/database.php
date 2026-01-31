<?php
/**
 * Database Configuration and Connection
 * Uses PDO for secure database operations
 */

class Database {
    private static $instance = null;
    private $connection;
    
    private $host = 'localhost';
    private $dbname = 'kids_bazaar';
    private $username = 'phpmyadmin';
    private $password = 'Vynex@001';
    private $charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false, // Don't use persistent connections
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            // Verify connection is actually working
            $this->connection->query("SELECT 1");
        } catch (PDOException $e) {
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            error_log("Database Connection Error - Code: {$errorCode}, Message: {$errorMessage}");
            error_log("Database Connection Error - Host: {$this->host}, Database: {$this->dbname}, Username: {$this->username}");
            
            // Set connection to null so we can detect it
            $this->connection = null;
            
            if (ENVIRONMENT === 'development') {
                die("Database Connection Error: " . $errorMessage . " (Code: {$errorCode})");
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        // Verify connection is still valid
        if ($this->connection === null) {
            throw new Exception("Database connection is not available. Please check your database configuration.");
        }
        
        try {
            // Test connection with a simple query
            $this->connection->query("SELECT 1");
        } catch (PDOException $e) {
            error_log("Database connection lost: " . $e->getMessage());
            $this->connection = null;
            throw new Exception("Database connection lost. Please check your database configuration.");
        }
        
        return $this->connection;
    }
    
    /**
     * Test database connection
     */
    public function testConnection() {
        try {
            if ($this->connection === null) {
                return ['success' => false, 'message' => 'Database connection is null'];
            }
            
            $this->connection->query("SELECT 1");
            return ['success' => true, 'message' => 'Database connection is working'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()];
        }
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Global database instance
$db = Database::getInstance()->getConnection();

