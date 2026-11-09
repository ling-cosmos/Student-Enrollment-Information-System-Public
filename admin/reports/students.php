<?php
$pageTitle = 'Student Report';
$activeNav = 'rep_students';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo = getPDO();

$total      = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$active     = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'")->fetchColumn();
$inactive   = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Inactive'")->fetchColumn();
$graduated  = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Graduated'")->fetchColumn();
$dropped    = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Dropped'")->fetchColumn();
$recentDate = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql' ? "NOW() - INTERVAL '30 days'" : 'DATE_SUB(NOW(), INTERVAL 30 DAY)';
$last30     = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE created_at >= $recentDate")->fetchColumn();

$byGender   = $pdo->query("SELECT gender, COUNT(*) AS cnt FROM students WHERE gender IS NOT NULL GROUP BY gender ORDER BY cnt DESC")->fetchAll();
$byStatus   = $pdo->query("SELECT status, COUNT(*) AS cnt FROM students GROUP BY status ORDER BY cnt DESC")->fetchAll();

$recentList = $pdo->query(
    "SELECT student_no, first_name, last_name, gender, status, created_at
     FROM students ORDER BY created_at DESC LIMIT 20"
)->fetchAll();
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Student Report</h1>
    <p class="page-subtitle">Summary statistics for all registered students</p>
  </div>
  <div class="page-header-actions">
    <button onclick="window.print()" class="btn-seis-outline">
      <i class="bi bi-printer"></i> Print
    </button>
  </div>
</div>

<!-- Summary Stats -->
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon si-students"><i class="bi bi-people-fill"></i></div>
    <div class="stat-body"><div class="stat-number"><?= $total ?></div><div class="stat-label">Total Students</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-enrolled"><i class="bi bi-person-check"></i></div>
    <div class="stat-body"><div class="stat-number"><?= $active ?></div><div class="stat-label">Active</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-courses"><i class="bi bi-mortarboard"></i></div>
    <div class="stat-body"><div class="stat-number"><?= $graduated ?></div><div class="stat-label">Graduated</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon si-users"><i class="bi bi-calendar-plus"></i></div>
    <div class="stat-body"><div class="stat-number"><?= $last30 ?></div><div class="stat-label">Added (30 days)</div></div>
  </div>
</div>

<div class="dashboard-grid">
  <!-- By Status -->
  <div class="seis-card">
    <div class="seis-card-header"><i class="bi bi-pie-chart"></i> By Status</div>
    <div style="overflow-x:auto;">
      <table class="seis-table">
        <thead><tr><th>Status</th><th>Count</th><th>Share</th></tr></thead>
        <tbody>
          <?php foreach ($byStatus as $row):
            $pct = $total > 0 ? round($row['cnt'] / $total * 100, 1) : 0; ?>
            <tr>
              <td><?= statusBadge($row['status']) ?></td>
              <td class="fw-seis"><?= $row['cnt'] ?></td>
              <td>
                <div class="fill-bar-wrap">
                  <div class="fill-bar-track">
                    <div class="fill-bar-fill low" style="width:<?= $pct ?>%;"></div>
                  </div>
                  <span class="fill-bar-pct"><?= $pct ?>%</span>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td>Total</td><td><?= $total ?></td><td>100%</td></tr></tfoot>
      </table>
    </div>
  </div>

  <!-- By Gender -->
  <div class="seis-card">
    <div class="seis-card-header"><i class="bi bi-gender-ambiguous"></i> By Gender</div>
    <div style="overflow-x:auto;">
      <table class="seis-table">
        <thead><tr><th>Gender</th><th>Count</th><th>Share</th></tr></thead>
        <tbody>
          <?php foreach ($byGender as $row):
            $pct = $total > 0 ? round($row['cnt'] / $total * 100, 1) : 0; ?>
            <tr>
              <td><?= sanitize($row['gender'] ?? 'Not set') ?></td>
              <td class="fw-seis"><?= $row['cnt'] ?></td>
              <td>
                <div class="fill-bar-wrap">
                  <div class="fill-bar-track">
                    <div class="fill-bar-fill low" style="width:<?= $pct ?>%;"></div>
                  </div>
                  <span class="fill-bar-pct"><?= $pct ?>%</span>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Recent 20 -->
<div class="seis-card">
  <div class="seis-card-header"><i class="bi bi-clock"></i> 20 Most Recently Added</div>
  <div style="overflow-x:auto;">
    <table class="seis-table">
      <thead><tr><th>Student No.</th><th>Name</th><th>Gender</th><th>Status</th><th>Added On</th></tr></thead>
      <tbody>
        <?php foreach ($recentList as $r): ?>
          <tr>
            <td><a href="<?= BASE_URL ?>/admin/students/profile.php?id=<?= urlencode($r['student_no']) ?>" class="text-brown fw-seis"><?= sanitize($r['student_no']) ?></a></td>
            <td><?= sanitize($r['last_name'] . ', ' . $r['first_name']) ?></td>
            <td><?= sanitize($r['gender'] ?? '—') ?></td>
            <td><?= statusBadge($r['status']) ?></td>
            <td class="text-muted-sm"><?= formatDate($r['created_at'], 'M j, Y') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
