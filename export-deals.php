<?php
/**
 * Export Deals to CSV
 * Customer & Real-Time Trading Management System
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

require_once __DIR__ . '/models/DealModel.php';

$dealModel = new DealModel();

// Get filters
$filters = [];
if (!empty($_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
}
if (!empty($_GET['customer_id'])) {
    $filters['customer_id'] = (int)$_GET['customer_id'];
}
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}

$deals = $dealModel->getAll($filters);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="deals_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// Add CSV header
fputcsv($output, [
    'Deal Number',
    'Date',
    'Customer',
    'Buy Amount',
    'Sell Amount',
    'Profit',
    'Status',
    'Notes'
]);

// Add data rows
foreach ($deals as $deal) {
    fputcsv($output, [
        $deal['deal_number'],
        $deal['deal_date'],
        $deal['customer_name'],
        number_format($deal['total_buy_amount'], 2),
        number_format($deal['total_sell_amount'], 2),
        number_format($deal['profit'], 2),
        ucfirst($deal['status']),
        $deal['notes'] ?? ''
    ]);
}

fclose($output);
exit;
