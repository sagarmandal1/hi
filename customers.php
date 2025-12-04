<?php
/**
 * Customer List Page
 * Customer & Real-Time Trading Management System
 */

$pageTitle = 'Customers';
require_once __DIR__ . '/includes/header.php';
requireLogin();

require_once __DIR__ . '/models/CustomerModel.php';

$customerModel = new CustomerModel();

// Handle filters
$filters = [];
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $filters['is_active'] = (int)$_GET['status'];
}

$customers = $customerModel->getAll($filters);
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4>Customer Management</h4>
        <p>Manage your customers</p>
    </div>
    <a href="customer-add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Customer
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Search by name or phone..." 
                           value="<?php echo sanitize($_GET['search'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] === '1') ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] === '0') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="customers.php" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Customer List -->
<div class="card">
    <div class="card-body">
        <?php if (empty($customers)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people display-1 text-muted"></i>
                <p class="mt-3 text-muted">No customers found</p>
                <a href="customer-add.php" class="btn btn-primary">Add Your First Customer</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $index => $customer): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <a href="customer-view.php?id=<?php echo $customer['id']; ?>" class="text-decoration-none">
                                    <strong><?php echo sanitize($customer['name']); ?></strong>
                                </a>
                            </td>
                            <td><?php echo sanitize($customer['phone']); ?></td>
                            <td><?php echo sanitize($customer['email'] ?? '-'); ?></td>
                            <td>
                                <?php if ($customer['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="customer-view.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="customer-edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="customer-delete.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline-danger" title="Delete"
                                       onclick="return confirmDelete('Are you sure you want to delete this customer?')">
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
                <small class="text-muted">Showing <?php echo count($customers); ?> customer(s)</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
