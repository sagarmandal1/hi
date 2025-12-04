<?php
/**
 * Delete Expense (Soft Delete)
 * Customer & Real-Time Trading Management System
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

require_once __DIR__ . '/models/ExpenseModel.php';

$expenseModel = new ExpenseModel();

$id = (int)($_GET['id'] ?? 0);
$expense = $expenseModel->findById($id);

if (!$expense) {
    setFlashMessage('error', 'Expense not found.');
    redirect('expenses.php');
}

if ($expenseModel->delete($id)) {
    setFlashMessage('success', 'Expense deleted successfully.');
} else {
    setFlashMessage('error', 'Failed to delete expense.');
}

redirect('expenses.php');
