<?php
// includes/sidebar.php
// Left navigation. Highlight the active menu item automatically.

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

function isActive(string $dir, string $page = ''): string {
    global $currentDir, $currentPage;
    if ($page) {
        return ($currentDir === $dir && $currentPage === $page) ? 'active' : '';
    }
    return $currentDir === $dir ? 'active' : '';
}
?>
<nav class="seis-sidebar" id="seisSidebar" aria-label="Primary navigation">
    <ul class="nav flex-column">

        <li>
            <a href="<?= BASE_URL ?>/admin/dashboard.php"
               class="nav-link <?= isActive('admin','dashboard.php') ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <li><span class="sidebar-group-label">Students</span></li>
        <li>
            <a href="<?= BASE_URL ?>/admin/students/view.php"
               class="nav-link <?= isActive('students','view.php') ?>">
                <i class="bi bi-people"></i> All Students
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/students/add.php"
               class="nav-link <?= isActive('students','add.php') ?>">
                <i class="bi bi-person-plus"></i> Add Student
            </a>
        </li>

        <li><span class="sidebar-group-label">Enrollment</span></li>
        <li>
            <a href="<?= BASE_URL ?>/admin/enrollment/enroll.php"
               class="nav-link <?= isActive('enrollment','enroll.php') ?>">
                <i class="bi bi-journal-plus"></i> Enroll Student
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/enrollment/view.php"
               class="nav-link <?= isActive('enrollment','view.php') ?>">
                <i class="bi bi-journal-text"></i> Current Enrolled
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/enrollment/history.php"
               class="nav-link <?= isActive('enrollment','history.php') ?>">
                <i class="bi bi-clock-history"></i> History
            </a>
        </li>

        <li><span class="sidebar-group-label">Courses</span></li>
        <li>
            <a href="<?= BASE_URL ?>/admin/courses/view.php"
               class="nav-link <?= isActive('courses','view.php') ?>">
                <i class="bi bi-book"></i> All Courses
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/courses/add.php"
               class="nav-link <?= isActive('courses','add.php') ?>">
                <i class="bi bi-book-half"></i> Add Course
            </a>
        </li>

        <li class="sidebar-group">
          <button type="button" class="sidebar-group-toggle" data-sidebar-group="reports" aria-expanded="true">
            <span>Reports</span><i class="bi bi-chevron-down" aria-hidden="true"></i>
          </button>
          <ul class="sidebar-subnav" data-sidebar-panel="reports">
        <li>
            <a href="<?= BASE_URL ?>/admin/reports/students.php"
               class="nav-link <?= isActive('reports','students.php') ?>">
                <i class="bi bi-people-fill"></i> Students
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/reports/enrollment.php"
               class="nav-link <?= isActive('reports','enrollment.php') ?>">
                <i class="bi bi-bar-chart-fill"></i> Enrollment
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/reports/courses.php"
               class="nav-link <?= isActive('reports','courses.php') ?>">
                <i class="bi bi-book-fill"></i> Courses
            </a>
        </li>
          </ul>
        </li>

        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
        <li class="sidebar-group">
          <button type="button" class="sidebar-group-toggle" data-sidebar-group="system" aria-expanded="true">
            <span>System</span><i class="bi bi-chevron-down" aria-hidden="true"></i>
          </button>
          <ul class="sidebar-subnav" data-sidebar-panel="system">
        <li>
            <a href="<?= BASE_URL ?>/admin/users/manage.php"
               class="nav-link <?= isActive('users','manage.php') ?>">
                <i class="bi bi-people"></i> Manage Users
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/users/add.php"
               class="nav-link <?= isActive('users','add.php') ?>">
                <i class="bi bi-person-plus"></i> Add User
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/settings/academic_year.php"
               class="nav-link <?= isActive('settings','academic_year.php') ?>">
                <i class="bi bi-calendar3"></i> Academic Years
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/settings/semester.php"
               class="nav-link <?= isActive('settings','semester.php') ?>">
                <i class="bi bi-calendar-week"></i> Semesters
            </a>
        </li>
          </ul>
        </li>
        <?php endif; ?>

    </ul>
</nav>
