<?php
/**
 * Database Connection
 * PDO connection settings for the QuickCourse platform
 */

// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'course');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Connection failed: " . $e->getMessage());
            } else {
                die("Database connection error. Please try again later.");
            }
        }
    }
    
    // Get singleton instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Get PDO connection
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Execute a query with params
    public static function query($sql, $params = []) {
        $db = self::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    // Fetch a single row
    public static function fetchOne($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetch();
    }
    
    // Fetch all rows
    public static function fetchAll($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
    
    // Insert and get last ID
    public static function insert($sql, $params = []) {
        $db = self::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $db->lastInsertId();
    }
    
    // Update and get affected rows
    public static function update($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
    
    // Begin a transaction
    public static function beginTransaction() {
        return self::getInstance()->getConnection()->beginTransaction();
    }
    
    // Commit a transaction
    public static function commit() {
        return self::getInstance()->getConnection()->commit();
    }
    
    // Rollback a transaction
    public static function rollback() {
        return self::getInstance()->getConnection()->rollBack();
    }
}