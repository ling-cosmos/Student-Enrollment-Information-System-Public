<?php
$pageTitle = 'Current Enrollments';
$activeNav = 'enroll_view';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo    = getPDO();
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$semesterId = max(0, (int)($_GET['semester_id'] ?? 0));
$semesters = $pdo->query(
    "SELECT sem.id, sem.name, ay.year_label
     FROM semesters sem JOIN academic_years ay ON ay.id = sem.academic_year_id
     ORDER BY ay.id DESC, sem.start_date DESC"
)->fetchAll();

$where  = ["e.status = 'Enrolled'"];
$params = [];
if ($semesterId) {
    $where[] = 'e.semester_id = :semester_id';
    $params[':semester_id'] = $semesterId;
}
if ($search !== '') {
    $where[] = "(s.student_no LIKE :q_student_no OR s.first_name LIKE :q_first_name OR s.last_name LIKE :q_last_name OR c.course_code LIKE :q_course_code)";
    $searchPattern = "%{$search}%";
    $params[':q_student_no'] = $searchPattern;
    $params[':q_first_name'] = $searchPattern;
    $params[':q_last_name'] = $searchPattern;
    $params[':q_course_code'] = $searchPattern;
}
$whereSQL = 'WHERE ' . implode(' AND ', $where);

$countStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM enrollments e
     JOIN students s ON e.student_id = s.id
     JOIN courses  c ON e.course_id  = c.id
     $whereSQL"
);
$countStmt->execute($params);
$pg = paginate((int)$countStmt->fetchColumn(), $page);

$stmt = $pdo->prepare(
    "SELECT e.id, e.enrolled_at,
            s.id AS student_db_id, s.student_no, s.first_name, s.last_name,
            c.course_code, c.course_name, c.units,
            sem.name AS semester_name, ay.year_label
     FROM enrollments e
     JOIN students s  ON e.student_id  = s.id
     JOIN courses  c  ON e.course_id   = c.id
     JOIN semesters sem ON e.semester_id = sem.id
     JOIN academic_years ay ON sem.academic_year_id = ay.id
     $whereSQL
     ORDER BY e.enrolled_at DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $k => $v) $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
$stmt->bindValue(':limit',  $pg['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pg['offset'],  PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();
$baseUrl = '?q=' . urlencode($search) . '&semester_id=' . $semesterId;
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Current Enrollments</h1>
    <p class="page-subtitle"><?= number_format($pg['total']) ?> active enrollment<?= $pg['total'] !== 1 ? 's' : '' ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/enrollment/enroll.php" class="btn-seis">
      <i class="bi bi-journal-plus"></i> New Enrollment
    </a>
    <a href="<?= BASE_URL ?>/admin/enrollment/history.php" class="btn-seis-outline">
      <i class="bi bi-clock-history"></i> History
    </a>
  </div>
</div>

<div class="seis-card">
  <div class="seis-card-header"><i class="bi bi-journal-text"></i> Enrolled Students</div>
  <div class="seis-card-body">
    <form method="GET" class="search-row">
      <div class="search-input-wrap">
        <input type="text" name="q" value="<?= sanitize($search) ?>"
               placeholder="Search student name, no., or course code…"
               class="seis-input search-input">
        <button type="submit" class="btn-seis search-btn"><i class="bi bi-search"></i></button>
      </div>
      <select name="semester_id" class="seis-select enrollment-term-filter" aria-label="Filter by semester" onchange="this.form.submit()">
        <option value="0">All semesters</option>
        <?php foreach ($semesters as $semester): ?><option value="<?= (int)$semester['id'] ?>" <?= (int)$semester['id'] === $semesterId ? 'selected' : '' ?>><?= sanitize($semester['year_label'] . ' / ' . $semester['name']) ?></option><?php endforeach; ?>
      </select>
      <?php if ($search || $semesterId): ?>
        <a href="?" class="btn-seis-outline"><i class="bi bi-x"></i> Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <div style="overflow-x:auto;">
    <table class="seis-table">
      <thead>
        <tr>
          <th>Student No.</th>
          <th>Student Name</th>
          <th>Course</th>
          <th>Units</th>
          <th>Period</th>
          <th>Enrolled On</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($rows): foreach ($rows as $r): ?>
          <tr>
            <td>
              <a href="<?= BASE_URL ?>/admin/students/profile.php?id=<?= $r['student_db_id'] ?>" class="fw-seis text-brown">
                <?= sanitize($r['student_no']) ?>
              </a>
            </td>
            <td><?= sanitize($r['last_name'] . ', ' . $r['first_name']) ?></td>
            <td>
              <span class="badge-seis badge-brown"><?= sanitize($r['course_code']) ?></span>
              <span class="text-muted-sm" style="margin-left:.4rem;"><?= sanitize($r['course_name']) ?></span>
            </td>
            <td><?= $r['units'] ?></td>
            <td class="text-muted-sm"><?= sanitize($r['year_label'] . ' / ' . $r['semester_name']) ?></td>
            <td class="text-muted-sm"><?= formatDate($r['enrolled_at'], 'M j, Y') ?></td>
            <td>
              <a href="<?= BASE_URL ?>/admin/enrollment/drop.php?id=<?= $r['id'] ?>"
                 class="btn-seis-danger btn-icon-sm"
                 onclick="return confirm('Drop this enrollment?')" title="Drop">
                <i class="bi bi-x-lg"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="7">
            <div class="empty-state">
              <div class="empty-icon"><i class="bi bi-journal"></i></div>
              <h3>No Enrollments Found</h3>
              <p><?= $search ? 'Try a different search term.' : 'No students are currently enrolled.' ?></p>
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
