<?php
$pageTitle = 'Courses';
$activeNav = 'courses_list';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo    = getPDO();
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));

$where  = [];
$params = [];
if ($search !== '') {
    $where[]      = "(course_code LIKE :q OR course_name LIKE :q)";
    $params[':q'] = "%{$search}%";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM courses $whereSQL");
$countStmt->execute($params);
$pg = paginate((int)$countStmt->fetchColumn(), $page);

$stmt = $pdo->prepare(
    "SELECT c.*,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'Enrolled') AS enrolled_count
     FROM courses c $whereSQL
     ORDER BY c.course_code
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $pg['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pg['offset'],  PDO::PARAM_INT);
$stmt->execute();
$courses = $stmt->fetchAll();
$baseUrl = '?q=' . urlencode($search);
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Courses</h1>
    <p class="page-subtitle"><?= number_format($pg['total']) ?> course<?= $pg['total'] !== 1 ? 's' : '' ?> in total</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/courses/add.php" class="btn-seis">
      <i class="bi bi-plus-lg"></i> Add Course
    </a>
  </div>
</div>

<div class="seis-card">
  <div class="seis-card-header"><i class="bi bi-book"></i> Course List</div>
  <div class="seis-card-body">
    <form method="GET" class="search-row">
      <div class="search-input-wrap">
        <input type="text" name="q" value="<?= sanitize($search) ?>"
               placeholder="Search by code or name…"
               class="seis-input search-input">
        <button type="submit" class="btn-seis search-btn"><i class="bi bi-search"></i></button>
      </div>
      <?php if ($search): ?>
        <a href="?" class="btn-seis-outline"><i class="bi bi-x"></i> Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <div style="overflow-x:auto;">
    <table class="seis-table">
      <thead>
        <tr>
          <th>Course Code</th>
          <th>Course Name</th>
          <th>Units</th>
          <th>Capacity</th>
          <th>Enrolled</th>
          <th>Fill Rate</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($courses): foreach ($courses as $c):
          $pct = $c['capacity'] > 0 ? round($c['enrolled_count'] / $c['capacity'] * 100) : 0;
          $barCls = $pct >= 90 ? 'high' : ($pct >= 65 ? 'mid' : 'low');
        ?>
          <tr>
            <td><span class="badge-seis badge-brown fw-seis"><?= sanitize($c['course_code']) ?></span></td>
            <td><?= sanitize($c['course_name']) ?></td>
            <td><?= $c['units'] ?></td>
            <td><?= $c['capacity'] ?></td>
            <td><?= $c['enrolled_count'] ?></td>
            <td style="min-width:120px;">
              <div class="fill-bar-wrap">
                <div class="fill-bar-track">
                  <div class="fill-bar-fill <?= $barCls ?>" style="width:<?= min(100,$pct) ?>%;"></div>
                </div>
                <span class="fill-bar-pct"><?= $pct ?>%</span>
              </div>
            </td>
            <td><?= statusBadge($c['is_active'] ? 'active' : 'inactive') ?></td>
            <td>
              <a href="<?= BASE_URL ?>/admin/courses/edit.php?id=<?= $c['id'] ?>"
                 class="btn-seis-caramel btn-icon-sm" title="Edit">
                <i class="bi bi-pencil"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="8">
            <div class="empty-state">
              <div class="empty-icon"><i class="bi bi-book"></i></div>
              <h3>No Courses Found</h3>
              <p><?= $search ? 'Try a different search term.' : 'Add your first course.' ?></p>
            </div>
          </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="seis-card-footer">
    <?php renderPagination($pg, $baseUrl); ?>
    <span class="text-muted-sm" style="margin-left:auto;">Page <?= $pg['page'] ?> of <?= $pg['totalPages'] ?></span>
  </div>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>