<?php
/**
 * Customer Payment Page - Pay against customer's total due
 * গ্রাহকের মোট বাকি পরিশোধ পেজ
 * Customer & Real-Time Trading Management System
 */

$pageTitle = __('pay_against_customer');
require_once __DIR__ . '/includes/header.php';
requireLogin();

require_once __DIR__ . '/models/PaymentModel.php';
require_once __DIR__ . '/models/DealModel.php';
require_once __DIR__ . '/models/CustomerModel.php';

$paymentModel = new PaymentModel();
$dealModel = new DealModel();
$customerModel = new CustomerModel();

// Get all active customers with dues
$allCustomers = $customerModel->getAll(['is_active' => 1]);

// Pre-select customer if passed in URL
$selectedCustomerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
$selectedCustomer = null;
$customerDeals = [];
$totalCustomerDue = 0;

if ($selectedCustomerId) {
    $selectedCustomer = $customerModel->findById($selectedCustomerId);
    if ($selectedCustomer) {
        $customerDeals = $dealModel->getByCustomer($selectedCustomerId);
        $stats = $customerModel->getStats($selectedCustomerId);
        $totalCustomerDue = max(0, $stats['total_due']);
    }
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = __('invalid_request');
    }

    $customerId = (int)($_POST['customer_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $notes = sanitize($_POST['notes'] ?? '');

    // Validation
    if (empty($customerId)) {
        $errors[] = __('select_customer');
    }
    if ($amount <= 0) {
        $errors[] = __('payment_amount') . ' must be greater than 0';
    }

    $customer = $customerModel->findById($customerId);
    if (!$customer) {
        $errors[] = __('customer_not_found');
    }

    if (empty($errors)) {
        // Get all deals with dues for this customer and distribute payment
        $deals = $dealModel->getByCustomer($customerId);
        $remainingAmount = $amount;
        $paymentsMade = 0;
        
        foreach ($deals as $deal) {
            if ($remainingAmount <= 0) break;
            
            $dealDue = $deal['total_sell_amount'] - $deal['total_paid'];
            if ($dealDue <= 0) continue;
            
            // Calculate payment for this deal
            $paymentForDeal = min($remainingAmount, $dealDue);
            
            // Create payment record
            $result = $paymentModel->create([
                'deal_id' => $deal['id'],
                'customer_id' => $customerId,
                'amount' => $paymentForDeal,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
                'notes' => $notes ? $notes . ' (গ্রাহকের সম্মিলিত বাকি থেকে)' : 'গ্রাহকের সম্মিলিত বাকি থেকে পরিশোধ'
            ]);
            
            if ($result) {
                $paymentsMade++;
                $remainingAmount -= $paymentForDeal;
            }
        }

        if ($paymentsMade > 0) {
            setFlashMessage('success', __('payment_recorded') . ' (' . formatCurrency($amount) . ')');
            redirect('customer-view.php?id=' . $customerId);
        } else {
            $errors[] = 'পেমেন্ট রেকর্ড করতে ব্যর্থ হয়েছে।';
        }
    }
}
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="payments.php"><?php _e('payments'); ?></a></li>
            <li class="breadcrumb-item active"><?php _e('pay_against_customer'); ?></li>
        </ol>
    </nav>
    <h4><?php _e('pay_against_customer'); ?></h4>
    <p class="text-muted"><?php _e('pay_any_amount'); ?></p>
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

                <form method="POST" action="" id="customerPaymentForm">
                    <?php echo csrfField(); ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customer_id" class="form-label"><?php _e('customer'); ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="customer_id" name="customer_id" required onchange="loadCustomerDues(this.value)">
                                <option value=""><?php _e('select_customer'); ?></option>
                                <?php foreach ($allCustomers as $cust): 
                                    $custStats = $customerModel->getStats($cust['id']);
                                    $custDue = max(0, $custStats['total_due']);
                                ?>
                                    <option value="<?php echo $cust['id']; ?>" 
                                            data-due="<?php echo $custDue; ?>"
                                            <?php echo ($selectedCustomerId == $cust['id']) ? 'selected' : ''; ?>>
                                        <?php echo sanitize($cust['name']); ?> - <?php echo sanitize($cust['phone']); ?>
                                        (<?php _e('due'); ?>: <?php echo formatCurrency($custDue); ?>)
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
                                       value="<?php echo $_POST['amount'] ?? $totalCustomerDue; ?>" required>
                            </div>
                            <small class="text-muted"><?php _e('pay_any_amount'); ?></small>
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
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i><?php _e('record_payment'); ?>
                        </button>
                        <a href="payments.php" class="btn btn-outline-secondary btn-lg"><?php _e('cancel'); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Customer Due Summary -->
        <div class="card mb-4" id="customerDueCard" style="<?php echo $selectedCustomer ? '' : 'display:none;'; ?>">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-exclamation-triangle me-2"></i><?php _e('customer_due'); ?>
            </div>
            <div class="card-body text-center">
                <h2 class="text-danger mb-3" id="totalDueAmount"><?php echo formatCurrency($totalCustomerDue); ?></h2>
                <p class="mb-0"><?php _e('all_transactions_due'); ?></p>
            </div>
        </div>
        
        <!-- Customer Deals with Dues -->
        <?php if ($selectedCustomer && !empty($customerDeals)): ?>
        <div class="card">
            <div class="card-header">
                <i class="bi bi-list-ul me-2"></i><?php _e('outstanding_dues'); ?>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($customerDeals as $deal): 
                        $dealDue = $deal['total_sell_amount'] - $deal['total_paid'];
                        if ($dealDue <= 0) continue;
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <a href="deal-view.php?id=<?php echo $deal['id']; ?>"><?php echo sanitize($deal['deal_number']); ?></a>
                            <br><small class="text-muted"><?php echo formatDate($deal['deal_date']); ?></small>
                        </div>
                        <span class="badge bg-danger"><?php echo formatCurrency($dealDue); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$extraScripts = <<<'SCRIPT'
<script>
function loadCustomerDues(customerId) {
    const card = document.getElementById('customerDueCard');
    const amountInput = document.getElementById('amount');
    
    if (customerId) {
        const option = document.querySelector(`#customer_id option[value="${customerId}"]`);
        if (option) {
            const due = parseFloat(option.dataset.due) || 0;
            document.getElementById('totalDueAmount').textContent = '৳' + due.toFixed(2);
            amountInput.value = due.toFixed(2);
            card.style.display = 'block';
            
            // Reload page to show customer deals
            if (!window.location.search.includes('customer_id=' + customerId)) {
                window.location.href = '?customer_id=' + customerId;
            }
        }
    } else {
        card.style.display = 'none';
    }
}
</script>
SCRIPT;
?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
