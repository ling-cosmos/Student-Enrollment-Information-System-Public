<?php
$pageTitle = 'Add Student';
$activeNav = 'students_add';
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();
?>

<div class="page-header">
  <div class="page-header-left">
    <h1>Add New Student</h1>
    <p class="page-subtitle">Fill in all required fields marked with *</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= BASE_URL ?>/admin/students/view.php" class="btn-seis-outline">
      <i class="bi bi-arrow-left"></i> Back to List
    </a>
  </div>
</div>

<form method="POST" action="<?= BASE_URL ?>/handlers/student_handler.php" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
  <input type="hidden" name="action" value="add">

  <div class="student-form-layout">

    <!-- Left: Main Info -->
    <div>
      <div class="seis-card">
        <div class="seis-card-header"><i class="bi bi-person-badge"></i> Personal Information</div>
        <div class="seis-card-body">
          <div class="form-group">
            <label class="seis-label">Student Number <span class="req">*</span></label>
            <input type="text" name="student_no" class="seis-input"
                   placeholder="e.g. 2024-0001" required
                   value="<?= sanitize($_POST['student_no'] ?? '') ?>">
          </div>
          <div class="form-grid-3">
            <div class="form-group">
              <label class="seis-label">First Name <span class="req">*</span></label>
              <input type="text" name="first_name" class="seis-input"
                     placeholder="First name" required
                     value="<?= sanitize($_POST['first_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="seis-label">Middle Name</label>
              <input type="text" name="middle_name" class="seis-input"
                     placeholder="Middle name"
                     value="<?= sanitize($_POST['middle_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="seis-label">Last Name <span class="req">*</span></label>
              <input type="text" name="last_name" class="seis-input"
                     placeholder="Last name" required
                     value="<?= sanitize($_POST['last_name'] ?? '') ?>">
            </div>
          </div>
          <div class="form-grid-2">
            <div class="form-group">
              <label class="seis-label">Email <span class="req">*</span></label>
              <input type="email" name="email" class="seis-input"
                     placeholder="student@school.edu" required
                     value="<?= sanitize($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="seis-label">Phone</label>
              <input type="text" name="phone" class="seis-input"
                     placeholder="+63 9XX XXX XXXX"
                     value="<?= sanitize($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="seis-label">Gender</label>
              <select name="gender" class="seis-select">
                <option value="">Select gender</option>
                <?php foreach (['Male','Female','Other'] as $g): ?>
                  <option value="<?= $g ?>" <?= ($_POST['gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="seis-label">Birthdate</label>
              <input type="date" name="birthdate" class="seis-input"
                     value="<?= sanitize($_POST['birthdate'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="seis-label">Address</label>
            <textarea name="address" class="seis-input" rows="3"
                      placeholder="Full address…"><?= sanitize($_POST['address'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label class="seis-label">Status</label>
            <select name="status" class="seis-select">
              <?php foreach (['Active','Inactive','Dropped','Graduated'] as $st): ?>
                <option value="<?= $st ?>" <?= ($_POST['status'] ?? 'Active') === $st ? 'selected' : '' ?>><?= $st ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Photo -->
    <div>
      <div class="seis-card">
        <div class="seis-card-header"><i class="bi bi-image"></i> Student Photo</div>
        <div class="seis-card-body" style="text-align:center;">
          <div id="photoPreview" class="photo-placeholder" style="margin:0 auto 1rem;">
            <i class="bi bi-person-bounding-box"></i>
          </div>
          <input type="file" name="photo" id="photoInput" class="seis-input"
                 accept="image/jpeg,image/png,image/webp"
                 onchange="previewPhoto(this)">
          <p class="form-hint">JPG, PNG, WebP · Max 2 MB</p>
        </div>
      </div>

      <div class="seis-card">
        <div class="seis-card-body">
          <button type="submit" class="btn-seis" style="width:100%;justify-content:center;">
            <i class="bi bi-floppy"></i> Save Student
          </button>
          <a href="<?= BASE_URL ?>/admin/students/view.php"
             class="btn-seis-outline" style="width:100%;justify-content:center;margin-top:.5rem;">
            Cancel
          </a>
        </div>
      </div>
    </div>
  </div>
</form>

<?php require_once dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
