<?php
$pageTitle = 'Courses Report';
$activeNav = 'rep_courses';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo = getPDO();

$totalCourses  = (int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$activeCourses = (int)$pdo->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetchColumn();
$totalCapacity = (int)$pdo->query("SELECT SUM(capacity) FROM courses WHERE is_active = 1")->fetchColumn();
$totalSeats    = (int)$pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'Enrolled'")->fetchColumn();

$courses = $pdo->query(
    "SELECT c.*,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'Enrolled')  AS enrolled,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'Dropped')   AS dropped,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'Completed') AS completed
     FROM courses c
     ORDER BY enrolled DESC"
)->fetchAll();
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Courses Report</h1>
    <p class="page-subtitle">Capacity and enrollment breakdown per course</p>
  </div>
  <div class="page-header-actions">
    <button onclick="window.print()" class="btn-seis-outline"><i class="bi bi-printer"></i> Print</button>
  </div>
</div>

<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon si-courses"><i class="bi bi-book-fill"></i></div>
    <div class="stat-body"><div class="stat-number"><?= $totalCourses ?></div><div class="stat-label">Total Courses</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-enrolled"><i class="bi bi-check-circle"></i></div>
    <div class="stat-body"><div class="stat-number"><?= $activeCourses ?></div><div class="stat-label">Active</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-students"><i class="bi bi-grid-3x3"></i></div>
    <div class="stat-body"><div class="stat-number"><?= number_format($totalCapacity) ?></div><div class="stat-label">Total Capacity</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-users"><i class="bi bi-journal-check"></i></div>
    <div class="stat-body"><div class="stat-number"><?= number_format($totalSeats) ?></div><div class="stat-label">Seats Filled</div></div>
  </div>
</div>

<div class="seis-card">
  <div class="seis-card-header"><i class="bi bi-book-fill"></i> All Courses &mdash; Capacity &amp; Enrollment</div>
  <div style="overflow-x:auto;">
    <table class="seis-table">
      <thead>
        <tr>
          <th>Code</th>
          <th>Course Name</th>
          <th>Units</th>
          <th>Capacity</th>
          <th>Enrolled</th>
          <th>Fill Rate</th>
          <th>Dropped</th>
          <th>Completed</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($courses as $c):
          $pct    = $c['capacity'] > 0 ? round($c['enrolled'] / $c['capacity'] * 100) : 0;
          $barCls = $pct >= 90 ? 'high' : ($pct >= 65 ? 'mid' : 'low');
        ?>
          <tr>
            <td><span class="badge-seis badge-brown"><?= sanitize($c['course_code']) ?></span></td>
            <td><?= sanitize($c['course_name']) ?></td>
            <td><?= $c['units'] ?></td>
            <td><?= $c['capacity'] ?></td>
            <td class="fw-seis"><?= $c['enrolled'] ?></td>
            <td style="min-width:130px;">
              <div class="fill-bar-wrap">
                <div class="fill-bar-track">
                  <div class="fill-bar-fill <?= $barCls ?>" style="width:<?= min(100,$pct) ?>%;"></div>
                </div>
                <span class="fill-bar-pct"><?= $pct ?>%</span>
              </div>
            </td>
            <td><?= $c['dropped'] ?></td>
            <td><?= $c['completed'] ?></td>
            <td><?= statusBadge($c['is_active'] ? 'active' : 'inactive') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3">Totals</td>
          <td><?= $totalCapacity ?></td>
          <td><?= $totalSeats ?></td>
          <td colspan="4" class="responsive-table-spacer" aria-hidden="true"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
