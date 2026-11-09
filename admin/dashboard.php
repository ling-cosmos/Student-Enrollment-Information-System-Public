<?php
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require_once dirname(__DIR__) . '/includes/header.php';
requireLogin();

$pdo = getPDO();

$semesters = $pdo->query(
    "SELECT sem.id, sem.name, sem.is_active, ay.year_label
     FROM semesters sem JOIN academic_years ay ON ay.id = sem.academic_year_id
     ORDER BY ay.id DESC, sem.start_date DESC"
)->fetchAll();
$requestedSemesterId = (int)($_GET['semester_id'] ?? 0);
$activeSemester = null;
foreach ($semesters as $semester) {
    if (($requestedSemesterId && (int)$semester['id'] === $requestedSemesterId) || (!$requestedSemesterId && (int)$semester['is_active'] === 1)) {
        $activeSemester = $semester;
        break;
    }
}
if (!$activeSemester && $semesters) $activeSemester = $semesters[0];
$selectedSemesterId = (int)($activeSemester['id'] ?? 0);

$totalStudents  = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalCourses   = (int)$pdo->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetchColumn();
$enrollmentCountStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE status = 'Enrolled' AND semester_id = ?");
$enrollmentCountStmt->execute([$selectedSemesterId]);
$totalEnrolled = (int)$enrollmentCountStmt->fetchColumn();
$totalUsers = ($_SESSION['user_role'] ?? '') === 'admin'
    ? (int)$pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn()
    : 0;

$recentStudents = $pdo->query(
    "SELECT id, student_no, first_name, last_name, status, created_at
     FROM students ORDER BY created_at DESC LIMIT 8"
)->fetchAll();

$topCourseStmt = $pdo->prepare(
    "SELECT c.course_code, c.course_name, c.capacity,
            COUNT(e.id) AS enrolled_count
     FROM courses c
     LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'Enrolled' AND e.semester_id = ?
     WHERE c.is_active = 1
     GROUP BY c.id
     ORDER BY enrolled_count DESC
     LIMIT 6"
);
$topCourseStmt->execute([$selectedSemesterId]);
$topCourses = $topCourseStmt->fetchAll();
$capacityWarningStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM (
       SELECT c.id
       FROM courses c
       LEFT JOIN enrollments e ON e.course_id = c.id AND e.status = 'Enrolled' AND e.semester_id = ?
       WHERE c.is_active = 1 AND c.capacity > 0
       GROUP BY c.id, c.capacity
       HAVING COUNT(e.id) * 1.0 / c.capacity >= .9
     ) capacity_alerts"
);
$capacityWarningStmt->execute([$selectedSemesterId]);
$capacityWarnings = (int)$capacityWarningStmt->fetchColumn();

$enrollmentTrend = $pdo->query(
    "SELECT sem.name, ay.year_label, COUNT(e.id) AS total
     FROM semesters sem
     JOIN academic_years ay ON ay.id = sem.academic_year_id
     LEFT JOIN enrollments e ON e.semester_id = sem.id AND e.status = 'Enrolled'
     GROUP BY sem.id, ay.year_label
     ORDER BY sem.start_date DESC
     LIMIT 6"
)->fetchAll();
$enrollmentTrend = array_reverse($enrollmentTrend);
$trendMax = max(array_merge([1], array_map(fn($row) => (int)$row['total'], $enrollmentTrend)));

$canManageUsers = ($_SESSION['user_role'] ?? '') === 'admin';
$activitySources = [
    "SELECT 'student' AS activity_type, CONCAT('Added student ', first_name, ' ', last_name) AS description, created_at AS activity_at FROM students",
    "SELECT 'enrollment', CONCAT(s.first_name, ' ', s.last_name, ' enrolled in ', c.course_code), e.enrolled_at FROM enrollments e JOIN students s ON s.id = e.student_id JOIN courses c ON c.id = e.course_id",
];
if ($canManageUsers) {
    $activitySources[] = "SELECT 'user', CONCAT('Created system account ', username), created_at FROM users";
}
$recentActivity = $pdo->query(
    'SELECT activity_type, description, activity_at FROM (' . implode(' UNION ALL ', $activitySources) . ') activity ORDER BY activity_at DESC LIMIT 6'
)->fetchAll();
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Dashboard</h1>
    <p class="page-subtitle">Welcome back, <?= sanitize($_SESSION['full_name'] ?? 'Administrator') ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/students/add.php" class="btn-seis">
      <i class="bi bi-person-plus"></i> Add Student
    </a>
    <a href="<?= BASE_URL ?>/admin/enrollment/enroll.php" class="btn-seis-caramel">
      <i class="bi bi-journal-plus"></i> Enroll
    </a>
  </div>
</div>

<section class="term-context" aria-label="Dashboard term">
  <div><span class="term-context-label">Reporting term</span><strong><?= $activeSemester ? sanitize($activeSemester['year_label'] . ' · ' . $activeSemester['name']) : 'No semester configured' ?></strong><small><?= $capacityWarnings ? $capacityWarnings . ' course capacity warning' . ($capacityWarnings !== 1 ? 's' : '') : 'All course capacities are within range' ?></small></div>
  <?php if ($semesters): ?><form method="GET" class="term-selector"><label class="visually-hidden" for="semester_id">Semester</label><select class="seis-select" id="semester_id" name="semester_id" onchange="this.form.submit()"><?php foreach ($semesters as $semester): ?><option value="<?= (int)$semester['id'] ?>" <?= (int)$semester['id'] === $selectedSemesterId ? 'selected' : '' ?>><?= sanitize($semester['year_label'] . ' / ' . $semester['name']) ?></option><?php endforeach; ?></select></form><?php else: ?><a class="btn-seis-outline" href="<?= BASE_URL ?>/admin/settings/semester.php">Configure semester</a><?php endif; ?>
</section>

<!-- Stat Cards -->
<div class="stats-row">
  <a class="stat-card stat-card-link" href="<?= BASE_URL ?>/admin/students/view.php">
    <div class="stat-icon si-students"><i class="bi bi-people-fill"></i></div>
    <div class="stat-body">
      <div class="stat-number"><?= number_format($totalStudents) ?></div>
      <div class="stat-label">Total Students</div>
    </div>
  </a>
  <a class="stat-card stat-card-link" href="<?= BASE_URL ?>/admin/courses/view.php">
    <div class="stat-icon si-courses"><i class="bi bi-book-fill"></i></div>
    <div class="stat-body">
      <div class="stat-number"><?= number_format($totalCourses) ?></div>
      <div class="stat-label">Active Courses</div>
    </div>
  </a>
  <a class="stat-card stat-card-link" href="<?= BASE_URL ?>/admin/enrollment/view.php?semester_id=<?= $selectedSemesterId ?>">
    <div class="stat-icon si-enrolled"><i class="bi bi-journal-check"></i></div>
    <div class="stat-body">
      <div class="stat-number"><?= number_format($totalEnrolled) ?></div>
      <div class="stat-label">Current Enrollments</div>
    </div>
  </a>
  <?php if ($canManageUsers): ?><a class="stat-card stat-card-link" href="<?= BASE_URL ?>/admin/users/manage.php">
    <div class="stat-icon si-users"><i class="bi bi-people-fill"></i></div>
    <div class="stat-body">
      <div class="stat-number"><?= number_format($totalUsers) ?></div>
      <div class="stat-label">System Users</div>
    </div>
  </a><?php endif; ?>
</div>

<!-- Dashboard Grid -->
<div class="dashboard-grid">
  <!-- Recent Students -->
  <div class="seis-card">
    <div class="seis-card-header">
      <i class="bi bi-person-lines-fill"></i> Recent Students
      <div class="seis-card-header-actions">
        <a href="<?= BASE_URL ?>/admin/students/view.php" class="btn-seis-outline btn-compact">View All</a>
      </div>
    </div>
    <div style="overflow-x:auto;">
      <table class="seis-table">
        <thead>
          <tr>
            <th>Student No.</th>
            <th>Name</th>
            <th>Status</th>
            <th>Added</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($recentStudents): foreach ($recentStudents as $s): ?>
            <tr>
              <td>
                <a href="<?= BASE_URL ?>/admin/students/profile.php?id=<?= (int)$s['id'] ?>" class="fw-seis text-brown">
                  <?= sanitize($s['student_no']) ?>
                </a>
              </td>
              <td><?= sanitize($s['last_name'] . ', ' . $s['first_name']) ?></td>
              <td><?= statusBadge($s['status']) ?></td>
              <td class="text-muted-sm"><?= formatDate($s['created_at'], 'M j, Y') ?></td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="4">
              <div class="empty-state empty-state-compact">
                <div class="empty-icon"><i class="bi bi-people"></i></div>
                <p>No students yet.</p>
                <a href="<?= BASE_URL ?>/admin/students/add.php" class="btn-seis">
                  <i class="bi bi-person-plus"></i> Add the first student
                </a>
              </div>
            </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top Courses -->
  <div class="seis-card">
    <div class="seis-card-header">
      <i class="bi bi-bar-chart-fill"></i> Enrollment by Course
      <div class="seis-card-header-actions">
        <a href="<?= BASE_URL ?>/admin/reports/courses.php" class="btn-seis-outline btn-compact">Report</a>
      </div>
    </div>
    <div style="overflow-x:auto;">
      <table class="seis-table">
        <thead>
          <tr><th>Code</th><th>Course</th><th>Enrolled / Capacity</th></tr>
        </thead>
        <tbody>
          <?php if ($topCourses): foreach ($topCourses as $c): ?>
            <tr>
              <td><span class="badge-seis badge-brown"><?= sanitize($c['course_code']) ?></span></td>
              <td><?= sanitize($c['course_name']) ?></td>
              <td>
                <?php
                  $pct = $c['capacity'] > 0 ? round($c['enrolled_count'] / $c['capacity'] * 100) : 0;
                  $cls = $pct >= 90 ? 'high' : ($pct >= 65 ? 'mid' : 'low');
                ?>
                <div class="fill-bar-wrap">
                  <div class="fill-bar-track">
                    <div class="fill-bar-fill <?= $cls ?>" style="width:<?= min(100,$pct) ?>%;"></div>
                  </div>
                  <span class="fill-bar-pct"><?= $c['enrolled_count'] ?> / <?= $c['capacity'] ?></span>
                </div>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="3">
              <div class="empty-state empty-state-compact">
                <div class="empty-icon"><i class="bi bi-book"></i></div>
                <p>No course data yet.</p>
              </div>
            </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="dashboard-insights">
  <section class="seis-card">
    <div class="seis-card-header"><i class="bi bi-graph-up-arrow"></i> Enrollment Trend<div class="seis-card-header-actions"><a href="<?= BASE_URL ?>/admin/reports/enrollment.php" class="btn-seis-outline btn-compact">Full report</a></div></div>
    <div class="seis-card-body">
      <?php if ($enrollmentTrend): ?><div class="trend-chart" aria-label="Enrollment totals by semester"><?php foreach ($enrollmentTrend as $trend): ?><div class="trend-row"><span><?= sanitize($trend['year_label'] . ' · ' . $trend['name']) ?></span><div class="trend-track"><span style="width:<?= max(3, round(((int)$trend['total'] / $trendMax) * 100)) ?>%"></span></div><strong><?= (int)$trend['total'] ?></strong></div><?php endforeach; ?></div><?php else: ?><div class="empty-state empty-state-compact"><p>No semester data yet.</p></div><?php endif; ?>
    </div>
  </section>
  <section class="seis-card">
    <div class="seis-card-header"><i class="bi bi-activity"></i> Recent Activity</div>
    <div class="seis-card-body activity-list">
      <?php if ($recentActivity): foreach ($recentActivity as $activity): ?><div class="activity-item"><span class="activity-icon"><i class="bi bi-<?= $activity['activity_type'] === 'student' ? 'person-plus' : ($activity['activity_type'] === 'enrollment' ? 'journal-check' : 'person-gear') ?>"></i></span><div><strong><?= sanitize($activity['description']) ?></strong><small><?= formatDate($activity['activity_at'], 'M j, Y · g:i A') ?></small></div></div><?php endforeach; else: ?><div class="empty-state empty-state-compact"><p>No recent activity yet.</p></div><?php endif; ?>
    </div>
  </section>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
