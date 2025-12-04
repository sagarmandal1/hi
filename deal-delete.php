<?php
/**
 * Delete Deal (Soft Delete)
 * Customer & Real-Time Trading Management System
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

require_once __DIR__ . '/models/DealModel.php';

$dealModel = new DealModel();

$id = (int)($_GET['id'] ?? 0);
$deal = $dealModel->findById($id);

if (!$deal) {
    setFlashMessage('error', 'Deal not found.');
    redirect('deals.php');
}

if ($dealModel->delete($id)) {
    setFlashMessage('success', 'Deal deleted successfully.');
} else {
    setFlashMessage('error', 'Failed to delete deal.');
}

redirect('deals.php');
