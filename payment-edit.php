<?php
/**
 * Edit Payment Page
 * Customer & Real-Time Trading Management System
 */

$pageTitle = 'Edit Payment';
require_once __DIR__ . '/includes/header.php';
requireLogin();

require_once __DIR__ . '/models/PaymentModel.php';
require_once __DIR__ . '/models/DealModel.php';

$paymentModel = new PaymentModel();
$dealModel = new DealModel();

$id = (int)($_GET['id'] ?? 0);
$payment = $paymentModel->findById($id);

if (!$payment) {
    setFlashMessage('error', 'Payment not found.');
    redirect('payments.php');
}

$deal = $dealModel->findById($payment['deal_id']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $notes = sanitize($_POST['notes'] ?? '');

    if ($amount <= 0) {
        $errors[] = 'Amount must be greater than 0.';
    }

    if (empty($errors)) {
        $result = $paymentModel->update($id, [
            'deal_id' => $payment['deal_id'],
            'customer_id' => $payment['customer_id'],
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_date' => $paymentDate,
            'notes' => $notes ?: null
        ]);

        if ($result) {
            setFlashMessage('success', 'Payment updated successfully!');
            redirect('deal-view.php?id=' . $payment['deal_id']);
        } else {
            $errors[] = 'Failed to update payment. Please try again.';
        }
    }
} else {
    $_POST = $payment;
}
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="payments.php">Payments</a></li>
            <li class="breadcrumb-item active">Edit Payment</li>
        </ol>
    </nav>
    <h4>Edit Payment</h4>
</div>

<div class="row">
    <div class="col-lg-8">
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
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Deal</label>
                            <input type="text" class="form-control" value="<?php echo sanitize($payment['deal_number']); ?>" readonly>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                   value="<?php echo $_POST['payment_date']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" 
                                       value="<?php echo $_POST['amount']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="cash" <?php echo ($_POST['payment_method'] === 'cash') ? 'selected' : ''; ?>>Cash</option>
                                <option value="online" <?php echo ($_POST['payment_method'] === 'online') ? 'selected' : ''; ?>>Online</option>
                                <option value="bank" <?php echo ($_POST['payment_method'] === 'bank') ? 'selected' : ''; ?>>Bank Transfer</option>
                                <option value="other" <?php echo ($_POST['payment_method'] === 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo sanitize($_POST['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Payment
                        </button>
                        <a href="deal-view.php?id=<?php echo $payment['deal_id']; ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Deal Information
            </div>
            <div class="card-body">
                <p><strong>Customer:</strong> <?php echo sanitize($payment['customer_name']); ?></p>
                <p><strong>Deal #:</strong> <?php echo sanitize($payment['deal_number']); ?></p>
                <?php if ($deal): ?>
                <p><strong>Total Sell:</strong> <?php echo formatCurrency($deal['total_sell_amount']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
