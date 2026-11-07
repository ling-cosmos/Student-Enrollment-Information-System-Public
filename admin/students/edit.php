<?php
$pageTitle = 'Edit Student';
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
$student = $stmt->fetch();

if (!$student) {
    flash('danger', 'Student not found.');
    redirect(BASE_URL . '/admin/students/view.php');
}
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Edit Student</h1>
    <p class="page-subtitle"><?= sanitize($student['student_no']) ?> &mdash; <?= sanitize($student['first_name'] . ' ' . $student['last_name']) ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/students/profile.php?id=<?= $id ?>" class="btn-seis-outline">
      <i class="bi bi-eye"></i> View Profile
    </a>
    <a href="<?= BASE_URL ?>/admin/students/view.php" class="btn-seis-outline">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>
</div>

<form method="POST" action="<?= BASE_URL ?>/handlers/student_handler.php" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
  <input type="hidden" name="action"     value="edit">
  <input type="hidden" name="id"         value="<?= $id ?>">

  <div class="student-form-layout">
    <div>
      <div class="seis-card">
        <div class="seis-card-header"><i class="bi bi-person-badge"></i> Personal Information</div>
        <div class="seis-card-body">
          <div class="form-group">
            <label class="seis-label">Student Number <span class="req">*</span></label>
            <input type="text" name="student_no" class="seis-input"
                   value="<?= sanitize($student['student_no']) ?>" required>
          </div>
          <div class="form-grid-3">
            <div class="form-group">
              <label class="seis-label">First Name <span class="req">*</span></label>
              <input type="text" name="first_name" class="seis-input"
                     value="<?= sanitize($student['first_name']) ?>" required>
            </div>
            <div class="form-group">
              <label class="seis-label">Middle Name</label>
              <input type="text" name="middle_name" class="seis-input"
                     value="<?= sanitize($student['middle_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="seis-label">Last Name <span class="req">*</span></label>
              <input type="text" name="last_name" class="seis-input"
                     value="<?= sanitize($student['last_name']) ?>" required>
            </div>
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label class="seis-label">Email <span class="req">*</span></label>
              <input type="email" name="email" class="seis-input"
                     value="<?= sanitize($student['email']) ?>" required>
            </div>
            <div class="form-group">
              <label class="seis-label">Phone</label>
              <input type="text" name="phone" class="seis-input"
                     value="<?= sanitize($student['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="seis-label">Gender</label>
              <select name="gender" class="seis-select">
                <option value="">Select gender</option>
                <?php foreach (['Male','Female','Other'] as $g): ?>
                  <option value="<?= $g ?>" <?= $student['gender'] === $g ? 'selected' : '' ?>><?= $g ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="seis-label">Birthdate</label>
              <input type="date" name="birthdate" class="seis-input"
                     value="<?= sanitize($student['birthdate'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="seis-label">Status</label>
              <select name="status" class="seis-select">
                <?php foreach (['Active','Inactive','Dropped','Graduated'] as $st): ?>
                  <option value="<?= $st ?>" <?= $student['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="seis-label">Address</label>
            <textarea name="address" class="seis-input" rows="3"><?= sanitize($student['address'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="seis-card">
        <div class="seis-card-header"><i class="bi bi-image"></i> Photo</div>
        <div class="seis-card-body" style="text-align:center;">
          <?php if (!empty($student['photo']) && $student['photo'] !== 'default.png'): ?>
            <img id="photoPreview" src="<?= BASE_URL ?>/assets/img/uploads/<?= sanitize($student['photo']) ?>"
                 class="profile-photo-preview" style="margin:0 auto 1rem;">
          <?php else: ?>
            <div id="photoPreview" class="photo-placeholder" style="margin:0 auto 1rem;">
              <i class="bi bi-person-bounding-box"></i>
            </div>
          <?php endif; ?>
          <input type="file" name="photo" class="seis-input"
                 accept="image/jpeg,image/png,image/webp"
                 onchange="previewPhoto(this)">
          <p class="form-hint">Leave blank to keep current photo.</p>
        </div>
      </div>

      <div class="seis-card">
        <div class="seis-card-body">
          <button type="submit" class="btn-seis" style="width:100%;justify-content:center;">
            <i class="bi bi-floppy"></i> Save Changes
          </button>
          <a href="<?= BASE_URL ?>/admin/students/profile.php?id=<?= $id ?>"
             class="btn-seis-outline" style="width:100%;justify-content:center;margin-top:.5rem;">
            Cancel
          </a>
        </div>
      </div>
    </div>
  </div>
</form>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
