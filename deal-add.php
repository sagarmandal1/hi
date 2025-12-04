<?php
/**
 * Add Deal Page
 * Customer & Real-Time Trading Management System
 */

$pageTitle = 'New Deal';
require_once __DIR__ . '/includes/header.php';
requireLogin();

require_once __DIR__ . '/models/DealModel.php';
require_once __DIR__ . '/models/CustomerModel.php';
require_once __DIR__ . '/models/ProductModel.php';

$dealModel = new DealModel();
$customerModel = new CustomerModel();
$productModel = new ProductModel();

$customers = $customerModel->getAll(['is_active' => 1]);
$products = $productModel->getAll();
$dealNumber = $dealModel->generateDealNumber();

$errors = [];

// Pre-select customer if passed in URL
$selectedCustomer = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    $customerId = (int)($_POST['customer_id'] ?? 0);
    $dealDate = $_POST['deal_date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'pending';
    $notes = sanitize($_POST['notes'] ?? '');
    $items = $_POST['items'] ?? [];

    // Validation
    if (empty($customerId)) {
        $errors[] = 'Please select a customer.';
    }
    if (empty($items)) {
        $errors[] = 'Please add at least one item.';
    }

    // Calculate totals
    $totalBuy = 0;
    $totalSell = 0;
    foreach ($items as $item) {
        $totalBuy += (float)($item['total_buy'] ?? 0);
        $totalSell += (float)($item['total_sell'] ?? 0);
    }
    $profit = $totalSell - $totalBuy;

    if (empty($errors)) {
        // Create deal
        $dealId = $dealModel->create([
            'deal_number' => $dealNumber,
            'customer_id' => $customerId,
            'deal_date' => $dealDate,
            'status' => $status,
            'total_buy_amount' => $totalBuy,
            'total_sell_amount' => $totalSell,
            'profit' => $profit,
            'notes' => $notes ?: null
        ]);

        if ($dealId) {
            // Add deal items
            foreach ($items as $item) {
                $buyQty = (float)($item['buy_qty'] ?? 1);
                $buyPrice = (float)($item['buy_price'] ?? 0);
                $sellQty = (float)($item['sell_qty'] ?? 1);
                $sellPrice = (float)($item['sell_price'] ?? 0);
                $itemTotalBuy = $buyQty * $buyPrice;
                $itemTotalSell = $sellQty * $sellPrice;
                $itemProfit = $itemTotalSell - $itemTotalBuy;

                $dealModel->addItem([
                    'deal_id' => $dealId,
                    'product_id' => $item['product_id'] ?: null,
                    'product_name' => sanitize($item['product_name']),
                    'buy_quantity' => $buyQty,
                    'buy_price' => $buyPrice,
                    'total_buy' => $itemTotalBuy,
                    'sell_quantity' => $sellQty,
                    'sell_price' => $sellPrice,
                    'total_sell' => $itemTotalSell,
                    'profit' => $itemProfit
                ]);
            }

            setFlashMessage('success', 'Deal created successfully!');
            redirect('deal-view.php?id=' . $dealId);
        } else {
            $errors[] = 'Failed to create deal. Please try again.';
        }
    }
}
?>

<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="deals.php">Deals</a></li>
            <li class="breadcrumb-item active">New Deal</li>
        </ol>
    </nav>
    <h4>Create New Deal</h4>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="" id="dealForm">
    <?php echo csrfField(); ?>
    
    <!-- Deal Info -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-info-circle me-2"></i>Deal Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Deal Number</label>
                    <input type="text" class="form-control" value="<?php echo sanitize($dealNumber); ?>" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="deal_date" class="form-label">Deal Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="deal_date" name="deal_date" 
                           value="<?php echo $_POST['deal_date'] ?? date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                    <select class="form-select" id="customer_id" name="customer_id" required>
                        <option value="">Select Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>" 
                                    <?php echo (($selectedCustomer ?? $_POST['customer_id'] ?? '') == $customer['id']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($customer['name']); ?> (<?php echo sanitize($customer['phone']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="pending" <?php echo (($_POST['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="completed" <?php echo (($_POST['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo (($_POST['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo sanitize($_POST['notes'] ?? ''); ?></textarea>
            </div>
        </div>
    </div>
    
    <!-- Deal Items -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-cart me-2"></i>Deal Items</span>
            <button type="button" class="btn btn-sm btn-primary" onclick="addDealItemRow()">
                <i class="bi bi-plus me-1"></i>Add Item
            </button>
        </div>
        <div class="card-body">
            <div id="dealItemsContainer">
                <!-- First item row -->
                <div class="deal-item-row">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Product</label>
                            <select class="form-select product-select" name="items[0][product_id]" onchange="updateProductName(this)">
                                <option value="">Select or type new</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>" data-name="<?php echo sanitize($product['name']); ?>">
                                        <?php echo sanitize($product['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control product-name" name="items[0][product_name]" required>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Buy Qty</label>
                            <input type="number" step="0.01" min="0" class="form-control buy-qty" name="items[0][buy_qty]" 
                                   value="1" onchange="calculateItemTotal(this.closest('.deal-item-row'))">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Buy Price</label>
                            <input type="number" step="0.01" min="0" class="form-control buy-price" name="items[0][buy_price]" 
                                   value="0" onchange="calculateItemTotal(this.closest('.deal-item-row'))">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Total Buy</label>
                            <input type="number" step="0.01" class="form-control total-buy" name="items[0][total_buy]" value="0" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-2"></div>
                        <div class="col-md-3 mb-2"></div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Sell Qty</label>
                            <input type="number" step="0.01" min="0" class="form-control sell-qty" name="items[0][sell_qty]" 
                                   value="1" onchange="calculateItemTotal(this.closest('.deal-item-row'))">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Sell Price</label>
                            <input type="number" step="0.01" min="0" class="form-control sell-price" name="items[0][sell_price]" 
                                   value="0" onchange="calculateItemTotal(this.closest('.deal-item-row'))">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Total Sell</label>
                            <input type="number" step="0.01" class="form-control total-sell" name="items[0][total_sell]" value="0" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10 mb-2">
                            <label class="form-label">Item Profit</label>
                            <input type="number" step="0.01" class="form-control item-profit" name="items[0][profit]" value="0" readonly>
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="removeDealItemRow(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Totals -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-calculator me-2"></i>Deal Summary
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <h5>Total Buy Amount</h5>
                    <h3 class="text-info" id="grandTotalBuy">$0.00</h3>
                </div>
                <div class="col-md-4">
                    <h5>Total Sell Amount</h5>
                    <h3 class="text-primary" id="grandTotalSell">$0.00</h3>
                </div>
                <div class="col-md-4">
                    <h5>Profit</h5>
                    <h3 class="text-success" id="grandProfit">$0.00</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex gap-2 mb-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-check-circle me-2"></i>Create Deal
        </button>
        <a href="deals.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
    </div>
</form>

<!-- Item Template -->
<template id="dealItemTemplate">
    <div class="deal-item-row">
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="form-label">Product</label>
                <select class="form-select product-select" onchange="updateProductName(this)">
                    <option value="">Select or type new</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>" data-name="<?php echo sanitize($product['name']); ?>">
                            <?php echo sanitize($product['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control product-name" required>
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Buy Qty</label>
                <input type="number" step="0.01" min="0" class="form-control buy-qty" value="1" 
                       onchange="calculateItemTotal(this.closest('.deal-item-row'))">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Buy Price</label>
                <input type="number" step="0.01" min="0" class="form-control buy-price" value="0" 
                       onchange="calculateItemTotal(this.closest('.deal-item-row'))">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Total Buy</label>
                <input type="number" step="0.01" class="form-control total-buy" value="0" readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-2"></div>
            <div class="col-md-3 mb-2"></div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Sell Qty</label>
                <input type="number" step="0.01" min="0" class="form-control sell-qty" value="1" 
                       onchange="calculateItemTotal(this.closest('.deal-item-row'))">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Sell Price</label>
                <input type="number" step="0.01" min="0" class="form-control sell-price" value="0" 
                       onchange="calculateItemTotal(this.closest('.deal-item-row'))">
            </div>
            <div class="col-md-2 mb-2">
                <label class="form-label">Total Sell</label>
                <input type="number" step="0.01" class="form-control total-sell" value="0" readonly>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 mb-2">
                <label class="form-label">Item Profit</label>
                <input type="number" step="0.01" class="form-control item-profit" value="0" readonly>
            </div>
            <div class="col-md-2 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeDealItemRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<?php
$extraScripts = <<<'SCRIPT'
<script>
let itemCounter = 1;

function updateProductName(select) {
    const row = select.closest('.deal-item-row');
    const productName = row.querySelector('.product-name');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption && selectedOption.dataset.name) {
        productName.value = selectedOption.dataset.name;
    }
}

function addDealItemRow() {
    const container = document.getElementById('dealItemsContainer');
    const template = document.getElementById('dealItemTemplate');
    const clone = template.content.cloneNode(true);
    
    // Update names for form array
    clone.querySelectorAll('[class*="product-select"]').forEach(el => {
        el.name = `items[${itemCounter}][product_id]`;
    });
    clone.querySelectorAll('.product-name').forEach(el => {
        el.name = `items[${itemCounter}][product_name]`;
    });
    clone.querySelectorAll('.buy-qty').forEach(el => {
        el.name = `items[${itemCounter}][buy_qty]`;
    });
    clone.querySelectorAll('.buy-price').forEach(el => {
        el.name = `items[${itemCounter}][buy_price]`;
    });
    clone.querySelectorAll('.total-buy').forEach(el => {
        el.name = `items[${itemCounter}][total_buy]`;
    });
    clone.querySelectorAll('.sell-qty').forEach(el => {
        el.name = `items[${itemCounter}][sell_qty]`;
    });
    clone.querySelectorAll('.sell-price').forEach(el => {
        el.name = `items[${itemCounter}][sell_price]`;
    });
    clone.querySelectorAll('.total-sell').forEach(el => {
        el.name = `items[${itemCounter}][total_sell]`;
    });
    clone.querySelectorAll('.item-profit').forEach(el => {
        el.name = `items[${itemCounter}][profit]`;
    });
    
    container.appendChild(clone);
    itemCounter++;
}

function removeDealItemRow(btn) {
    const rows = document.querySelectorAll('.deal-item-row');
    if (rows.length > 1) {
        btn.closest('.deal-item-row').remove();
        calculateDealTotals();
    } else {
        alert('At least one item is required');
    }
}

function calculateItemTotal(row) {
    const buyQty = parseFloat(row.querySelector('.buy-qty').value) || 0;
    const buyPrice = parseFloat(row.querySelector('.buy-price').value) || 0;
    const sellQty = parseFloat(row.querySelector('.sell-qty').value) || 0;
    const sellPrice = parseFloat(row.querySelector('.sell-price').value) || 0;
    
    const totalBuy = buyQty * buyPrice;
    const totalSell = sellQty * sellPrice;
    const profit = totalSell - totalBuy;
    
    row.querySelector('.total-buy').value = totalBuy.toFixed(2);
    row.querySelector('.total-sell').value = totalSell.toFixed(2);
    row.querySelector('.item-profit').value = profit.toFixed(2);
    
    calculateDealTotals();
}

function calculateDealTotals() {
    let totalBuy = 0;
    let totalSell = 0;
    
    document.querySelectorAll('.deal-item-row').forEach(row => {
        totalBuy += parseFloat(row.querySelector('.total-buy').value) || 0;
        totalSell += parseFloat(row.querySelector('.total-sell').value) || 0;
    });
    
    const profit = totalSell - totalBuy;
    
    document.getElementById('grandTotalBuy').textContent = '$' + totalBuy.toFixed(2);
    document.getElementById('grandTotalSell').textContent = '$' + totalSell.toFixed(2);
    document.getElementById('grandProfit').textContent = '$' + profit.toFixed(2);
    document.getElementById('grandProfit').className = profit >= 0 ? 'text-success' : 'text-danger';
}
</script>
SCRIPT;
?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
