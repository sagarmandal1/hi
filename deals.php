<?php
/**
 * Deal List Page
 * Customer & Real-Time Trading Management System
 */

// Check login before including header (which outputs HTML)
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pageTitle = 'Deals';
require_once __DIR__ . '/includes/header.php';

require_once __DIR__ . '/models/DealModel.php';
require_once __DIR__ . '/models/CustomerModel.php';
require_once __DIR__ . '/models/PaymentModel.php';

$dealModel = new DealModel();
$customerModel = new CustomerModel();
$paymentModel = new PaymentModel();

// Handle filters
$filters = [];
if (!empty($_GET['customer_id'])) {
    $filters['customer_id'] = (int)$_GET['customer_id'];
}
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['start_date'])) {
    $filters['start_date'] = $_GET['start_date'];
}
if (!empty($_GET['end_date'])) {
    $filters['end_date'] = $_GET['end_date'];
}

$deals = $dealModel->getAll($filters);
$customers = $customerModel->getAll();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4>Deal Management</h4>
        <p>Track all buy and sell transactions</p>
    </div>
    <a href="deal-add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>New Deal
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
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo (($_GET['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo (($_GET['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo (($_GET['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo sanitize($_GET['start_date'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo sanitize($_GET['end_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="deals.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Deal List -->
<div class="card">
    <div class="card-body">
        <?php if (empty($deals)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-check display-1 text-muted"></i>
                <p class="mt-3 text-muted">No deals found</p>
                <a href="deal-add.php" class="btn btn-primary">Create Your First Deal</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Deal #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Buy Amount</th>
                            <th>Sell Amount</th>
                            <th>Profit</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deals as $deal): 
                            $totalPaid = $paymentModel->getTotalPaidForDeal($deal['id']);
                            $due = $deal['total_sell_amount'] - $totalPaid;
                        ?>
                        <tr>
                            <td><a href="deal-view.php?id=<?php echo $deal['id']; ?>"><?php echo sanitize($deal['deal_number']); ?></a></td>
                            <td><?php echo formatDate($deal['deal_date']); ?></td>
                            <td>
                                <a href="customer-view.php?id=<?php echo $deal['customer_id']; ?>">
                                    <?php echo sanitize($deal['customer_name']); ?>
                                </a>
                            </td>
                            <td><?php echo formatCurrency($deal['total_buy_amount']); ?></td>
                            <td><?php echo formatCurrency($deal['total_sell_amount']); ?></td>
                            <td class="<?php echo $deal['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <strong><?php echo formatCurrency($deal['profit']); ?></strong>
                            </td>
                            <td class="<?php echo $due > 0 ? 'text-danger' : 'text-success'; ?>">
                                <?php echo formatCurrency($due); ?>
                            </td>
                            <td>
                                <span class="badge badge-status-<?php echo $deal['status']; ?>">
                                    <?php echo ucfirst($deal['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="deal-view.php?id=<?php echo $deal['id']; ?>" class="btn btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="deal-edit.php?id=<?php echo $deal['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($due > 0): ?>
                                    <a href="payment-add.php?deal_id=<?php echo $deal['id']; ?>" class="btn btn-outline-success" title="Add Payment">
                                        <i class="bi bi-credit-card"></i>
                                    </a>
                                    <?php endif; ?>
                                    <a href="deal-delete.php?id=<?php echo $deal['id']; ?>" class="btn btn-outline-danger" title="Delete"
                                       onclick="return confirmDelete('Are you sure you want to delete this deal?')">
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
                <small class="text-muted">Showing <?php echo count($deals); ?> deal(s)</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
