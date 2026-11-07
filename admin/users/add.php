<?php
$pageTitle = 'Add User';
$activeNav = 'users_add';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireRole('admin');
?>

<nav class="seis-breadcrumb" aria-label="Breadcrumb"><a href="<?= BASE_URL ?>/admin/dashboard.php">System</a><i class="bi bi-chevron-right"></i><a href="<?= BASE_URL ?>/admin/users/manage.php">Manage Users</a><i class="bi bi-chevron-right"></i><span aria-current="page">Add User</span></nav>

<div class="page-header">
  <div class="page-header-left">
    <h1>Add System User</h1>
    <p class="page-subtitle">Create a new administrator or registrar account</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/users/manage.php" class="btn-seis-outline">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>
</div>

<div class="user-form-wrap">
  <form method="POST" action="<?= BASE_URL ?>/handlers/user_handler.php" data-loading-form>
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="action"     value="add">

    <div class="seis-card">
      <div class="seis-card-header"><i class="bi bi-shield-plus"></i> Account Details</div>
      <div class="seis-card-body">

        <div class="form-group">
          <label class="seis-label">Full Name <span class="req">*</span></label>
          <input type="text" name="full_name" class="seis-input"
                 placeholder="Full name" required
                 value="<?= sanitize($_POST['full_name'] ?? '') ?>">
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="seis-label">Username <span class="req">*</span></label>
            <input type="text" name="username" class="seis-input"
                   placeholder="Username" required autocomplete="off"
                   value="<?= sanitize($_POST['username'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="seis-label">Email <span class="req">*</span></label>
            <input type="email" name="email" class="seis-input"
                   placeholder="user@school.edu" required
                   value="<?= sanitize($_POST['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="seis-label">Password <span class="req">*</span></label>
            <input type="password" name="password" class="seis-input"
                   placeholder="Min. 8 characters" required autocomplete="new-password">
          </div>
          <div class="form-group">
            <label class="seis-label">Confirm Password <span class="req">*</span></label>
            <input type="password" name="confirm_password" class="seis-input"
                   placeholder="Repeat password" required>
          </div>
        </div>

        <div class="form-group">
          <label class="seis-label">Role <span class="req">*</span></label>
          <select name="role" class="seis-select" required>
            <option value="registrar" <?= ($_POST['role'] ?? '') === 'registrar' ? 'selected' : '' ?>>Registrar</option>
            <option value="admin"     <?= ($_POST['role'] ?? '') === 'admin'     ? 'selected' : '' ?>>Administrator</option>
            <option value="viewer"    <?= ($_POST['role'] ?? '') === 'viewer'    ? 'selected' : '' ?>>Viewer (read-only)</option>
          </select>
          <p class="form-hint">Admin has full access. Registrar can manage students &amp; enrollment. Viewer is read-only.</p>
        </div>

      </div>
      <div class="seis-card-footer">
        <button type="submit" class="btn-seis"><i class="bi bi-floppy"></i> Create User</button>
        <a href="<?= BASE_URL ?>/admin/users/manage.php" class="btn-seis-outline">Cancel</a>
      </div>
    </div>
  </form>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
