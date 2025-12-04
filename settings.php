<?php
/**
 * Settings Page
 * Customer & Real-Time Trading Management System
 */

// Check login before including header (which outputs HTML)
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$pageTitle = 'Settings';
require_once __DIR__ . '/includes/header.php';

require_once __DIR__ . '/models/UserModel.php';

$userModel = new UserModel();
$currentUser = getCurrentUser();

$errors = [];
$success = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($currentPassword)) {
        $errors[] = 'Current password is required.';
    }
    if (empty($newPassword)) {
        $errors[] = 'New password is required.';
    }
    if (strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New passwords do not match.';
    }

    // Verify current password
    if (empty($errors)) {
        $user = $userModel->findById($currentUser['id']);
        if (!$userModel->verifyPassword($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
    }

    if (empty($errors)) {
        if ($userModel->updatePassword($currentUser['id'], $newPassword)) {
            $success = 'Password changed successfully!';
        } else {
            $errors[] = 'Failed to change password. Please try again.';
        }
    }
}
?>

<div class="page-header">
    <h4>Settings</h4>
    <p>Manage your account and system settings</p>
</div>

<div class="row">
    <!-- Profile Info -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-circle me-2"></i>Profile Information
            </div>
            <div class="card-body text-center">
                <div class="customer-avatar mx-auto mb-3">
                    <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                </div>
                <h5><?php echo sanitize($currentUser['name']); ?></h5>
                <p class="text-muted"><?php echo sanitize($currentUser['email']); ?></p>
                <span class="badge bg-primary"><?php echo ucfirst($currentUser['role']); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-key me-2"></i>Change Password
            </div>
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
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Database Backup Info -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-database me-2"></i>Database Backup
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Manual Backup Instructions:</strong>
        </div>
        <p>To backup your database, you can use one of the following methods:</p>
        <ol>
            <li>
                <strong>Using phpMyAdmin:</strong>
                <ul>
                    <li>Log in to phpMyAdmin</li>
                    <li>Select your database</li>
                    <li>Click on "Export" tab</li>
                    <li>Choose "Quick" export method and SQL format</li>
                    <li>Click "Go" to download the backup file</li>
                </ul>
            </li>
            <li class="mt-3">
                <strong>Using Command Line (mysqldump):</strong>
                <pre class="bg-light p-3 rounded"><code>mysqldump -u [username] -p [database_name] > backup_$(date +%Y%m%d).sql</code></pre>
            </li>
        </ol>
        <div class="alert alert-warning mt-3">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Important:</strong> Regular backups are recommended. Store backup files in a secure location.
        </div>
    </div>
</div>

<!-- System Info -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-info-circle me-2"></i>System Information
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th>PHP Version:</th>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th>Server:</th>
                        <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                    </tr>
                    <tr>
                        <th>Database:</th>
                        <td>MySQL (PDO)</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <th>System Name:</th>
                        <td><?php echo SITE_NAME; ?></td>
                    </tr>
                    <tr>
                        <th>Database:</th>
                        <td>Connected</td>
                    </tr>
                    <tr>
                        <th>Current Time:</th>
                        <td><?php echo date('Y-m-d H:i:s'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
