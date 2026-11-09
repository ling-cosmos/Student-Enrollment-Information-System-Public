<?php
declare(strict_types=1);
ini_set('display_errors', '0');
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
$root = dirname(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (PHP_SAPI === 'cli-server' && str_starts_with($path, '/assets/')) {
    $asset = realpath($root . $path);
    if ($asset && str_starts_with($asset, $root . '/assets/') && is_file($asset)) return false;
}
$relative = ltrim($path, '/') ?: 'index.php';
$allowed = ['index.php'];
foreach (['admin','auth','handlers'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') $allowed[] = substr($file->getPathname(), strlen($root) + 1);
    }
}
if (!in_array($relative, $allowed, true)) { http_response_code(404); echo 'Page not found.'; exit; }
if (getenv('PORTFOLIO_DEMO') !== 'true') { http_response_code(503); echo 'Demo hosting is not configured.'; exit; }
try {
    require_once $root . '/hosting/demo.php';
    session_set_cookie_params(['secure' => getenv('VERCEL') === '1' || !empty($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Lax', 'lifetime' => 21600]);
    session_set_save_handler(new DemoSessions(), true);
    session_start();
    if (empty($_SESSION['user_id'])) {
        $user = demoDatabase()->query("SELECT * FROM users WHERE username='demo'")->fetch();
        $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name']; $_SESSION['user_role'] = $user['role'];
    }
    require $root . '/' . $relative;
} catch (Throwable $error) {
    error_log('SEIS demo: ' . $error->getMessage());
    http_response_code(503);
    echo 'Student Enrollment could not start its demo session. Please try again shortly.';
}
