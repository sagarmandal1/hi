<?php
/**
 * View Customer Profile Page
 * Customer & Real-Time Trading Management System
 */

// Check login before including header (which outputs HTML)
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pageTitle = 'Customer Profile';
require_once __DIR__ . '/includes/header.php';

require_once __DIR__ . '/models/CustomerModel.php';
require_once __DIR__ . '/models/DealModel.php';
require_once __DIR__ . '/models/PaymentModel.php';

$customerModel = new CustomerModel();
$dealModel = new DealModel();
$paymentModel = new PaymentModel();

$id = (int)($_GET['id'] ?? 0);
$customer = $customerModel->findById($id);

if (!$customer) {
    setFlashMessage('error', 'Customer not found.');
    redirect('customers.php');
}

$stats = $customerModel->getStats($id);
$deals = $dealModel->getByCustomer($id);
$payments = $paymentModel->getByCustomer($id);
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="customers.php">Customers</a></li>
            <li class="breadcrumb-item active"><?php echo sanitize($customer['name']); ?></li>
        </ol>
    </nav>
</div>

<div class="row">
    <!-- Customer Info Card -->
    <div class="col-lg-4 mb-4">
        <div class="card customer-profile-card h-100">
            <div class="card-body">
                <div class="customer-avatar">
                    <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                </div>
                <h4><?php echo sanitize($customer['name']); ?></h4>
                <p class="text-muted mb-3">
                    <?php if ($customer['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                </p>
                
                <div class="text-start mt-4">
                    <p><i class="bi bi-telephone me-2"></i><?php echo sanitize($customer['phone']); ?></p>
                    <?php if ($customer['email']): ?>
                        <p><i class="bi bi-envelope me-2"></i><?php echo sanitize($customer['email']); ?></p>
                    <?php endif; ?>
                    <?php if ($customer['address']): ?>
                        <p><i class="bi bi-geo-alt me-2"></i><?php echo sanitize($customer['address']); ?></p>
                    <?php endif; ?>
                    <?php if ($customer['notes']): ?>
                        <p><i class="bi bi-sticky me-2"></i><?php echo sanitize($customer['notes']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="mt-4">
                    <a href="customer-edit.php?id=<?php echo $id; ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-2"></i><?php _e('edit'); ?>
                    </a>
                    <a href="deal-add.php?customer_id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-plus-circle me-2"></i><?php _e('new_deal'); ?>
                    </a>
                    <?php if ($stats['total_due'] > 0): ?>
                    <a href="customer-payment.php?customer_id=<?php echo $id; ?>" class="btn btn-success btn-sm">
                        <i class="bi bi-credit-card me-2"></i><?php _e('pay_against_customer'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="col-lg-8 mb-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="stats-value"><?php echo $stats['total_deals']; ?></h3>
                        <p class="stats-label mb-0">Total Deals</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stats-card success">
                    <div class="card-body text-center">
                        <h3 class="stats-value"><?php echo formatCurrency($stats['total_sell']); ?></h3>
                        <p class="stats-label mb-0">Total Sell</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stats-card success">
                    <div class="card-body text-center">
                        <h3 class="stats-value"><?php echo formatCurrency($stats['total_profit']); ?></h3>
                        <p class="stats-label mb-0">Total Profit</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="stats-value"><?php echo formatCurrency($stats['total_paid']); ?></h3>
                        <p class="stats-label mb-0">Total Paid</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card stats-card <?php echo $stats['total_due'] > 0 ? 'warning' : 'success'; ?>">
                    <div class="card-body text-center">
                        <h3 class="stats-value"><?php echo formatCurrency(max(0, $stats['total_due'])); ?></h3>
                        <p class="stats-label mb-0">Total Due</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs for Deals and Payments -->
<ul class="nav nav-tabs mb-4" id="customerTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="deals-tab" data-bs-toggle="tab" data-bs-target="#deals" type="button" role="tab">
            <i class="bi bi-cart-check me-2"></i>Deals (<?php echo count($deals); ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">
            <i class="bi bi-credit-card me-2"></i>Payments (<?php echo count($payments); ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="customerTabsContent">
    <!-- Deals Tab -->
    <div class="tab-pane fade show active" id="deals" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <?php if (empty($deals)): ?>
                    <p class="text-muted text-center py-4">No deals found for this customer</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Deal #</th>
                                    <th>Date</th>
                                    <th>Sell Amount</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($deals as $deal): 
                                    $due = $deal['total_sell_amount'] - $deal['total_paid'];
                                ?>
                                <tr>
                                    <td><a href="deal-view.php?id=<?php echo $deal['id']; ?>"><?php echo sanitize($deal['deal_number']); ?></a></td>
                                    <td><?php echo formatDate($deal['deal_date']); ?></td>
                                    <td><?php echo formatCurrency($deal['total_sell_amount']); ?></td>
                                    <td><?php echo formatCurrency($deal['total_paid']); ?></td>
                                    <td class="<?php echo $due > 0 ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo formatCurrency($due); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-status-<?php echo $deal['status']; ?>">
                                            <?php echo ucfirst($deal['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="payment-add.php?deal_id=<?php echo $deal['id']; ?>" class="btn btn-sm btn-outline-success" title="Add Payment">
                                            <i class="bi bi-credit-card"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Payments Tab -->
    <div class="tab-pane fade" id="payments" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <?php if (empty($payments)): ?>
                    <p class="text-muted text-center py-4">No payments found for this customer</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Deal #</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?php echo formatDate($payment['payment_date']); ?></td>
                                    <td><a href="deal-view.php?id=<?php echo $payment['deal_id']; ?>"><?php echo sanitize($payment['deal_number']); ?></a></td>
                                    <td class="text-success"><?php echo formatCurrency($payment['amount']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($payment['payment_method']); ?></span></td>
                                    <td><?php echo sanitize($payment['notes'] ?? '-'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
