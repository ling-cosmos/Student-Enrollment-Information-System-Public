<?php
$pageTitle = 'Semesters';
$activeNav = 'set_semesters';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireRole('admin');

$pdo   = getPDO();
$years = $pdo->query("SELECT id, year_label FROM academic_years ORDER BY id DESC")->fetchAll();
$sems  = $pdo->query(
    "SELECT sem.*,
            ay.year_label,
            (SELECT COUNT(*) FROM enrollments e WHERE e.semester_id = sem.id) AS enrollment_count
     FROM semesters sem
     JOIN academic_years ay ON sem.academic_year_id = ay.id
     ORDER BY ay.id DESC, sem.start_date"
)->fetchAll();
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Semesters</h1>
    <p class="page-subtitle">Define semesters for each academic year</p>
  </div>
</div>

<div class="settings-layout">

  <!-- Add Form -->
  <div>
    <form method="POST" action="<?= BASE_URL ?>/handlers/settings_handler.php">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="action"     value="add_semester">

      <div class="seis-card">
        <div class="seis-card-header"><i class="bi bi-calendar-week-fill"></i> Add Semester</div>
        <div class="seis-card-body">

          <div class="form-group">
            <label class="seis-label">Academic Year <span class="req">*</span></label>
            <select name="academic_year_id" class="seis-select" required>
              <option value="">— Select Year —</option>
              <?php foreach ($years as $y): ?>
                <option value="<?= $y['id'] ?>"><?= sanitize($y['year_label']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="seis-label">Semester Name <span class="req">*</span></label>
            <input type="text" name="name" class="seis-input"
                   placeholder="e.g. 1st Semester" required
                   value="<?= sanitize($_POST['name'] ?? '') ?>">
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="seis-label">Start Date <span class="req">*</span></label>
              <input type="date" name="start_date" class="seis-input" required
                     value="<?= sanitize($_POST['start_date'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="seis-label">End Date <span class="req">*</span></label>
              <input type="date" name="end_date" class="seis-input" required
                     value="<?= sanitize($_POST['end_date'] ?? '') ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="seis-label">Set as Active?</label>
            <select name="is_active" class="seis-select">
              <option value="0">No</option>
              <option value="1">Yes (deactivates others)</option>
            </select>
          </div>

        </div>
        <div class="seis-card-footer">
          <button type="submit" class="btn-seis"><i class="bi bi-plus-lg"></i> Add Semester</button>
        </div>
      </div>
    </form>
  </div>

  <!-- List -->
  <div class="seis-card">
    <div class="seis-card-header"><i class="bi bi-calendar-week"></i> All Semesters</div>
    <div style="overflow-x:auto;">
      <table class="seis-table">
        <thead>
          <tr><th>Year</th><th>Semester</th><th>Start</th><th>End</th><th>Enrollments</th><th>Active</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php if ($sems): foreach ($sems as $sem): ?>
            <tr>
              <td class="text-muted-sm"><?= sanitize($sem['year_label']) ?></td>
              <td class="fw-seis"><?= sanitize($sem['name']) ?></td>
              <td class="text-muted-sm"><?= formatDate($sem['start_date'], 'M j, Y') ?></td>
              <td class="text-muted-sm"><?= formatDate($sem['end_date'], 'M j, Y') ?></td>
              <td><?= $sem['enrollment_count'] ?></td>
              <td><?= $sem['is_active'] ? "<span class='badge-seis badge-active'>Active</span>" : "<span class='badge-seis badge-inactive'>No</span>" ?></td>
              <td>
                <?php if (!$sem['is_active']): ?>
                  <form method="POST" action="<?= BASE_URL ?>/handlers/settings_handler.php" style="display:inline;">
                    <input type="hidden" name="csrf_token"   value="<?= csrfToken() ?>">
                    <input type="hidden" name="action"       value="set_active_semester">
                    <input type="hidden" name="semester_id"  value="<?= $sem['id'] ?>">
                    <button type="submit" class="btn-seis-outline" style="font-size:.78rem;padding:.3rem .8rem;">Set Active</button>
                  </form>
                <?php else: ?>
                  <span class="text-muted-sm">Current</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="7">
              <div class="empty-state" style="padding:1.5rem;">
                <div class="empty-icon"><i class="bi bi-calendar-week"></i></div>
                <p>No semesters configured yet.</p>
              </div>
            </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>