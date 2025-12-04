<?php
/**
 * Edit Expense Page
 * Customer & Real-Time Trading Management System
 */

$pageTitle = 'Edit Expense';
require_once __DIR__ . '/includes/header.php';
requireLogin();

require_once __DIR__ . '/models/ExpenseModel.php';

$expenseModel = new ExpenseModel();

$id = (int)($_GET['id'] ?? 0);
$expense = $expenseModel->findById($id);

if (!$expense) {
    setFlashMessage('error', 'Expense not found.');
    redirect('expenses.php');
}

$categories = $expenseModel->getCategories();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    $categoryId = (int)($_POST['category_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
    $notes = sanitize($_POST['notes'] ?? '');

    if ($amount <= 0) {
        $errors[] = 'Amount must be greater than 0.';
    }

    if (empty($errors)) {
        $result = $expenseModel->update($id, [
            'category_id' => $categoryId ?: null,
            'amount' => $amount,
            'expense_date' => $expenseDate,
            'notes' => $notes ?: null
        ]);

        if ($result) {
            setFlashMessage('success', 'Expense updated successfully!');
            redirect('expenses.php');
        } else {
            $errors[] = 'Failed to update expense. Please try again.';
        }
    }
} else {
    $_POST = $expense;
}
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="expenses.php">Expenses</a></li>
            <li class="breadcrumb-item active">Edit Expense</li>
        </ol>
    </nav>
    <h4>Edit Expense</h4>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="expense_date" name="expense_date" 
                               value="<?php echo $_POST['expense_date']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo sanitize($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" 
                                   value="<?php echo $_POST['amount']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo sanitize($_POST['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Expense
                        </button>
                        <a href="expenses.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
