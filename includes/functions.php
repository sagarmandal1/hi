<?php
/**
 * Common Functions
 * Customer & Real-Time Trading Management System
 */

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
 * Format currency
 * @param float $amount
 * @return string
 */
function formatCurrency($amount) {
    return '$' . number_format((float)$amount, 2);
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
