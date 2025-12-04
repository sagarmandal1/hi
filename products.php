<?php
/**
 * Product List Page
 * Customer & Real-Time Trading Management System
 */

$pageTitle = 'Products';
require_once __DIR__ . '/includes/header.php';
requireLogin();

require_once __DIR__ . '/models/ProductModel.php';

$productModel = new ProductModel();

$products = $productModel->getAll();
$categories = $productModel->getCategories();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h4>Product Reference</h4>
        <p>Manage your product catalog (no stock management)</p>
    </div>
    <a href="product-add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Product
    </a>
</div>

<div class="row">
    <!-- Products Card -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-box-seam me-2"></i>Products
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-box-seam display-1 text-muted"></i>
                        <p class="mt-3 text-muted">No products found</p>
                        <a href="product-add.php" class="btn btn-primary">Add Your First Product</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $index => $product): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><strong><?php echo sanitize($product['name']); ?></strong></td>
                                    <td>
                                        <?php if ($product['category_name']): ?>
                                            <span class="badge bg-info"><?php echo sanitize($product['category_name']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo sanitize($product['notes'] ?? '-'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="product-delete.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-danger" title="Delete"
                                               onclick="return confirmDelete('Are you sure you want to delete this product?')">
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
                        <small class="text-muted">Showing <?php echo count($products); ?> product(s)</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Categories Card -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-tags me-2"></i>Categories</span>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <p class="text-muted text-center">No categories found</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($categories as $category): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo sanitize($category['name']); ?>
                            <?php if ($category['description']): ?>
                                <small class="text-muted"><?php echo sanitize($category['description']); ?></small>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="category-add.php">
                <?php echo csrfField(); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="category_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_description" class="form-label">Description</label>
                        <textarea class="form-control" id="category_description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
