<?php
/**
 * Expense List Page
 * Customer & Real-Time Trading Management System
 */

$pageTitle = 'Expenses';
require_once __DIR__ . '/includes/header.php';
requireLogin();

require_once __DIR__ . '/models/ExpenseModel.php';

$expenseModel = new ExpenseModel();

// Handle filters
$filters = [];
if (!empty($_GET['category_id'])) {
    $filters['category_id'] = (int)$_GET['category_id'];
}
if (!empty($_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
}

$expenses = $expenseModel->getAll($filters);
$categories = $expenseModel->getCategories();

// Calculate total
$total = array_sum(array_column($expenses, 'amount'));
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4>Expense Management</h4>
        <p>Track all business expenses</p>
    </div>
    <a href="expense-add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Expense
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo (($_GET['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo sanitize($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo sanitize($_GET['start_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo sanitize($_GET['end_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="expenses.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Total -->
<div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
    <span><i class="bi bi-calculator me-2"></i>Total Expenses (filtered):</span>
    <strong class="fs-5"><?php echo formatCurrency($total); ?></strong>
</div>

<!-- Expense List -->
<div class="card">
    <div class="card-body">
        <?php if (empty($expenses)): ?>
            <div class="text-center py-5">
                <i class="bi bi-wallet2 display-1 text-muted"></i>
                <p class="mt-3 text-muted">No expenses found</p>
                <a href="expense-add.php" class="btn btn-primary">Add First Expense</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $index => $expense): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo formatDate($expense['expense_date']); ?></td>
                            <td>
                                <?php if ($expense['category_name']): ?>
                                    <span class="badge bg-secondary"><?php echo sanitize($expense['category_name']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-danger"><strong><?php echo formatCurrency($expense['amount']); ?></strong></td>
                            <td><?php echo sanitize($expense['notes'] ?? '-'); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="expense-edit.php?id=<?php echo $expense['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="expense-delete.php?id=<?php echo $expense['id']; ?>" class="btn btn-outline-danger" title="Delete"
                                       onclick="return confirmDelete('Are you sure you want to delete this expense?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <small class="text-muted">Showing <?php echo count($expenses); ?> expense(s)</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
