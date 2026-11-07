<?php
$pageTitle = 'Add Course';
$activeNav = 'courses_add';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Add New Course</h1>
    <p class="page-subtitle">Create a new course offering</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/courses/view.php" class="btn-seis-outline">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>
</div>

<div style="max-width:680px;">
  <form method="POST" action="<?= BASE_URL ?>/handlers/course_handler.php">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="action"     value="add">

    <div class="seis-card">
      <div class="seis-card-header"><i class="bi bi-book-half"></i> Course Information</div>
      <div class="seis-card-body">

        <div class="form-grid-2">
          <div class="form-group">
            <label class="seis-label">Course Code <span class="req">*</span></label>
            <input type="text" name="course_code" class="seis-input"
                   placeholder="e.g. CS101" required
                   value="<?= sanitize($_POST['course_code'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="seis-label">Units <span class="req">*</span></label>
            <input type="number" name="units" class="seis-input"
                   min="1" max="9" value="<?= sanitize($_POST['units'] ?? '3') ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="seis-label">Course Name <span class="req">*</span></label>
          <input type="text" name="course_name" class="seis-input"
                 placeholder="Full course name" required
                 value="<?= sanitize($_POST['course_name'] ?? '') ?>">
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="seis-label">Capacity</label>
            <input type="number" name="capacity" class="seis-input"
                   min="1" placeholder="40"
                   value="<?= sanitize($_POST['capacity'] ?? '40') ?>">
            <p class="form-hint">Max students per semester.</p>
          </div>
          <div class="form-group">
            <label class="seis-label">Status</label>
            <select name="is_active" class="seis-select">
              <option value="1" <?= ($_POST['is_active'] ?? '1') === '1' ? 'selected' : '' ?>>Active</option>
              <option value="0" <?= ($_POST['is_active'] ?? '1') === '0' ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="seis-label">Description</label>
          <textarea name="description" class="seis-input" rows="3"
                    placeholder="Optional course description…"><?= sanitize($_POST['description'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="seis-card-footer">
        <button type="submit" class="btn-seis"><i class="bi bi-floppy"></i> Save Course</button>
        <a href="<?= BASE_URL ?>/admin/courses/view.php" class="btn-seis-outline">Cancel</a>
      </div>
    </div>
  </form>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>