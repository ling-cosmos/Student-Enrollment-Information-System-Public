<?php
$pageTitle = 'Edit Course';
$activeNav = 'courses_list';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) {
    flash('danger', 'Invalid course ID.');
    redirect(BASE_URL . '/admin/courses/view.php');
}

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
    flash('danger', 'Course not found.');
    redirect(BASE_URL . '/admin/courses/view.php');
}
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Edit Course</h1>
    <p class="page-subtitle"><?= sanitize($c['course_code'] . ' — ' . $c['course_name']) ?></p>
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
    <input type="hidden" name="action"     value="edit">
    <input type="hidden" name="id"         value="<?= $id ?>">

    <div class="seis-card">
      <div class="seis-card-header"><i class="bi bi-pencil-square"></i> Edit Course Details</div>
      <div class="seis-card-body">

        <div class="form-grid-2">
          <div class="form-group">
            <label class="seis-label">Course Code <span class="req">*</span></label>
            <input type="text" name="course_code" class="seis-input"
                   value="<?= sanitize($c['course_code']) ?>" required>
          </div>
          <div class="form-group">
            <label class="seis-label">Units <span class="req">*</span></label>
            <input type="number" name="units" class="seis-input"
                   min="1" max="9" value="<?= $c['units'] ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label class="seis-label">Course Name <span class="req">*</span></label>
          <input type="text" name="course_name" class="seis-input"
                 value="<?= sanitize($c['course_name']) ?>" required>
        </div>

        <div class="form-grid-2">
          <div class="form-group">
            <label class="seis-label">Capacity</label>
            <input type="number" name="capacity" class="seis-input"
                   min="1" value="<?= $c['capacity'] ?>">
          </div>
          <div class="form-group">
            <label class="seis-label">Status</label>
            <select name="is_active" class="seis-select">
              <option value="1" <?= $c['is_active'] ? 'selected' : '' ?>>Active</option>
              <option value="0" <?= !$c['is_active'] ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="seis-label">Description</label>
          <textarea name="description" class="seis-input" rows="3"><?= sanitize($c['description'] ?? '') ?></textarea>
        </div>

      </div>
      <div class="seis-card-footer">
        <button type="submit" class="btn-seis"><i class="bi bi-floppy"></i> Save Changes</button>
        <a href="<?= BASE_URL ?>/admin/courses/view.php" class="btn-seis-outline">Cancel</a>
      </div>
    </div>
  </form>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>