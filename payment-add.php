<?php
/**
 * Add Payment Page
 * পেমেন্ট যোগ করুন
 * Customer & Real-Time Trading Management System
 */

// Check login before including header (which outputs HTML)
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pageTitle = __('record_payment');
require_once __DIR__ . '/includes/header.php';

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
        $errors[] = __('invalid_request');
    }

    $dealId = (int)($_POST['deal_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $notes = sanitize($_POST['notes'] ?? '');

    // Validation
    if (empty($dealId)) {
        $errors[] = __('select_deal_or_customer');
    }
    if ($amount <= 0) {
        $errors[] = __('payment_amount') . ' must be greater than 0';
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
            setFlashMessage('success', __('payment_recorded'));
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
            <li class="breadcrumb-item"><a href="payments.php"><?php _e('payments'); ?></a></li>
            <li class="breadcrumb-item active"><?php _e('record_payment'); ?></li>
        </ol>
    </nav>
    <h4><?php _e('record_payment'); ?></h4>
</div>

<!-- Payment Type Selection -->
<div class="alert alert-info mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-info-circle me-2"></i>
            <strong><?php _e('pay_against_customer'); ?>?</strong> 
            <?php _e('customer_payment_info'); ?>
        </div>
        <a href="customer-payment.php" class="btn btn-warning">
            <i class="bi bi-person-check me-2"></i><?php _e('pay_against_customer'); ?>
        </a>
    </div>
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
                            <label for="deal_id" class="form-label"><?php _e('deal'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="deal_id" name="deal_id" required onchange="updateDealInfo(this)">
                                <option value=""><?php _e('select_deal_or_customer'); ?></option>
                                <?php foreach ($allDeals as $deal): 
                                    $dealDue = $deal['total_sell_amount'] - $paymentModel->getTotalPaidForDeal($deal['id']);
                                ?>
                                    <option value="<?php echo $deal['id']; ?>" 
                                            data-customer="<?php echo sanitize($deal['customer_name']); ?>"
                                            data-sell="<?php echo $deal['total_sell_amount']; ?>"
                                            data-due="<?php echo $dealDue; ?>"
                                            <?php echo ($selectedDealId == $deal['id']) ? 'selected' : ''; ?>>
                                        <?php echo sanitize($deal['deal_number']); ?> - <?php echo sanitize($deal['customer_name']); ?>
                                        (<?php _e('due'); ?>: <?php echo formatCurrency($dealDue); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="payment_date" class="form-label"><?php _e('payment_date'); ?> <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                   value="<?php echo $_POST['payment_date'] ?? date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label"><?php _e('payment_amount'); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo CURRENCY_SYMBOL; ?></span>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" 
                                       value="<?php echo $_POST['amount'] ?? ($selectedDeal ? ($selectedDeal['total_sell_amount'] - $paymentModel->getTotalPaidForDeal($selectedDealId)) : ''); ?>" required>
                            </div>
                            <small class="text-muted"><?php _e('pay_any_amount'); ?> - <?php _e('full_payment'); ?> বা <?php _e('partial_payment'); ?></small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label"><?php _e('payment_method'); ?></label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="cash" <?php echo (($_POST['payment_method'] ?? '') === 'cash') ? 'selected' : ''; ?>><?php _e('cash'); ?></option>
                                <option value="bkash" <?php echo (($_POST['payment_method'] ?? '') === 'bkash') ? 'selected' : ''; ?>><?php _e('bkash'); ?></option>
                                <option value="nagad" <?php echo (($_POST['payment_method'] ?? '') === 'nagad') ? 'selected' : ''; ?>>নগদ (Nagad)</option>
                                <option value="rocket" <?php echo (($_POST['payment_method'] ?? '') === 'rocket') ? 'selected' : ''; ?>><?php _e('rocket'); ?></option>
                                <option value="bank" <?php echo (($_POST['payment_method'] ?? '') === 'bank') ? 'selected' : ''; ?>><?php _e('bank'); ?></option>
                                <option value="other" <?php echo (($_POST['payment_method'] ?? '') === 'other') ? 'selected' : ''; ?>><?php _e('other'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label"><?php _e('notes'); ?></label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo sanitize($_POST['notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i><?php _e('record_payment'); ?>
                        </button>
                        <a href="payments.php" class="btn btn-outline-secondary"><?php _e('cancel'); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card" id="dealInfoCard" style="<?php echo $selectedDeal ? '' : 'display:none;'; ?>">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i><?php _e('deal_info'); ?>
            </div>
            <div class="card-body">
                <p><strong><?php _e('customer'); ?>:</strong> <span id="infoCustomer"><?php echo $selectedDeal ? sanitize($selectedDeal['customer_name']) : ''; ?></span></p>
                <p><strong><?php _e('total_sell'); ?>:</strong> <span id="infoSell"><?php echo $selectedDeal ? formatCurrency($selectedDeal['total_sell_amount']) : ''; ?></span></p>
                <p><strong><?php _e('due_amount'); ?>:</strong> <span id="infoDue" class="text-danger"><?php echo $selectedDeal ? formatCurrency($selectedDeal['total_sell_amount'] - $paymentModel->getTotalPaidForDeal($selectedDealId)) : ''; ?></span></p>
            </div>
        </div>
    </div>
</div>

<?php
$symbol = CURRENCY_SYMBOL;
?>
<script>
function updateDealInfo(select) {
    const option = select.options[select.selectedIndex];
    const card = document.getElementById('dealInfoCard');
    const symbol = '<?php echo $symbol; ?>';
    
    if (option.value) {
        document.getElementById('infoCustomer').textContent = option.dataset.customer;
        document.getElementById('infoSell').textContent = symbol + parseFloat(option.dataset.sell).toFixed(2);
        document.getElementById('infoDue').textContent = symbol + parseFloat(option.dataset.due).toFixed(2);
        document.getElementById('amount').value = parseFloat(option.dataset.due).toFixed(2);
        card.style.display = 'block';
    } else {
        card.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
