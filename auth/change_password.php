<?php
// auth/change_password.php
// Allows logged-in users to change their password

require_once '../includes/header.php';
requireLogin();  // Must be logged in to change password

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'All fields are required.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New password and confirmation do not match.';
    } elseif (strlen($newPassword) < 8) {
        $error = 'New password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $error = 'New password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $newPassword)) {
        $error = 'New password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $error = 'New password must contain at least one number.';
    } elseif (!preg_match('/[@$!%*?&#]/', $newPassword)) {
        $error = 'New password must contain at least one special character (@$!%*?&#).';
    } else {
        // Verify current password
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");

            if ($updateStmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                flash('success', 'Password changed successfully!');
                redirect(BASE_URL . '/admin/dashboard.php');
            } else {
                $error = 'Failed to update password. Please try again.';
            }
        }
    }
}
?>

    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="bi bi-key-fill"></i> Change Password</h1>
            <p class="page-subtitle">Update your account password</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn-seis-outline">
            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="seis-card">
                <div class="seis-card-body">

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= sanitize($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="needs-validation">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                        <!-- Current Password -->
                        <div class="form-group">
                            <label for="current_password" class="seis-label">
                                Current Password <span class="req">*</span>
                            </label>
                            <input type="password" class="seis-input" id="current_password"
                                   name="current_password" placeholder="Enter your current password" required>
                        </div>

                        <hr class="my-4">

                        <!-- New Password -->
                        <div class="form-group">
                            <label for="new_password" class="seis-label">
                                New Password <span class="req">*</span>
                            </label>
                            <input type="password" class="seis-input" id="new_password"
                                   name="new_password" placeholder="Enter new password"
                                   minlength="8" required>
                            <div class="form-text">
                                <small class="d-block">Password must contain:</small>
                                <ul class="small mb-0 ps-3">
                                    <li>At least 8 characters</li>
                                    <li>One uppercase letter (A-Z)</li>
                                    <li>One lowercase letter (a-z)</li>
                                    <li>One number (0-9)</li>
                                    <li>One special character (@$!%*?&#)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="form-group">
                            <label for="confirm_password" class="seis-label">
                                Confirm New Password <span class="req">*</span>
                            </label>
                            <input type="password" class="seis-input" id="confirm_password"
                                   name="confirm_password" placeholder="Re-enter new password"
                                   minlength="8" required>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn-seis">
                                <i class="bi bi-check-circle me-2"></i>Change Password
                            </button>
                            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn-seis-outline">
                                Cancel
                            </a>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Security Tips -->
            <div class="seis-card mt-3">
                <div class="seis-card-body">
                    <h6 class="text-muted mb-2">
                        <i class="bi bi-info-circle me-2"></i>Password Security Tips
                    </h6>
                    <ul class="small text-muted mb-0">
                        <li>Don't use personal information (name, birthdate, etc.)</li>
                        <li>Avoid common words or patterns (e.g., "password123")</li>
                        <li>Don't reuse passwords from other websites</li>
                        <li>Change your password regularly (every 3-6 months)</li>
                        <li>Never share your password with anyone</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php require_once '../includes/footer.php'; ?>
