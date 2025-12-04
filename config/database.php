<?php
/**
 * Database Configuration
 * Customer & Real-Time Trading Management System
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'fortestt_freelance');
define('DB_USER', 'fortestt_freelance2');
define('DB_PASS', 'fortestt_freelance2');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Trading Management System');
define('SITE_URL', '/');

// Session configuration
define('SESSION_LIFETIME', 3600); // 1 hour

/**
 * Database Connection Class using PDO
 */
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Get database connection
 * @return PDO
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
