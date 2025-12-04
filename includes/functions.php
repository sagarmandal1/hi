<?php
/**
 * Common Functions
 * Customer & Real-Time Trading Management System
 * বাংলাদেশের জন্য কাস্টমার ও ট্রেডিং ম্যানেজমেন্ট সিস্টেম
 */

// Include language file
require_once __DIR__ . '/lang/bn.php';

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Redirect to a URL
 * @param string $url
 */
function redirect($url) {
    // Clean output buffer to prevent "headers already sent" error
    while (ob_get_level()) {
        ob_end_clean();
    }
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }
    return null;
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

/**
 * Format currency (Bangladesh Taka)
 * @param float $amount
 * @return string
 */
function formatCurrency($amount) {
    $symbol = defined('CURRENCY_SYMBOL') ? CURRENCY_SYMBOL : '৳';
    return $symbol . number_format((float)$amount, 2);
}

/**
 * Format date
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Generate unique deal number
 * @param PDO $db
 * @return string
 */
function generateDealNumber($db) {
    $stmt = $db->query("SELECT MAX(id) as max_id FROM deals");
    $result = $stmt->fetch();
    $nextId = ($result['max_id'] ?? 0) + 1;
    return 'DEAL-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
}

/**
 * Set flash message
 * @param string $type (success, error, warning, info)
 * @param string $message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        $alertClass = 'alert-info';
        switch ($type) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
        }
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

/**
 * Get date range based on filter
 * @param string $filter
 * @return array
 */
function getDateRange($filter) {
    $today = date('Y-m-d');
    $startDate = $today;
    $endDate = $today;

    switch ($filter) {
        case 'yesterday':
            $startDate = date('Y-m-d', strtotime('-1 day'));
            $endDate = $startDate;
            break;
        case 'this_week':
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
            break;
        case 'this_month':
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            break;
        case 'last_month':
            $startDate = date('Y-m-01', strtotime('first day of last month'));
            $endDate = date('Y-m-t', strtotime('last day of last month'));
            break;
        case 'this_year':
            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
            break;
        case 'custom':
            $startDate = $_GET['start_date'] ?? $today;
            $endDate = $_GET['end_date'] ?? $today;
            break;
        default: // today
            break;
    }

    return ['start' => $startDate, 'end' => $endDate];
}

/**
 * Calculate deal due amount
 * @param PDO $db
 * @param int $dealId
 * @return float
 */
function getDealDue($db, $dealId) {
    // Get deal total sell amount
    $stmt = $db->prepare("SELECT total_sell_amount FROM deals WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$dealId]);
    $deal = $stmt->fetch();
    
    if (!$deal) return 0;

    // Get total paid amount
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total_paid FROM payments WHERE deal_id = ? AND deleted_at IS NULL");
    $stmt->execute([$dealId]);
    $payment = $stmt->fetch();

    return $deal['total_sell_amount'] - $payment['total_paid'];
}

/**
 * Get customer total due
 * @param PDO $db
 * @param int $customerId
 * @return float
 */
function getCustomerDue($db, $customerId) {
    $stmt = $db->prepare("
        SELECT 
            COALESCE(SUM(d.total_sell_amount), 0) - COALESCE((
                SELECT SUM(p.amount) FROM payments p 
                WHERE p.customer_id = ? AND p.deleted_at IS NULL
            ), 0) as total_due
        FROM deals d 
        WHERE d.customer_id = ? AND d.deleted_at IS NULL AND d.status != 'cancelled'
    ");
    $stmt->execute([$customerId, $customerId]);
    $result = $stmt->fetch();
    return max(0, $result['total_due'] ?? 0);
}

/**
 * CSRF token generation
 * @return string
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field
 * @return string
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * Display PHP errors section for debugging
 * Only shows when DEBUG_MODE is enabled
 */
function displayErrorSection() {
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
        return;
    }
    
    $errors = error_get_last();
    if ($errors) {
        echo '<div class="alert alert-danger mt-3" role="alert">';
        echo '<h6><i class="bi bi-exclamation-triangle me-2"></i>' . __('php_errors') . '</h6>';
        echo '<pre class="mb-0 small">';
        echo 'Type: ' . $errors['type'] . '<br>';
        echo 'Message: ' . htmlspecialchars($errors['message']) . '<br>';
        echo 'File: ' . htmlspecialchars($errors['file']) . '<br>';
        echo 'Line: ' . $errors['line'];
        echo '</pre>';
        echo '</div>';
    }
}

/**
 * Custom error handler for displaying errors on page
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
        return false;
    }
    
    $errorType = match($errno) {
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_DEPRECATED => 'User Deprecated',
        default => 'Unknown Error'
    };
    
    // Store error in session for display
    if (!isset($_SESSION['php_errors'])) {
        $_SESSION['php_errors'] = [];
    }
    
    $_SESSION['php_errors'][] = [
        'type' => $errorType,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ];
    
    return false; // Continue with normal error handling
}

/**
 * Display stored PHP errors
 */
function displayPHPErrors() {
    if (!defined('DEBUG_MODE') || !DEBUG_MODE) {
        return;
    }
    
    if (isset($_SESSION['php_errors']) && !empty($_SESSION['php_errors'])) {
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
        echo '<h6><i class="bi bi-bug me-2"></i>' . __('php_errors') . ' (' . __('debug_mode') . ')</h6>';
        echo '<ul class="mb-0 small">';
        foreach ($_SESSION['php_errors'] as $error) {
            echo '<li><strong>' . $error['type'] . ':</strong> ' . htmlspecialchars($error['message']);
            echo ' <em>(' . basename($error['file']) . ':' . $error['line'] . ')</em></li>';
        }
        echo '</ul>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        // Clear errors after display
        $_SESSION['php_errors'] = [];
    }
}

// Set custom error handler if in debug mode
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    set_error_handler('customErrorHandler');
}
