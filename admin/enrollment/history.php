<?php
$pageTitle = 'Enrollment History';
$activeNav = 'enroll_hist';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo    = getPDO();
$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));

$where  = [];
$params = [];
if ($search !== '') {
    $where[]      = "(s.student_no LIKE :q OR s.first_name LIKE :q OR s.last_name LIKE :q OR c.course_code LIKE :q)";
    $params[':q'] = "%{$search}%";
}
if (in_array($status, ['Enrolled','Dropped','Completed'])) {
    $where[]          = "e.status = :status";
    $params[':status'] = $status;
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM enrollments e
     JOIN students s ON e.student_id = s.id
     JOIN courses  c ON e.course_id  = c.id
     $whereSQL"
);
$countStmt->execute($params);
$pg = paginate((int)$countStmt->fetchColumn(), $page);

$stmt = $pdo->prepare(
    "SELECT e.id, e.status, e.enrolled_at, e.dropped_at,
            s.id AS student_db_id, s.student_no, s.first_name, s.last_name,
            c.course_code, c.course_name,
            sem.name AS semester_name, ay.year_label
     FROM enrollments e
     JOIN students s ON e.student_id = s.id
     JOIN courses  c ON e.course_id  = c.id
     JOIN semesters sem ON e.semester_id = sem.id
     JOIN academic_years ay ON sem.academic_year_id = ay.id
     $whereSQL
     ORDER BY e.enrolled_at DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $pg['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pg['offset'],  PDO::PARAM_INT);
$stmt->execute();
$rows    = $stmt->fetchAll();
$baseUrl = '?q=' . urlencode($search) . '&status=' . urlencode($status);
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Enrollment History</h1>
    <p class="page-subtitle">All-time enrollment records &mdash; <?= number_format($pg['total']) ?> total</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/enrollment/enroll.php" class="btn-seis">
      <i class="bi bi-journal-plus"></i> New Enrollment
    </a>
  </div>
</div>

<div class="seis-card">
  <div class="seis-card-header"><i class="bi bi-clock-history"></i> All Enrollment Records</div>
  <div class="seis-card-body">
    <form method="GET" class="search-row">
      <div class="search-input-wrap">
        <input type="text" name="q" value="<?= sanitize($search) ?>"
               placeholder="Search name, student no., or course…"
               class="seis-input search-input">
        <button type="submit" class="btn-seis search-btn"><i class="bi bi-search"></i></button>
      </div>
      <select name="status" class="seis-select" style="width:auto;" onchange="this.form.submit()">
        <option value="">All Status</option>
        <?php foreach (['Enrolled','Dropped','Completed'] as $opt): ?>
          <option value="<?= $opt ?>" <?= $status === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
      <?php if ($search || $status): ?>
        <a href="?" class="btn-seis-outline"><i class="bi bi-x"></i> Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <div style="overflow-x:auto;">
    <table class="seis-table">
      <thead>
        <tr>
          <th>Student</th>
          <th>Course</th>
          <th>Period</th>
          <th>Status</th>
          <th>Enrolled On</th>
          <th>Dropped On</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
          <tr>
            <td>
              <a href="<?= BASE_URL ?>/admin/students/profile.php?id=<?= $r['student_db_id'] ?>" class="fw-seis text-brown">
                <?= sanitize($r['student_no']) ?>
              </a>
              <div class="text-muted-sm"><?= sanitize($r['last_name'] . ', ' . $r['first_name']) ?></div>
            </td>
            <td>
              <span class="badge-seis badge-brown"><?= sanitize($r['course_code']) ?></span>
              <div class="text-muted-sm"><?= sanitize($r['course_name']) ?></div>
            </td>
            <td class="text-muted-sm"><?= sanitize($r['year_label'] . ' / ' . $r['semester_name']) ?></td>
            <td><?= statusBadge($r['status']) ?></td>
            <td class="text-muted-sm"><?= formatDate($r['enrolled_at'], 'M j, Y') ?></td>
            <td class="text-muted-sm"><?= $r['dropped_at'] ? formatDate($r['dropped_at'], 'M j, Y') : '—' ?></td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6">
            <div class="empty-state">
              <div class="empty-icon"><i class="bi bi-clock-history"></i></div>
              <h3>No Records Found</h3>
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