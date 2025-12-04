<?php
/**
 * Dashboard (Home Page)
 * Customer & Real-Time Trading Management System
 */

// Check login before including header (which outputs HTML)
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

require_once __DIR__ . '/models/DealModel.php';
require_once __DIR__ . '/models/CustomerModel.php';
require_once __DIR__ . '/models/ExpenseModel.php';

$dealModel = new DealModel();
$customerModel = new CustomerModel();
$expenseModel = new ExpenseModel();

$today = date('Y-m-d');

// Get today's statistics
$todayStats = $dealModel->getDayStats($today);
$todayExpense = $expenseModel->getDayTotal($today);
$todayNetProfit = $todayStats['total_profit'] - $todayExpense;

// Get total statistics
$totalCustomers = $customerModel->getTotalCount();
$totalDeals = $dealModel->getTotalCount();
$totalDue = $dealModel->getTotalDue();

// Get recent deals
$recentDeals = $dealModel->getAll(['start_date' => date('Y-m-d', strtotime('-7 days'))]);
$recentDeals = array_slice($recentDeals, 0, 5);

// Get customers with dues
$customersWithDues = $customerModel->getWithDues();
$customersWithDues = array_slice($customersWithDues, 0, 5);
?>

<div class="row mb-4">
    <!-- Today's Sell -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="card-icon bg-primary me-3">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div>
                    <p class="stats-label mb-1">Today's Sell</p>
                    <h4 class="stats-value mb-0"><?php echo formatCurrency($todayStats['total_sell']); ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Today's Buy -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="card-icon bg-info me-3">
                    <i class="bi bi-bag"></i>
                </div>
                <div>
                    <p class="stats-label mb-1">Today's Buy</p>
                    <h4 class="stats-value mb-0"><?php echo formatCurrency($todayStats['total_buy']); ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Today's Gross Profit -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card success">
            <div class="card-body d-flex align-items-center">
                <div class="card-icon bg-success me-3">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div>
                    <p class="stats-label mb-1">Gross Profit</p>
                    <h4 class="stats-value mb-0"><?php echo formatCurrency($todayStats['total_profit']); ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Today's Expense -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card danger">
            <div class="card-body d-flex align-items-center">
                <div class="card-icon bg-danger me-3">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <p class="stats-label mb-1">Today's Expense</p>
                    <h4 class="stats-value mb-0"><?php echo formatCurrency($todayExpense); ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Net Profit -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card <?php echo $todayNetProfit >= 0 ? 'success' : 'danger'; ?>">
            <div class="card-body d-flex align-items-center">
                <div class="card-icon <?php echo $todayNetProfit >= 0 ? 'bg-success' : 'bg-danger'; ?> me-3">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div>
                    <p class="stats-label mb-1">Net Profit Today</p>
                    <h4 class="stats-value mb-0"><?php echo formatCurrency($todayNetProfit); ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Due -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card warning">
            <div class="card-body d-flex align-items-center">
                <div class="card-icon bg-warning me-3">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div>
                    <p class="stats-label mb-1">Total Due</p>
                    <h4 class="stats-value mb-0"><?php echo formatCurrency($totalDue); ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Customers -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="card-icon bg-primary me-3">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <p class="stats-label mb-1">Total Customers</p>
                    <h4 class="stats-value mb-0"><?php echo $totalCustomers; ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Deals -->
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card stats-card">
            <div class="card-body d-flex align-items-center">
                <div class="card-icon bg-info me-3">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <p class="stats-label mb-1">Total Deals</p>
                    <h4 class="stats-value mb-0"><?php echo $totalDeals; ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Deals -->
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Recent Deals</span>
                <a href="deals.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentDeals)): ?>
                    <p class="text-muted text-center py-4">No deals found</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Deal #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Profit</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentDeals as $deal): ?>
                                <tr>
                                    <td><a href="deal-view.php?id=<?php echo $deal['id']; ?>"><?php echo sanitize($deal['deal_number']); ?></a></td>
                                    <td><?php echo sanitize($deal['customer_name']); ?></td>
                                    <td><?php echo formatCurrency($deal['total_sell_amount']); ?></td>
                                    <td class="<?php echo $deal['profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo formatCurrency($deal['profit']); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-status-<?php echo $deal['status']; ?>">
                                            <?php echo ucfirst($deal['status']); ?>
                                        </span>
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
    
    <!-- Customers with Dues -->
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle me-2"></i>Outstanding Dues</span>
                <a href="reports.php?type=due" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($customersWithDues)): ?>
                    <p class="text-muted text-center py-4">No outstanding dues</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($customersWithDues as $customer): 
                            $due = $customer['total_sell'] - $customer['total_paid'];
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo sanitize($customer['name']); ?></strong>
                                <br><small class="text-muted"><?php echo sanitize($customer['phone']); ?></small>
                            </div>
                            <span class="badge bg-warning text-dark fs-6"><?php echo formatCurrency($due); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Quick Actions
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="deal-add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>New Deal
                    </a>
                    <a href="customer-add.php" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus me-2"></i>Add Customer
                    </a>
                    <a href="payment-add.php" class="btn btn-outline-success">
                        <i class="bi bi-credit-card me-2"></i>Record Payment
                    </a>
                    <a href="expense-add.php" class="btn btn-outline-danger">
                        <i class="bi bi-wallet me-2"></i>Add Expense
                    </a>
                    <a href="reports.php" class="btn btn-outline-info">
                        <i class="bi bi-bar-chart me-2"></i>View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
