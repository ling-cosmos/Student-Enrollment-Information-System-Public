<?php
require_once dirname(dirname(__DIR__)) . '/config/constants.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
requireRole('admin');

$id = (int)($_GET['id'] ?? 0);
$stmt = getPDO()->prepare('SELECT id, username, full_name FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user || $id === (int)$_SESSION['user_id']) {
    flash('warning', 'Use Change Password to update your own account.');
    redirect(BASE_URL . '/admin/users/manage.php');
}
$pageTitle = 'Reset User Password';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
?>
<nav class="seis-breadcrumb" aria-label="Breadcrumb"><a href="<?= BASE_URL ?>/admin/dashboard.php">System</a><i class="bi bi-chevron-right"></i><a href="<?= BASE_URL ?>/admin/users/manage.php">Manage Users</a><i class="bi bi-chevron-right"></i><span aria-current="page">Reset Password</span></nav>
<div class="page-header"><div class="page-header-left"><h1>Reset Password</h1><p class="page-subtitle">Set a temporary password for <?= sanitize($user['full_name']) ?></p></div><a href="<?= BASE_URL ?>/admin/users/manage.php" class="btn-seis-outline"><i class="bi bi-arrow-left"></i> Back</a></div>
<div class="user-form-wrap"><form method="POST" action="<?= BASE_URL ?>/handlers/user_handler.php" data-loading-form><input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="reset_password"><input type="hidden" name="id" value="<?= $id ?>"><div class="seis-card"><div class="seis-card-header"><i class="bi bi-shield-lock"></i> <?= sanitize($user['username']) ?></div><div class="seis-card-body"><div class="form-group"><label class="seis-label" for="password">New Password <span class="req">*</span></label><input class="seis-input" type="password" id="password" name="password" minlength="8" required autocomplete="new-password"><p class="form-hint">At least 8 characters with uppercase, lowercase, and a number.</p></div><div class="form-group"><label class="seis-label" for="confirm_password">Confirm Password <span class="req">*</span></label><input class="seis-input" type="password" id="confirm_password" name="confirm_password" minlength="8" required autocomplete="new-password"></div></div><div class="seis-card-footer"><button class="btn-seis" type="submit"><i class="bi bi-key"></i> Reset Password</button><a class="btn-seis-outline" href="<?= BASE_URL ?>/admin/users/manage.php">Cancel</a></div></div></form></div>
<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
