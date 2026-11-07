<?php
// auth/login.php
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

session_start();

if (!empty($_SESSION['user_id'])) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Both username and password are required.';
    } else {
        $pdo  = getPDO();
        $stmt = $pdo->prepare(
            "SELECT id, username, password, full_name, role
             FROM users
             WHERE username = ? AND is_active = 1
             LIMIT 1"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];

            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")
                ->execute([$user['id']]);

            redirect(BASE_URL . '/admin/dashboard.php');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login &mdash; <?= APP_NAME ?></title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Shared design system + Auth CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/core/variables.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/core/base.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/module/auth.css">
</head>
<body class="auth-page">

<div class="login-wrapper">
  <div class="login-card">

    <!-- Header -->
    <div class="login-header">
      <div class="login-logo">
        <i class="bi bi-mortarboard-fill"></i>
      </div>
      <h1><?= APP_NAME ?></h1>
      <p>Sign in to your account to continue</p>
    </div>

    <!-- Body -->
    <div class="login-body">

      <?php if ($error): ?>
        <div class="login-error">
          <i class="bi bi-exclamation-triangle-fill"></i>
          <?= sanitize($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="login-form-group">
          <label class="login-label" for="username">Username</label>
          <div class="login-input-group">
            <span class="login-input-icon"><i class="bi bi-person"></i></span>
            <input type="text" id="username" name="username"
                   placeholder="Enter your username"
                   value="<?= sanitize($_POST['username'] ?? '') ?>"
                   autofocus required>
          </div>
        </div>

        <div class="login-form-group">
          <label class="login-label" for="password">Password</label>
          <div class="login-input-group">
            <span class="login-input-icon"><i class="bi bi-lock"></i></span>
            <input type="password" id="password" name="password"
                   placeholder="Enter your password"
                   required>
          </div>
        </div>

        <button type="submit" class="btn-login">
          <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
      </form>

    </div>

    <!-- Footer -->
    <div class="login-footer">
      &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; v<?= APP_VERSION ?>
    </div>

  </div>
</div>

</body>
</html>
