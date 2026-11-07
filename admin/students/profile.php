<?php
$pageTitle = 'Student Profile';
$activeNav = 'students_list';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) {
    flash('danger', 'Invalid student ID.');
    redirect(BASE_URL . '/admin/students/view.php');
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$s = $stmt->fetch();

if (!$s) {
    flash('danger', 'Student not found.');
    redirect(BASE_URL . '/admin/students/view.php');
}

$enrollments = $pdo->prepare(
    "SELECT e.id, e.status, e.enrolled_at, e.dropped_at,
            c.course_code, c.course_name, c.units,
            sem.name AS semester_name,
            ay.year_label
     FROM enrollments e
     JOIN courses  c   ON e.course_id   = c.id
     JOIN semesters sem ON e.semester_id = sem.id
     JOIN academic_years ay ON sem.academic_year_id = ay.id
     WHERE e.student_id = ?
     ORDER BY e.enrolled_at DESC"
);
$enrollments->execute([$id]);
$enrollments = $enrollments->fetchAll();
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Student Profile</h1>
    <p class="page-subtitle"><?= sanitize($s['student_no']) ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/students/edit.php?id=<?= $id ?>" class="btn-seis-caramel">
      <i class="bi bi-pencil"></i> Edit
    </a>
    <a href="<?= BASE_URL ?>/admin/enrollment/enroll.php?student_id=<?= $id ?>" class="btn-seis">
      <i class="bi bi-journal-plus"></i> Enroll
    </a>
    <a href="<?= BASE_URL ?>/admin/students/view.php" class="btn-seis-outline">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>
</div>

<div class="profile-layout">

  <!-- Left column -->
  <div>
    <div class="seis-card" style="overflow:visible;">
      <div class="profile-banner">
        <?php if (!empty($s['photo']) && $s['photo'] !== 'default.png'): ?>
          <img src="<?= BASE_URL ?>/assets/img/uploads/<?= sanitize($s['photo']) ?>"
               class="profile-photo-lg" alt="">
        <?php else: ?>
          <div class="avatar-initials-lg"><?= initials($s['first_name'], $s['last_name']) ?></div>
        <?php endif; ?>
        <h2><?= sanitize($s['first_name'] . ' ' . ($s['middle_name'] ? $s['middle_name'][0].'. ' : '') . $s['last_name']) ?></h2>
        <div class="profile-student-no"><?= sanitize($s['student_no']) ?></div>
      </div>
      <div class="profile-body-card">
        <div class="info-row">
          <div class="info-row-label">Status</div>
          <div class="info-row-value"><?= statusBadge($s['status']) ?></div>
        </div>
        <div class="info-row">
          <div class="info-row-label">Gender</div>
          <div class="info-row-value"><?= sanitize($s['gender'] ?? '—') ?></div>
        </div>
        <div class="info-row">
          <div class="info-row-label">Birthdate</div>
          <div class="info-row-value"><?= formatDate($s['birthdate'] ?? null) ?></div>
        </div>
        <div class="info-row">
          <div class="info-row-label">Email</div>
          <div class="info-row-value" style="word-break:break-all;"><?= sanitize($s['email']) ?></div>
        </div>
        <div class="info-row">
          <div class="info-row-label">Phone</div>
          <div class="info-row-value"><?= sanitize($s['phone'] ?? '—') ?></div>
        </div>
        <div class="info-row">
          <div class="info-row-label">Address</div>
          <div class="info-row-value"><?= sanitize($s['address'] ?? '—') ?></div>
        </div>
        <div class="info-row">
          <div class="info-row-label">Added</div>
          <div class="info-row-value"><?= formatDate($s['created_at']) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: Enrollment history -->
  <div>
    <div class="seis-card">
      <div class="seis-card-header">
        <i class="bi bi-journal-text"></i> Enrollment History
        <span style="margin-left:.5rem;font-size:.8rem;font-weight:400;opacity:.75;"><?= count($enrollments) ?> record<?= count($enrollments) !== 1 ? 's' : '' ?></span>
        <div class="card-header-actions">
          <a href="<?= BASE_URL ?>/admin/enrollment/enroll.php?student_id=<?= $id ?>" class="btn-seis-outline" style="font-size:.75rem;padding:.25rem .7rem;">
            + Enroll
          </a>
        </div>
      </div>
      <div style="overflow-x:auto;">
        <table class="seis-table">
          <thead>
            <tr>
              <th>Course</th>
              <th>Name</th>
              <th>Units</th>
              <th>Year / Semester</th>
              <th>Status</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if ($enrollments): foreach ($enrollments as $e): ?>
              <tr>
                <td><span class="badge-seis badge-brown"><?= sanitize($e['course_code']) ?></span></td>
                <td><?= sanitize($e['course_name']) ?></td>
                <td><?= $e['units'] ?></td>
                <td><?= sanitize($e['year_label'] . ' / ' . $e['semester_name']) ?></td>
                <td><?= statusBadge($e['status']) ?></td>
                <td class="text-muted-sm"><?= formatDate($e['enrolled_at'], 'M j, Y') ?></td>
                <td>
                  <?php if ($e['status'] === 'Enrolled'): ?>
                    <a href="<?= BASE_URL ?>/admin/enrollment/drop.php?id=<?= $e['id'] ?>&ref=profile&student_id=<?= $id ?>"
                       class="btn-seis-danger btn-icon-sm"
                       onclick="return confirm('Drop this enrollment?')" title="Drop">
                      <i class="bi bi-x-lg"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; else: ?>
              <tr>
                <td colspan="7">
                  <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-journal"></i></div>
                    <h3>No Enrollments Yet</h3>
                    <p><a href="<?= BASE_URL ?>/admin/enrollment/enroll.php?student_id=<?= $id ?>">Enroll this student</a> in a course.</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
