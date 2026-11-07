<?php
$pageTitle = 'Academic Years';
$activeNav = 'set_years';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireRole('admin');

$pdo   = getPDO();
$years = $pdo->query(
    "SELECT ay.*,
            (SELECT COUNT(*) FROM semesters s WHERE s.academic_year_id = ay.id) AS semester_count,
            (SELECT COUNT(*) FROM enrollments e JOIN semesters s ON e.semester_id = s.id WHERE s.academic_year_id = ay.id) AS enrollment_count
     FROM academic_years ay
     ORDER BY ay.id DESC"
)->fetchAll();
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Academic Years</h1>
    <p class="page-subtitle">Configure academic years and their active status</p>
  </div>
</div>

<div class="settings-layout">

  <!-- Add Form -->
  <div>
    <form method="POST" action="<?= BASE_URL ?>/handlers/settings_handler.php">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="action"     value="add_year">

      <div class="seis-card">
        <div class="seis-card-header"><i class="bi bi-calendar3-plus"></i> Add Academic Year</div>
        <div class="seis-card-body">

          <div class="form-group">
            <label class="seis-label">Year Label <span class="req">*</span></label>
            <input type="text" name="year_label" class="seis-input"
                   placeholder="e.g. 2024-2025" required
                   value="<?= sanitize($_POST['year_label'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="seis-label">Set as Active?</label>
            <select name="is_active" class="seis-select">
              <option value="0">No</option>
              <option value="1">Yes (deactivates others)</option>
            </select>
            <p class="form-hint">Only one year can be active at a time.</p>
          </div>

        </div>
        <div class="seis-card-footer">
          <button type="submit" class="btn-seis"><i class="bi bi-plus-lg"></i> Add Year</button>
        </div>
      </div>
    </form>
  </div>

  <!-- List -->
  <div class="seis-card">
    <div class="seis-card-header"><i class="bi bi-calendar3"></i> All Academic Years</div>
    <div style="overflow-x:auto;">
      <table class="seis-table">
        <thead>
          <tr><th>Year Label</th><th>Semesters</th><th>Enrollments</th><th>Active</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php if ($years): foreach ($years as $y): ?>
            <tr>
              <td class="fw-seis"><?= sanitize($y['year_label']) ?></td>
              <td><?= $y['semester_count'] ?></td>
              <td><?= $y['enrollment_count'] ?></td>
              <td><?= $y['is_active'] ? "<span class='badge-seis badge-active'>Active</span>" : "<span class='badge-seis badge-inactive'>No</span>" ?></td>
              <td>
                <?php if (!$y['is_active']): ?>
                  <form method="POST" action="<?= BASE_URL ?>/handlers/settings_handler.php" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action"  value="set_active_year">
                    <input type="hidden" name="year_id" value="<?= $y['id'] ?>">
                    <button type="submit" class="btn-seis-outline" style="font-size:.78rem;padding:.3rem .8rem;">Set Active</button>
                  </form>
                <?php else: ?>
                  <span class="text-muted-sm">Current</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="5">
              <div class="empty-state" style="padding:1.5rem;">
                <div class="empty-icon"><i class="bi bi-calendar3"></i></div>
                <p>No academic years yet.</p>
              </div>
            </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>