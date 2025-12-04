<?php
/**
 * Delete Customer (Soft Delete)
 * Customer & Real-Time Trading Management System
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

require_once __DIR__ . '/models/CustomerModel.php';

$customerModel = new CustomerModel();

$id = (int)($_GET['id'] ?? 0);
$customer = $customerModel->findById($id);

if (!$customer) {
    setFlashMessage('error', 'Customer not found.');
    redirect('customers.php');
}

if ($customerModel->delete($id)) {
    setFlashMessage('success', 'Customer deleted successfully.');
} else {
    setFlashMessage('error', 'Failed to delete customer.');
}

redirect('customers.php');
