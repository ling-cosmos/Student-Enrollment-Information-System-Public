<?php
$pageTitle = 'Enroll Student';
$activeNav = 'enroll';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo = getPDO();

$activeSemester = $pdo->query(
    "SELECT sem.id, sem.name, ay.year_label
     FROM semesters sem
     JOIN academic_years ay ON sem.academic_year_id = ay.id
     WHERE sem.is_active = 1
     LIMIT 1"
)->fetch();

$semesters  = $pdo->query(
    "SELECT sem.id, CONCAT(ay.year_label, ' — ', sem.name) AS label
     FROM semesters sem
     JOIN academic_years ay ON sem.academic_year_id = ay.id
     ORDER BY ay.year_label DESC, sem.start_date"
)->fetchAll();

$courses = $pdo->query(
    "SELECT id, course_code, course_name, units, capacity FROM courses WHERE is_active = 1 ORDER BY course_code"
)->fetchAll();

$students = $pdo->query(
    "SELECT id, student_no, first_name, last_name FROM students WHERE status = 'Active' ORDER BY last_name, first_name"
)->fetchAll();

$preselect = (int)($_GET['student_id'] ?? 0);
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Enroll Student</h1>
    <p class="page-subtitle">
      <?php if ($activeSemester): ?>
        Active period: <strong><?= sanitize($activeSemester['year_label'] . ' — ' . $activeSemester['name']) ?></strong>
      <?php else: ?>
        No active semester. <a href="<?= BASE_URL ?>/admin/settings/semester.php">Configure one</a>.
      <?php endif; ?>
    </p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/enrollment/view.php" class="btn-seis-outline">
      <i class="bi bi-journal-text"></i> View Enrollments
    </a>
  </div>
</div>

<div style="max-width:680px;">
  <form method="POST" action="<?= BASE_URL ?>/handlers/enrollment_handler.php">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="hidden" name="action"     value="enroll">

    <div class="seis-card">
      <div class="seis-card-header"><i class="bi bi-journal-plus"></i> Enrollment Details</div>
      <div class="seis-card-body">

        <div class="form-group">
          <label class="seis-label">Student <span class="req">*</span></label>
          <select name="student_id" class="seis-select" required>
            <option value="">— Select Student —</option>
            <?php foreach ($students as $st): ?>
              <option value="<?= $st['id'] ?>" <?= $preselect === $st['id'] ? 'selected' : '' ?>>
                <?= sanitize($st['last_name'] . ', ' . $st['first_name'] . ' (' . $st['student_no'] . ')') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="seis-label">Course <span class="req">*</span></label>
          <select name="course_id" class="seis-select" required>
            <option value="">— Select Course —</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= $c['id'] ?>">
                <?= sanitize($c['course_code'] . ' — ' . $c['course_name'] . ' (' . $c['units'] . ' units)') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="seis-label">Semester <span class="req">*</span></label>
          <select name="semester_id" class="seis-select" required>
            <option value="">— Select Semester —</option>
            <?php foreach ($semesters as $sem): ?>
              <option value="<?= $sem['id'] ?>"
                <?= $activeSemester && $activeSemester['id'] == $sem['id'] ? 'selected' : '' ?>>
                <?= sanitize($sem['label']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <p class="form-hint">Only active semester is pre-selected.</p>
        </div>

      </div>
      <div class="seis-card-footer">
        <button type="submit" class="btn-seis">
          <i class="bi bi-journal-check"></i> Confirm Enrollment
        </button>
        <a href="<?= BASE_URL ?>/admin/enrollment/view.php" class="btn-seis-outline">Cancel</a>
      </div>
    </div>
  </form>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>