<?php
/**
 * Add Payment Page
 * Customer & Real-Time Trading Management System
 */

$pageTitle = 'Record Payment';
require_once __DIR__ . '/includes/header.php';
requireLogin();

require_once __DIR__ . '/models/PaymentModel.php';
require_once __DIR__ . '/models/DealModel.php';
require_once __DIR__ . '/models/CustomerModel.php';

$paymentModel = new PaymentModel();
$dealModel = new DealModel();
$customerModel = new CustomerModel();

$customers = $customerModel->getAll(['is_active' => 1]);

// Pre-select deal if passed in URL
$selectedDealId = isset($_GET['deal_id']) ? (int)$_GET['deal_id'] : null;
$selectedDeal = null;

if ($selectedDealId) {
    $selectedDeal = $dealModel->findById($selectedDealId);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    $dealId = (int)($_POST['deal_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $notes = sanitize($_POST['notes'] ?? '');

    // Validation
    if (empty($dealId)) {
        $errors[] = 'Please select a deal.';
    }
    if ($amount <= 0) {
        $errors[] = 'Amount must be greater than 0.';
    }

    // Get deal info for customer_id
    $deal = $dealModel->findById($dealId);
    if (!$deal) {
        $errors[] = 'Invalid deal selected.';
    }

    if (empty($errors)) {
        $result = $paymentModel->create([
            'deal_id' => $dealId,
            'customer_id' => $deal['customer_id'],
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_date' => $paymentDate,
            'notes' => $notes ?: null
        ]);

        if ($result) {
            setFlashMessage('success', 'Payment recorded successfully!');
            redirect('deal-view.php?id=' . $dealId);
        } else {
            $errors[] = 'Failed to record payment. Please try again.';
        }
    }
}

// Get all deals for dropdown
$allDeals = $dealModel->getAll();
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="payments.php">Payments</a></li>
            <li class="breadcrumb-item active">Record Payment</li>
        </ol>
    </nav>
    <h4>Record Payment</h4>
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
                            <label for="deal_id" class="form-label">Deal <span class="text-danger">*</span></label>
                            <select class="form-select" id="deal_id" name="deal_id" required onchange="updateDealInfo(this)">
                                <option value="">Select Deal</option>
                                <?php foreach ($allDeals as $deal): 
                                    $dealDue = $deal['total_sell_amount'] - $paymentModel->getTotalPaidForDeal($deal['id']);
                                ?>
                                    <option value="<?php echo $deal['id']; ?>" 
                                            data-customer="<?php echo sanitize($deal['customer_name']); ?>"
                                            data-sell="<?php echo $deal['total_sell_amount']; ?>"
                                            data-due="<?php echo $dealDue; ?>"
                                            <?php echo ($selectedDealId == $deal['id']) ? 'selected' : ''; ?>>
                                        <?php echo sanitize($deal['deal_number']); ?> - <?php echo sanitize($deal['customer_name']); ?>
                                        (Due: <?php echo formatCurrency($dealDue); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                   value="<?php echo $_POST['payment_date'] ?? date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" 
                                       value="<?php echo $_POST['amount'] ?? ($selectedDeal ? ($selectedDeal['total_sell_amount'] - $paymentModel->getTotalPaidForDeal($selectedDealId)) : ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="cash" <?php echo (($_POST['payment_method'] ?? '') === 'cash') ? 'selected' : ''; ?>>Cash</option>
                                <option value="online" <?php echo (($_POST['payment_method'] ?? '') === 'online') ? 'selected' : ''; ?>>Online</option>
                                <option value="bank" <?php echo (($_POST['payment_method'] ?? '') === 'bank') ? 'selected' : ''; ?>>Bank Transfer</option>
                                <option value="other" <?php echo (($_POST['payment_method'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo sanitize($_POST['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Record Payment
                        </button>
                        <a href="payments.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card" id="dealInfoCard" style="<?php echo $selectedDeal ? '' : 'display:none;'; ?>">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Deal Information
            </div>
            <div class="card-body">
                <p><strong>Customer:</strong> <span id="infoCustomer"><?php echo $selectedDeal ? sanitize($selectedDeal['customer_name']) : ''; ?></span></p>
                <p><strong>Total Sell:</strong> <span id="infoSell"><?php echo $selectedDeal ? formatCurrency($selectedDeal['total_sell_amount']) : ''; ?></span></p>
                <p><strong>Due Amount:</strong> <span id="infoDue" class="text-danger"><?php echo $selectedDeal ? formatCurrency($selectedDeal['total_sell_amount'] - $paymentModel->getTotalPaidForDeal($selectedDealId)) : ''; ?></span></p>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'SCRIPT'
<script>
function updateDealInfo(select) {
    const option = select.options[select.selectedIndex];
    const card = document.getElementById('dealInfoCard');
    
    if (option.value) {
        document.getElementById('infoCustomer').textContent = option.dataset.customer;
        document.getElementById('infoSell').textContent = '$' + parseFloat(option.dataset.sell).toFixed(2);
        document.getElementById('infoDue').textContent = '$' + parseFloat(option.dataset.due).toFixed(2);
        document.getElementById('amount').value = parseFloat(option.dataset.due).toFixed(2);
        card.style.display = 'block';
    } else {
        card.style.display = 'none';
    }
}
</script>
SCRIPT;
?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
