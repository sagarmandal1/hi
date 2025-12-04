<?php
/**
 * Reports Page
 * Customer & Real-Time Trading Management System
 */

// Check login before including header (which outputs HTML)
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pageTitle = 'Reports';
require_once __DIR__ . '/includes/header.php';

require_once __DIR__ . '/models/DealModel.php';
require_once __DIR__ . '/models/CustomerModel.php';
require_once __DIR__ . '/models/ExpenseModel.php';
require_once __DIR__ . '/models/PaymentModel.php';

$dealModel = new DealModel();
$customerModel = new CustomerModel();
$expenseModel = new ExpenseModel();
$paymentModel = new PaymentModel();

// Get report type
$reportType = $_GET['type'] ?? 'profit';

// Get date range filter
$filter = $_GET['filter'] ?? 'this_month';
$dateRange = getDateRange($filter);
$startDate = $_GET['start_date'] ?? $dateRange['start'];
$endDate = $_GET['end_date'] ?? $dateRange['end'];

// Get report data based on type
$reportData = [];

switch ($reportType) {
    case 'profit':
        $dealStats = $dealModel->getDateRangeStats($startDate, $endDate);
        $totalExpense = $expenseModel->getDateRangeTotal($startDate, $endDate);
        $reportData = [
            'total_buy' => $dealStats['total_buy'],
            'total_sell' => $dealStats['total_sell'],
            'gross_profit' => $dealStats['total_profit'],
            'total_expense' => $totalExpense,
            'net_profit' => $dealStats['total_profit'] - $totalExpense,
            'total_deals' => $dealStats['total_deals']
        ];
        break;
        
    case 'customer':
        $customers = $customerModel->getAll();
        foreach ($customers as &$customer) {
            $stats = $customerModel->getStats($customer['id']);
            $customer['stats'] = $stats;
        }
        $reportData = $customers;
        break;
        
    case 'due':
        $reportData = $customerModel->getWithDues();
        break;
        
    case 'deals':
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        if (!empty($_GET['customer_id'])) {
            $filters['customer_id'] = (int)$_GET['customer_id'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        $reportData = $dealModel->getAll($filters);
        break;
}

$customers = $customerModel->getAll();
?>

<div class="page-header">
    <h4>Reports</h4>
    <p>View and analyze business performance</p>
</div>

<!-- Report Type Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?php echo $reportType === 'profit' ? 'active' : ''; ?>" href="?type=profit">
            <i class="bi bi-graph-up me-2"></i>Profit/Loss
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $reportType === 'customer' ? 'active' : ''; ?>" href="?type=customer">
            <i class="bi bi-people me-2"></i>Customer-wise
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $reportType === 'due' ? 'active' : ''; ?>" href="?type=due">
            <i class="bi bi-exclamation-triangle me-2"></i>Due Report
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $reportType === 'deals' ? 'active' : ''; ?>" href="?type=deals">
            <i class="bi bi-list-ul me-2"></i>Deal List
        </a>
    </li>
</ul>

<?php if ($reportType === 'profit' || $reportType === 'deals'): ?>
<!-- Date Filter -->
<div class="card mb-4 filter-card">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="type" value="<?php echo sanitize($reportType); ?>">
            
            <div class="col-md-2">
                <label class="form-label">Quick Filter</label>
                <select name="filter" class="form-select" onchange="this.form.submit()">
                    <option value="today" <?php echo $filter === 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="yesterday" <?php echo $filter === 'yesterday' ? 'selected' : ''; ?>>Yesterday</option>
                    <option value="this_week" <?php echo $filter === 'this_week' ? 'selected' : ''; ?>>This Week</option>
                    <option value="this_month" <?php echo $filter === 'this_month' ? 'selected' : ''; ?>>This Month</option>
                    <option value="last_month" <?php echo $filter === 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                    <option value="this_year" <?php echo $filter === 'this_year' ? 'selected' : ''; ?>>This Year</option>
                    <option value="custom" <?php echo $filter === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo sanitize($startDate); ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo sanitize($endDate); ?>">
            </div>
            
            <?php if ($reportType === 'deals'): ?>
            <div class="col-md-2">
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
            <?php endif; ?>
            
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Apply</button>
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Report Content -->
<?php if ($reportType === 'profit'): ?>
<!-- Profit/Loss Report -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-bar-chart me-2"></i>Profit/Loss Report
        <span class="badge bg-secondary ms-2"><?php echo formatDate($startDate); ?> - <?php echo formatDate($endDate); ?></span>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-2 mb-3">
                <div class="p-3 bg-light rounded">
                    <h6 class="text-muted">Total Deals</h6>
                    <h3><?php echo $reportData['total_deals']; ?></h3>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="p-3 bg-light rounded">
                    <h6 class="text-muted">Total Buy</h6>
                    <h3 class="text-info"><?php echo formatCurrency($reportData['total_buy']); ?></h3>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="p-3 bg-light rounded">
                    <h6 class="text-muted">Total Sell</h6>
                    <h3 class="text-primary"><?php echo formatCurrency($reportData['total_sell']); ?></h3>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="p-3 bg-light rounded">
                    <h6 class="text-muted">Gross Profit</h6>
                    <h3 class="<?php echo $reportData['gross_profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo formatCurrency($reportData['gross_profit']); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="p-3 bg-light rounded">
                    <h6 class="text-muted">Total Expense</h6>
                    <h3 class="text-danger"><?php echo formatCurrency($reportData['total_expense']); ?></h3>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="p-3 <?php echo $reportData['net_profit'] >= 0 ? 'bg-success' : 'bg-danger'; ?> text-white rounded">
                    <h6>Net Profit</h6>
                    <h3><?php echo formatCurrency($reportData['net_profit']); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($reportType === 'customer'): ?>
<!-- Customer-wise Report -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-people me-2"></i>Customer-wise Report
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th class="text-center">Total Deals</th>
                        <th class="text-end">Total Sell</th>
                        <th class="text-end">Total Paid</th>
                        <th class="text-end">Total Due</th>
                        <th class="text-end">Profit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $customer): ?>
                    <tr>
                        <td>
                            <a href="customer-view.php?id=<?php echo $customer['id']; ?>">
                                <?php echo sanitize($customer['name']); ?>
                            </a>
                        </td>
                        <td class="text-center"><?php echo $customer['stats']['total_deals']; ?></td>
                        <td class="text-end"><?php echo formatCurrency($customer['stats']['total_sell']); ?></td>
                        <td class="text-end text-success"><?php echo formatCurrency($customer['stats']['total_paid']); ?></td>
                        <td class="text-end <?php echo $customer['stats']['total_due'] > 0 ? 'text-danger' : 'text-success'; ?>">
                            <?php echo formatCurrency(max(0, $customer['stats']['total_due'])); ?>
                        </td>
                        <td class="text-end <?php echo $customer['stats']['total_profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo formatCurrency($customer['stats']['total_profit']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php elseif ($reportType === 'due'): ?>
<!-- Due Report -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-exclamation-triangle me-2"></i>Outstanding Dues Report
    </div>
    <div class="card-body">
        <?php if (empty($reportData)): ?>
            <div class="text-center py-5">
                <i class="bi bi-check-circle display-1 text-success"></i>
                <p class="mt-3 text-muted">No outstanding dues!</p>
            </div>
        <?php else: ?>
            <?php $totalDue = 0; ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th class="text-end">Total Sell</th>
                            <th class="text-end">Total Paid</th>
                            <th class="text-end">Due Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $customer): 
                            $due = $customer['total_sell'] - $customer['total_paid'];
                            $totalDue += $due;
                        ?>
                        <tr>
                            <td>
                                <a href="customer-view.php?id=<?php echo $customer['id']; ?>">
                                    <?php echo sanitize($customer['name']); ?>
                                </a>
                            </td>
                            <td><?php echo sanitize($customer['phone']); ?></td>
                            <td class="text-end"><?php echo formatCurrency($customer['total_sell']); ?></td>
                            <td class="text-end text-success"><?php echo formatCurrency($customer['total_paid']); ?></td>
                            <td class="text-end text-danger"><strong><?php echo formatCurrency($due); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Total Outstanding:</th>
                            <th class="text-end text-danger"><?php echo formatCurrency($totalDue); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php elseif ($reportType === 'deals'): ?>
<!-- Deal List Report -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-list-ul me-2"></i>Deal List
            <span class="badge bg-secondary ms-2"><?php echo formatDate($startDate); ?> - <?php echo formatDate($endDate); ?></span>
        </span>
        <a href="export-deals.php?start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>&customer_id=<?php echo urlencode($_GET['customer_id'] ?? ''); ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>" 
           class="btn btn-sm btn-outline-success">
            <i class="bi bi-download me-1"></i>Export CSV
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($reportData)): ?>
            <p class="text-muted text-center py-4">No deals found for the selected criteria</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Deal #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th class="text-end">Buy Amount</th>
                            <th class="text-end">Sell Amount</th>
                            <th class="text-end">Profit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalBuy = 0;
                        $totalSell = 0;
                        $totalProfit = 0;
                        foreach ($reportData as $deal): 
                            $totalBuy += $deal['total_buy_amount'];
                            $totalSell += $deal['total_sell_amount'];
                            $totalProfit += $deal['profit'];
                        ?>
                        <tr>
                            <td><a href="deal-view.php?id=<?php echo $deal['id']; ?>"><?php echo sanitize($deal['deal_number']); ?></a></td>
                            <td><?php echo formatDate($deal['deal_date']); ?></td>
                            <td><?php echo sanitize($deal['customer_name']); ?></td>
                            <td class="text-end"><?php echo formatCurrency($deal['total_buy_amount']); ?></td>
                            <td class="text-end"><?php echo formatCurrency($deal['total_sell_amount']); ?></td>
                            <td class="text-end <?php echo $deal['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo formatCurrency($deal['profit']); ?>
                            </td>
                            <td><span class="badge badge-status-<?php echo $deal['status']; ?>"><?php echo ucfirst($deal['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Totals:</th>
                            <th class="text-end"><?php echo formatCurrency($totalBuy); ?></th>
                            <th class="text-end"><?php echo formatCurrency($totalSell); ?></th>
                            <th class="text-end <?php echo $totalProfit >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo formatCurrency($totalProfit); ?>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-3">
                <small class="text-muted">Showing <?php echo count($reportData); ?> deal(s)</small>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
