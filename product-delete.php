<?php
/**
 * Delete Product (Soft Delete)
 * Customer & Real-Time Trading Management System
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

require_once __DIR__ . '/models/ProductModel.php';

$productModel = new ProductModel();

$id = (int)($_GET['id'] ?? 0);
$product = $productModel->findById($id);

if (!$product) {
    setFlashMessage('error', 'Product not found.');
    redirect('products.php');
}

if ($productModel->delete($id)) {
    setFlashMessage('success', 'Product deleted successfully.');
} else {
    setFlashMessage('error', 'Failed to delete product.');
}

redirect('products.php');
