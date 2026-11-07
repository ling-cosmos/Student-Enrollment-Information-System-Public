<?php
$pageTitle = 'Students';
$activeNav = 'students_list';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo    = getPDO();
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$status = $_GET['status'] ?? '';

$where  = [];
$params = [];

if ($search !== '') {
    $where[]  = "(s.student_no LIKE :q OR s.first_name LIKE :q OR s.last_name LIKE :q OR s.email LIKE :q)";
    $params[':q'] = "%{$search}%";
}
if ($status !== '') {
    $where[]  = "s.status = :status";
    $params[':status'] = $status;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM students s $whereSQL");
$countStmt->execute($params);
$pg = paginate((int)$countStmt->fetchColumn(), $page);

$stmt = $pdo->prepare(
    "SELECT s.id, s.student_no, s.first_name, s.last_name, s.email, s.gender, s.status, s.photo
     FROM students s $whereSQL
     ORDER BY s.last_name, s.first_name
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $pg['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pg['offset'],  PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll();

$baseUrl = '?q=' . urlencode($search) . '&status=' . urlencode($status);
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Students</h1>
    <p class="page-subtitle"><?= number_format($pg['total']) ?> total student<?= $pg['total'] !== 1 ? 's' : '' ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/students/add.php" class="btn-seis">
      <i class="bi bi-person-plus"></i> Add Student
    </a>
  </div>
</div>

<div class="seis-card">
  <div class="seis-card-header">
    <i class="bi bi-people"></i> Student List
  </div>
  <div class="seis-card-body">
    <!-- Search & Filter -->
    <form method="GET" class="search-row">
      <div class="search-input-wrap">
        <input type="text" name="q" value="<?= sanitize($search) ?>"
               placeholder="Search by name, student no., or email…"
               class="seis-input search-input">
        <button type="submit" class="btn-seis search-btn">
          <i class="bi bi-search"></i>
        </button>
      </div>
      <select name="status" class="seis-select" style="width:auto;" onchange="this.form.submit()">
        <option value="">All Status</option>
        <?php foreach (['Active','Inactive','Graduated','Dropped'] as $opt): ?>
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
          <th></th>
          <th>Student No.</th>
          <th>Name</th>
          <th>Email</th>
          <th>Gender</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($students): foreach ($students as $s): ?>
          <tr>
            <td style="width:50px;">
              <?php if (!empty($s['photo']) && $s['photo'] !== 'default.png'): ?>
                <img src="<?= BASE_URL ?>/assets/img/uploads/<?= sanitize($s['photo']) ?>"
                     alt="" class="student-avatar">
              <?php else: ?>
                <div class="avatar-initials"><?= initials($s['first_name'], $s['last_name']) ?></div>
              <?php endif; ?>
            </td>
            <td class="fw-seis"><?= sanitize($s['student_no']) ?></td>
            <td>
              <a href="<?= BASE_URL ?>/admin/students/profile.php?id=<?= $s['id'] ?>">
                <?= sanitize($s['last_name'] . ', ' . $s['first_name']) ?>
              </a>
            </td>
            <td class="text-muted-sm"><?= sanitize($s['email']) ?></td>
            <td><?= sanitize($s['gender'] ?? '—') ?></td>
            <td><?= statusBadge($s['status']) ?></td>
            <td>
              <div style="display:flex;gap:.35rem;">
                <a href="<?= BASE_URL ?>/admin/students/profile.php?id=<?= $s['id'] ?>"
                   class="btn-seis-outline btn-icon-sm" title="View Profile">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="<?= BASE_URL ?>/admin/students/edit.php?id=<?= $s['id'] ?>"
                   class="btn-seis-caramel btn-icon-sm" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-people"></i></div>
                <h3>No Students Found</h3>
                <p><?= $search ? 'No results for &ldquo;' . sanitize($search) . '&rdquo;.' : 'Add your first student to get started.' ?></p>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="seis-card-footer">
    <?php renderPagination($pg, $baseUrl); ?>
    <span class="text-muted-sm" style="margin-left:auto;">
      Page <?= $pg['page'] ?> of <?= $pg['totalPages'] ?>
    </span>
  </div>
</div>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>