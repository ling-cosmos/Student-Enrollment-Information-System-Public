<?php
// includes/functions.php
// Global helper functions. Loaded by header.php automatically.

// ── Session must already be started before calling these ──

/**
 * Sanitize user input to prevent XSS.
 */
function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to another URL and stop execution.
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Store a flash message in the session.
 * Usage: flash('success', 'Student added successfully!');
 */
function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Display and clear the flash message.
 */
function showFlash(): void {
    if (!empty($_SESSION['flash'])) {
        $type    = $_SESSION['flash']['type'];   // success | danger | warning | info
        $message = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);

        $icons = ['success' => 'check-circle-fill', 'danger' => 'exclamation-octagon-fill', 'warning' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'];
        $icon = $icons[$type] ?? $icons['info'];
        echo "<div class='alert alert-{$type} alert-dismissible fade show seis-toast' role='status'>
                <i class='bi bi-{$icon}' aria-hidden='true'></i><span>" . sanitize($message) . "</span>
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}

/**
 * Check if the current user is logged in. Redirect if not.
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        flash('warning', 'Please log in to continue.');
        redirect(BASE_URL . '/auth/login.php');
    }

    $stmt = getPDO()->prepare('SELECT username, full_name, role, is_active FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $account = $stmt->fetch();
    if (!$account || !(int)$account['is_active']) {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['full_name'], $_SESSION['user_role']);
        flash('warning', 'Your account is inactive or no longer exists.');
        redirect(BASE_URL . '/auth/login.php');
    }
    $_SESSION['username'] = $account['username'];
    $_SESSION['full_name'] = $account['full_name'];
    $_SESSION['user_role'] = $account['role'];
}

/**
 * Check if logged-in user has the required role.
 * Roles: admin > registrar > viewer
 */
function requireRole(string $role): void {
    requireLogin();
    $hierarchy = ['viewer' => 1, 'registrar' => 2, 'admin' => 3];
    $userRole  = $_SESSION['user_role'] ?? 'viewer';

    if (($hierarchy[$userRole] ?? 0) < ($hierarchy[$role] ?? 99)) {
        flash('danger', 'You do not have permission to access that page.');
        redirect(BASE_URL . '/admin/dashboard.php');
    }
}

/**
 * Generate a CSRF token and store it in the session.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate the submitted CSRF token.
 */
function validateCsrf(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';
    if (!is_string($submitted) || !is_string($stored) || $submitted === '' || $stored === '' || !hash_equals($stored, $submitted)) {
        flash('danger', 'Invalid form submission. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
    }
}

/**
 * Return the number of enrolled students in a course for a given semester.
 */
function getEnrollmentCount(int $courseId, int $semesterId): int {
    $pdo  = getPDO();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM enrollments
         WHERE course_id = ? AND semester_id = ? AND status = 'Enrolled'"
    );
    $stmt->execute([$courseId, $semesterId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Format a date for display.
 */
function formatDate(?string $date, string $format = 'F j, Y'): string {
    if (!$date) return '—';
    try { return (new DateTime($date))->format($format); }
    catch (Exception $e) { return $date; }
}

/**
 * Return full name in "Last, First Middle" format.
 */
function fullName(array $student): string {
    $middle = !empty($student['middle_name']) ? ' ' . $student['middle_name'] : '';
    return sanitize($student['last_name'] . ', ' . $student['first_name'] . $middle);
}

/**
 * Return two-letter initials for avatar fallback.
 */
function initials(string $first, string $last): string {
    return strtoupper(substr($first, 0, 1) . substr($last, 0, 1));
}

/**
 * Emit a badge-seis span for a given status string.
 */
function statusBadge(string $status): string {
    $s   = strtolower($status);
    $map = [
        'active'    => 'active',
        'inactive'  => 'inactive',
        'enrolled'  => 'enrolled',
        'dropped'   => 'dropped',
        'completed' => 'completed',
        'graduated' => 'graduated',
    ];
    $cls = $map[$s] ?? 'brown';
    return "<span class='badge-seis badge-{$cls}'>" . ucfirst($s) . "</span>";
}

/**
 * Calculate pagination values.
 */
function paginate(int $total, int $page, int $perPage = ROWS_PER_PAGE): array {
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = max(1, min($page, $totalPages));
    $offset     = ($page - 1) * $perPage;
    return compact('total', 'totalPages', 'page', 'offset', 'perPage');
}

/**
 * Render pagination links.
 */
function renderPagination(array $pg, string $baseUrl): void {
    if ($pg['totalPages'] <= 1) return;
    $c = $pg['page'];
    echo "<ul class='seis-pagination'>";
    // Prev
    if ($c > 1) {
        echo "<li class='pg-item'><a href='{$baseUrl}&page=" . ($c - 1) . "'><i class='bi bi-chevron-left'></i></a></li>";
    } else {
        echo "<li class='pg-item disabled'><span><i class='bi bi-chevron-left'></i></span></li>";
    }
    $start = max(1, $c - 2);
    $end   = min($pg['totalPages'], $c + 2);
    if ($start > 1) echo "<li class='pg-item'><a href='{$baseUrl}&page=1'>1</a></li>";
    if ($start > 2) echo "<li class='pg-item disabled'><span>&hellip;</span></li>";
    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $c ? ' active' : '';
        echo "<li class='pg-item{$active}'><a href='{$baseUrl}&page={$i}'>{$i}</a></li>";
    }
    if ($end < $pg['totalPages'] - 1) echo "<li class='pg-item disabled'><span>&hellip;</span></li>";
    if ($end < $pg['totalPages'])     echo "<li class='pg-item'><a href='{$baseUrl}&page={$pg['totalPages']}'>{$pg['totalPages']}</a></li>";
    // Next
    if ($c < $pg['totalPages']) {
        echo "<li class='pg-item'><a href='{$baseUrl}&page=" . ($c + 1) . "'><i class='bi bi-chevron-right'></i></a></li>";
    } else {
        echo "<li class='pg-item disabled'><span><i class='bi bi-chevron-right'></i></span></li>";
    }
    echo "</ul>";
}
