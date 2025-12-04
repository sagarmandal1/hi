<?php
/**
 * Payment List Page
 * Customer & Real-Time Trading Management System
 */

$pageTitle = 'Payments';
require_once __DIR__ . '/includes/header.php';
requireLogin();

require_once __DIR__ . '/models/PaymentModel.php';
require_once __DIR__ . '/models/CustomerModel.php';

$paymentModel = new PaymentModel();
$customerModel = new CustomerModel();

// Handle filters
$filters = [];
if (!empty($_GET['customer_id'])) {
    $filters['customer_id'] = (int)$_GET['customer_id'];
}
if (!empty($_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
}

$payments = $paymentModel->getAll($filters);
$customers = $customerModel->getAll();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4>Payment Management</h4>
        <p>Track all payment transactions</p>
    </div>
    <a href="payment-add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Record Payment
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select">
                    <option value="">All Customers</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>" 
                                <?php echo (($_GET['customer_id'] ?? '') == $customer['id']) ? 'selected' : ''; ?>>
                            <?php echo sanitize($customer['name']); ?>
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
                <a href="payments.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Payment List -->
<div class="card">
    <div class="card-body">
        <?php if (empty($payments)): ?>
            <div class="text-center py-5">
                <i class="bi bi-credit-card display-1 text-muted"></i>
                <p class="mt-3 text-muted">No payments found</p>
                <a href="payment-add.php" class="btn btn-primary">Record First Payment</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Deal #</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $index => $payment): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo formatDate($payment['payment_date']); ?></td>
                            <td>
                                <a href="customer-view.php?id=<?php echo $payment['customer_id']; ?>">
                                    <?php echo sanitize($payment['customer_name']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="deal-view.php?id=<?php echo $payment['deal_id']; ?>">
                                    <?php echo sanitize($payment['deal_number']); ?>
                                </a>
                            </td>
                            <td class="text-success"><strong><?php echo formatCurrency($payment['amount']); ?></strong></td>
                            <td><span class="badge bg-secondary"><?php echo ucfirst($payment['payment_method']); ?></span></td>
                            <td><?php echo sanitize($payment['notes'] ?? '-'); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="payment-edit.php?id=<?php echo $payment['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="payment-delete.php?id=<?php echo $payment['id']; ?>" class="btn btn-outline-danger" title="Delete"
                                       onclick="return confirmDelete('Are you sure you want to delete this payment?')">
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
                <small class="text-muted">Showing <?php echo count($payments); ?> payment(s)</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
