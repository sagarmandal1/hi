<?php
/**
 * Database Configuration
 * Customer & Real-Time Trading Management System
 * বাংলাদেশের জন্য কাস্টমার ও ট্রেডিং ম্যানেজমেন্ট সিস্টেম
 * 
 * IMPORTANT: For production, consider using environment variables
 * or a separate config file outside the web root.
 */

// Prevent multiple inclusions
if (defined('DB_CONFIG_LOADED')) {
    return;
}
define('DB_CONFIG_LOADED', true);

// Debug mode - Set to false in production
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}

// Enable error reporting for development
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}

// Database credentials - Update these for your environment
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'fortestt_freelance');
if (!defined('DB_USER')) define('DB_USER', 'fortestt_freelance2');
if (!defined('DB_PASS')) define('DB_PASS', 'fortestt_freelance2');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Site configuration
if (!defined('SITE_NAME')) define('SITE_NAME', 'ট্রেডিং ম্যানেজমেন্ট সিস্টেম');
if (!defined('SITE_URL')) define('SITE_URL', '/');

// Language configuration
if (!defined('SITE_LANG')) define('SITE_LANG', 'bn'); // bn = Bengali/Bangla

// Currency configuration (Bangladesh Taka)
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '৳');
if (!defined('CURRENCY_CODE')) define('CURRENCY_CODE', 'BDT');

// Session configuration
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 3600); // 1 hour

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
