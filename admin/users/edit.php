<?php
require_once dirname(dirname(__DIR__)) . '/config/constants.php';
require_once dirname(dirname(__DIR__)) . '/config/database.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
session_start();
requireRole('admin');

$id = (int)($_GET['id'] ?? 0);
$stmt = getPDO()->prepare('SELECT id, username, full_name, email, role FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    flash('danger', 'User account not found.');
    redirect(BASE_URL . '/admin/users/manage.php');
}
$editingSelf = $id === (int)$_SESSION['user_id'];
$pageTitle = 'Edit User';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<nav class="seis-breadcrumb" aria-label="Breadcrumb">
  <a href="<?= BASE_URL ?>/admin/dashboard.php">System</a><i class="bi bi-chevron-right"></i>
  <a href="<?= BASE_URL ?>/admin/users/manage.php">Manage Users</a><i class="bi bi-chevron-right"></i>
  <span aria-current="page">Edit User</span>
</nav>

<div class="page-header">
  <div class="page-header-left"><h1>Edit User</h1><p class="page-subtitle">Update account identity and access role</p></div>
  <a href="<?= BASE_URL ?>/admin/users/manage.php" class="btn-seis-outline"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="user-form-wrap">
  <form method="POST" action="<?= BASE_URL ?>/handlers/user_handler.php" data-loading-form>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="seis-card">
      <div class="seis-card-header"><i class="bi bi-person-gear"></i> Account Details</div>
      <div class="seis-card-body">
        <div class="form-group"><label class="seis-label" for="full_name">Full Name <span class="req">*</span></label><input class="seis-input" id="full_name" name="full_name" required value="<?= sanitize($user['full_name']) ?>"></div>
        <div class="form-grid-2">
          <div class="form-group"><label class="seis-label" for="username">Username <span class="req">*</span></label><input class="seis-input" id="username" name="username" required value="<?= sanitize($user['username']) ?>"></div>
          <div class="form-group"><label class="seis-label" for="email">Email <span class="req">*</span></label><input class="seis-input" type="email" id="email" name="email" required value="<?= sanitize($user['email']) ?>"></div>
        </div>
        <div class="form-group"><label class="seis-label" for="role">Role</label><select class="seis-select" id="role" name="role" <?= $editingSelf ? 'disabled' : '' ?>><?php foreach (['admin' => 'Administrator', 'registrar' => 'Registrar', 'viewer' => 'Viewer'] as $value => $label): ?><option value="<?= $value ?>" <?= $user['role'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select><?php if ($editingSelf): ?><p class="form-hint">You cannot change your own role.</p><?php endif; ?></div>
      </div>
      <div class="seis-card-footer"><button class="btn-seis" type="submit"><i class="bi bi-floppy"></i> Save Changes</button><a class="btn-seis-outline" href="<?= BASE_URL ?>/admin/users/manage.php">Cancel</a></div>
    </div>
  </form>
</div>
<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
