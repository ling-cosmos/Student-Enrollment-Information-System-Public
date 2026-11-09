<?php
// includes/header.php
// Included at the very top of every page.
// Set $moduleCSS (array) before including to load page-specific module CSS.
// e.g. $moduleCSS = ['students'];  — loads assets/css/module/students.css

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$pageTitle = $pageTitle ?? APP_NAME;
$requestPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (!isset($moduleCSS)) {
    $moduleRoutes = [
        '#/admin/dashboard\.php$#' => 'dashboard',
        '#/admin/students/#'       => 'students',
        '#/admin/enrollment/#'     => 'enrollment',
        '#/admin/courses/#'        => 'courses',
        '#/admin/reports/#'        => 'reports',
        '#/admin/settings/#'       => 'settings',
        '#/admin/users/#'          => 'users',
    ];
    $moduleCSS = [];
    foreach ($moduleRoutes as $routePattern => $moduleName) {
        if (preg_match($routePattern, $requestPath)) {
            $moduleCSS[] = $moduleName;
            break;
        }
    }
}
if (str_contains($requestPath, '/admin/') || str_ends_with($requestPath, '/auth/change_password.php')) {
    requireLogin();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> &mdash; SEIS</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- SEIS Core CSS (loaded on all admin pages) -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/core/variables.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/core/base.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/core/layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/core/components.css">

    <?php foreach ($moduleCSS as $mod): ?>
    <!-- Module CSS: <?= sanitize($mod) ?> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/module/<?= sanitize($mod) ?>.css">
    <?php endforeach; ?>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-dark seis-topbar">
    <button type="button"
            class="mobile-nav-toggle"
            aria-controls="seisSidebar"
            aria-expanded="false"
            aria-label="Open navigation">
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>
    <button type="button" class="desktop-sidebar-toggle" aria-label="Collapse sidebar" aria-expanded="true" aria-controls="seisSidebar">
        <i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>
    </button>
    <a class="navbar-brand" href="<?= BASE_URL ?>/admin/dashboard.php">
        <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="brand-name-full"><?= APP_NAME ?></span>
        <span class="brand-name-short">SEIS</span>
    </a>
    <?php if (!empty($_SESSION['user_id'])): ?>
    <div class="topbar-user ms-auto me-2">
        <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?></div>
        <span class="topbar-user-name"><?= sanitize($_SESSION['full_name'] ?? 'User') ?></span>
        <span class="topbar-role"><?= sanitize($_SESSION['user_role'] ?? '') ?></span>
    </div>
    <a href="<?= BASE_URL ?>/auth/change_password.php" class="btn-topbar me-1" title="Change Password">
        <i class="bi bi-key"></i>
    </a>
    <a href="<?= BASE_URL ?>/auth/logout.php" class="btn-topbar" aria-label="Logout">
        <i class="bi bi-box-arrow-right" aria-hidden="true"></i> <span class="topbar-action-label">Logout</span>
    </a>
    <?php endif; ?>
</nav>

<!-- Body layout -->
<div class="seis-body-wrap">
<?php require_once dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="seis-main">
<div class="flash-area">
<?php if (getenv('PORTFOLIO_DEMO') === 'true'): ?><div class="alert alert-info">Original SEIS app · Your own sample-data sandbox. Sessions expire after six hours. Enter fictional information only. Photo uploads are disabled. <a href="https://ling-cosmos.github.io/Portfolio/#projects">Back to portfolio ↗</a></div><?php endif; ?>
<?php showFlash(); ?>
</div>
