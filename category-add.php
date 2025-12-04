<?php
/**
 * Add Category
 * Customer & Real-Time Trading Management System
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

require_once __DIR__ . '/models/ProductModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
        redirect('products.php');
    }

    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');

    if (empty($name)) {
        setFlashMessage('error', 'Category name is required.');
        redirect('products.php');
    }

    $productModel = new ProductModel();
    $result = $productModel->createCategory([
        'name' => $name,
        'description' => $description ?: null
    ]);

    if ($result) {
        setFlashMessage('success', 'Category added successfully!');
    } else {
        setFlashMessage('error', 'Failed to add category.');
    }
}

redirect('products.php');
