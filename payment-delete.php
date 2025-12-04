<?php
/**
 * Delete Payment (Soft Delete)
 * Customer & Real-Time Trading Management System
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

require_once __DIR__ . '/models/PaymentModel.php';

$paymentModel = new PaymentModel();

$id = (int)($_GET['id'] ?? 0);
$dealId = (int)($_GET['deal_id'] ?? 0);
$payment = $paymentModel->findById($id);

if (!$payment) {
    setFlashMessage('error', 'Payment not found.');
    redirect('payments.php');
}

if ($paymentModel->delete($id)) {
    setFlashMessage('success', 'Payment deleted successfully.');
} else {
    setFlashMessage('error', 'Failed to delete payment.');
}

if ($dealId) {
    redirect('deal-view.php?id=' . $dealId);
} else {
    redirect('payments.php');
}
