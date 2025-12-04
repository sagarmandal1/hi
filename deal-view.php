<?php
/**
 * View Deal Page
 * Customer & Real-Time Trading Management System
 */

// Check login before including header (which outputs HTML)
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pageTitle = 'Deal Details';
require_once __DIR__ . '/includes/header.php';

require_once __DIR__ . '/models/DealModel.php';
require_once __DIR__ . '/models/PaymentModel.php';

$dealModel = new DealModel();
$paymentModel = new PaymentModel();

$id = (int)($_GET['id'] ?? 0);
$deal = $dealModel->findById($id);

if (!$deal) {
    setFlashMessage('error', 'Deal not found.');
    redirect('deals.php');
}

$items = $dealModel->getItems($id);
$payments = $paymentModel->getByDeal($id);
$totalPaid = $paymentModel->getTotalPaidForDeal($id);
$due = $deal['total_sell_amount'] - $totalPaid;
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="deals.php">Deals</a></li>
                <li class="breadcrumb-item active"><?php echo sanitize($deal['deal_number']); ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if ($due > 0): ?>
        <a href="payment-add.php?deal_id=<?php echo $id; ?>" class="btn btn-success">
            <i class="bi bi-credit-card me-2"></i>Add Payment
        </a>
        <?php endif; ?>
        <a href="deal-edit.php?id=<?php echo $id; ?>" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Edit Deal
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-2"></i>Print
        </button>
    </div>
</div>

<div class="row">
    <!-- Deal Info -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-info-circle me-2"></i>Deal Information</span>
                <span class="badge badge-status-<?php echo $deal['status']; ?> fs-6">
                    <?php echo ucfirst($deal['status']); ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <strong>Deal Number:</strong><br>
                        <span class="fs-5"><?php echo sanitize($deal['deal_number']); ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Date:</strong><br>
                        <span class="fs-5"><?php echo formatDate($deal['deal_date']); ?></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Customer:</strong><br>
                        <a href="customer-view.php?id=<?php echo $deal['customer_id']; ?>" class="fs-5">
                            <?php echo sanitize($deal['customer_name']); ?>
                        </a>
                        <br><small class="text-muted"><?php echo sanitize($deal['customer_phone']); ?></small>
                    </div>
                </div>
                
                <?php if ($deal['notes']): ?>
                <div class="mb-4">
                    <strong>Notes:</strong><br>
                    <?php echo sanitize($deal['notes']); ?>
                </div>
                <?php endif; ?>
                
                <!-- Deal Items -->
                <h6 class="mt-4 mb-3">Deal Items</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Buy Qty</th>
                                <th class="text-end">Buy Price</th>
                                <th class="text-end">Total Buy</th>
                                <th class="text-center">Sell Qty</th>
                                <th class="text-end">Sell Price</th>
                                <th class="text-end">Total Sell</th>
                                <th class="text-end">Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo sanitize($item['product_name']); ?></td>
                                <td class="text-center"><?php echo number_format($item['buy_quantity'], 2); ?></td>
                                <td class="text-end"><?php echo formatCurrency($item['buy_price']); ?></td>
                                <td class="text-end"><?php echo formatCurrency($item['total_buy']); ?></td>
                                <td class="text-center"><?php echo number_format($item['sell_quantity'], 2); ?></td>
                                <td class="text-end"><?php echo formatCurrency($item['sell_price']); ?></td>
                                <td class="text-end"><?php echo formatCurrency($item['total_sell']); ?></td>
                                <td class="text-end <?php echo $item['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo formatCurrency($item['profit']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Totals:</th>
                                <th class="text-end"><?php echo formatCurrency($deal['total_buy_amount']); ?></th>
                                <th colspan="2"></th>
                                <th class="text-end"><?php echo formatCurrency($deal['total_sell_amount']); ?></th>
                                <th class="text-end <?php echo $deal['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo formatCurrency($deal['profit']); ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Summary -->
    <div class="col-lg-4 mb-4">
        <!-- Summary Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-calculator me-2"></i>Summary
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Sell:</span>
                    <strong><?php echo formatCurrency($deal['total_sell_amount']); ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Paid:</span>
                    <strong class="text-success"><?php echo formatCurrency($totalPaid); ?></strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fs-5">Due Amount:</span>
                    <strong class="fs-5 <?php echo $due > 0 ? 'text-danger' : 'text-success'; ?>">
                        <?php echo formatCurrency($due); ?>
                    </strong>
                </div>
            </div>
        </div>
        
        <!-- Payment History -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-credit-card me-2"></i>Payment History</span>
                <?php if ($due > 0): ?>
                <a href="payment-add.php?deal_id=<?php echo $id; ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-plus"></i>
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($payments)): ?>
                    <p class="text-muted text-center">No payments recorded</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($payments as $payment): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?php echo formatCurrency($payment['amount']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    <?php echo formatDate($payment['payment_date']); ?> · 
                                    <?php echo ucfirst($payment['payment_method']); ?>
                                </small>
                            </div>
                            <a href="payment-delete.php?id=<?php echo $payment['id']; ?>&deal_id=<?php echo $id; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirmDelete('Delete this payment?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
