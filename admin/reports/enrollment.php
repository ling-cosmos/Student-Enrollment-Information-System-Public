<?php
$pageTitle = 'Enrollment Report';
$activeNav = 'rep_enrollment';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo = getPDO();

$totalEnroll  = (int)$pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$enrolled     = (int)$pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'Enrolled'")->fetchColumn();
$dropped      = (int)$pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'Dropped'")->fetchColumn();
$completed    = (int)$pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'Completed'")->fetchColumn();

$bySemester = $pdo->query(
    "SELECT ay.year_label, sem.name AS semester_name,
            COUNT(e.id) AS total,
            SUM(CASE WHEN e.status = 'Enrolled' THEN 1 ELSE 0 END) AS enrolled,
            SUM(CASE WHEN e.status = 'Dropped' THEN 1 ELSE 0 END) AS dropped,
            SUM(CASE WHEN e.status = 'Completed' THEN 1 ELSE 0 END) AS completed
     FROM enrollments e
     JOIN semesters sem ON e.semester_id = sem.id
     JOIN academic_years ay ON sem.academic_year_id = ay.id
     GROUP BY e.semester_id, ay.year_label, sem.name, sem.start_date
     ORDER BY ay.year_label DESC, sem.start_date"
)->fetchAll();
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Enrollment Report</h1>
    <p class="page-subtitle">Enrollment breakdown by period and status</p>
  </div>
  <div class="page-header-actions">
    <button onclick="window.print()" class="btn-seis-outline"><i class="bi bi-printer"></i> Print</button>
  </div>
</div>

<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon si-courses"><i class="bi bi-journal-bookmark"></i></div>
    <div class="stat-body"><div class="stat-number"><?= $totalEnroll ?></div><div class="stat-label">All Records</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-enrolled"><i class="bi bi-journal-check"></i></div>
    <div class="stat-body"><div class="stat-number"><?= $enrolled ?></div><div class="stat-label">Currently Enrolled</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-users"><i class="bi bi-journal-x"></i></div>
    <div class="stat-body"><div class="stat-number"><?= $dropped ?></div><div class="stat-label">Dropped</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-students"><i class="bi bi-journal-check"></i></div>
    <div class="stat-body"><div class="stat-number"><?= $completed ?></div><div class="stat-label">Completed</div></div>
  </div>
</div>

<div class="seis-card">
  <div class="seis-card-header"><i class="bi bi-bar-chart-fill"></i> By Academic Year &amp; Semester</div>
  <div style="overflow-x:auto;">
    <table class="seis-table">
      <thead>
        <tr>
          <th>Academic Year</th>
          <th>Semester</th>
          <th>Total</th>
          <th>Enrolled</th>
          <th>Dropped</th>
          <th>Completed</th>
          <th>Drop Rate</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($bySemester): foreach ($bySemester as $row):
          $dropRate = $row['total'] > 0 ? round($row['dropped'] / $row['total'] * 100, 1) : 0;
          $dropCls  = $dropRate >= 30 ? 'high' : ($dropRate >= 15 ? 'mid' : 'low');
        ?>
          <tr>
            <td class="fw-seis"><?= sanitize($row['year_label']) ?></td>
            <td><?= sanitize($row['semester_name']) ?></td>
            <td><?= $row['total'] ?></td>
            <td><?= statusBadge('Enrolled') ?> <?= $row['enrolled'] ?></td>
            <td><?= statusBadge('Dropped')  ?> <?= $row['dropped'] ?></td>
            <td><?= $row['completed'] ?></td>
            <td>
              <div class="fill-bar-wrap">
                <div class="fill-bar-track">
                  <div class="fill-bar-fill <?= $dropCls ?>" style="width:<?= $dropRate ?>%;"></div>
                </div>
                <span class="fill-bar-pct"><?= $dropRate ?>%</span>
              </div>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="7">
            <div class="empty-state" style="padding:1.5rem;">
              <div class="empty-icon"><i class="bi bi-bar-chart"></i></div>
              <p>No enrollment data yet.</p>
            </div>
          </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
