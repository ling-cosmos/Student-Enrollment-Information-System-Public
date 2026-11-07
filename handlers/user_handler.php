<?php
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
session_start();
requireRole('admin');
validateCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/users/manage.php');
}

$pdo    = getPDO();
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        flash('danger', 'Passwords do not match.');
        redirect(BASE_URL . '/admin/users/add.php');
    }
    if (strlen($password) < 8) {
        flash('danger', 'Password must be at least 8 characters.');
        redirect(BASE_URL . '/admin/users/add.php');
    }

    try {
        $pdo->prepare(
            "INSERT INTO users (username, password, full_name, email, role)
             VALUES (:user, :pass, :name, :email, :role)"
        )->execute([
            ':user'  => trim($_POST['username']  ?? ''),
            ':pass'  => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            ':name'  => trim($_POST['full_name'] ?? ''),
            ':email' => trim($_POST['email']     ?? ''),
            ':role'  => $_POST['role'] ?? 'registrar',
        ]);
        flash('success', 'User created successfully.');
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            flash('danger', 'Username or email already exists.');
        } else {
            flash('danger', 'Error: ' . $e->getMessage());
        }
    }
    redirect(BASE_URL . '/admin/users/manage.php');
}

if ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id && $id !== (int)$_SESSION['user_id']) {
        $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
        flash('success', 'User status updated.');
    }
    redirect(BASE_URL . '/admin/users/manage.php');
}

if ($action === 'bulk_status') {
    $bulkStatus = $_POST['bulk_status'] ?? '';
    $rawIds = $_POST['user_ids'] ?? null;
    if (!in_array($bulkStatus, ['activate', 'deactivate'], true) || !is_array($rawIds)) {
        flash('danger', 'Choose a valid bulk action and account selection.');
        redirect(BASE_URL . '/admin/users/manage.php');
    }
    $ids = [];
    foreach ($rawIds as $rawId) {
        $isPositiveInteger = is_int($rawId)
            ? $rawId > 0
            : is_string($rawId) && preg_match('/^[1-9][0-9]*$/D', $rawId) === 1;
        if (!$isPositiveInteger) {
            flash('danger', 'The account selection is invalid. No accounts were changed.');
            redirect(BASE_URL . '/admin/users/manage.php');
        }
        $ids[] = (int)$rawId;
    }
    $ids = array_values(array_unique($ids));
    $status = $bulkStatus === 'activate' ? 1 : 0;
    $ids = array_values(array_diff($ids, [(int)$_SESSION['user_id']]));

    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id IN ($placeholders)");
        $stmt->execute(array_merge([$status], $ids));
        flash('success', $stmt->rowCount() . ' user account(s) updated.');
    } else {
        flash('warning', 'Select at least one account that is not your current account.');
    }
    redirect(BASE_URL . '/admin/users/manage.php');
}

if ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'viewer';
    $allowedRoles = ['admin', 'registrar', 'viewer'];

    if (!$id || $username === '' || $fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('danger', 'Complete all required fields with a valid email address.');
        redirect(BASE_URL . '/admin/users/edit.php?id=' . $id);
    }
    if (!in_array($role, $allowedRoles, true)) {
        $role = 'viewer';
    }
    try {
        if ($id === (int)$_SESSION['user_id']) {
            $pdo->prepare('UPDATE users SET username = ?, full_name = ?, email = ? WHERE id = ?')
                ->execute([$username, $fullName, $email, $id]);
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $fullName;
        } else {
            $pdo->prepare('UPDATE users SET username = ?, full_name = ?, email = ?, role = ? WHERE id = ?')
                ->execute([$username, $fullName, $email, $role, $id]);
        }
        flash('success', 'User details updated.');
        redirect(BASE_URL . '/admin/users/manage.php');
    } catch (PDOException $e) {
        flash('danger', $e->getCode() === '23000' ? 'Username or email already exists.' : 'Unable to update the user.');
        redirect(BASE_URL . '/admin/users/edit.php?id=' . $id);
    }
}

if ($action === 'reset_password') {
    $id = (int)($_POST['id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$id || $id === (int)$_SESSION['user_id']) {
        flash('warning', 'Use Change Password to update your own account.');
        redirect(BASE_URL . '/admin/users/manage.php');
    }
    if ($password !== $confirm) {
        flash('danger', 'Passwords do not match.');
        redirect(BASE_URL . '/admin/users/reset_password.php?id=' . $id);
    }
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        flash('danger', 'Use at least 8 characters with uppercase, lowercase, and a number.');
        redirect(BASE_URL . '/admin/users/reset_password.php?id=' . $id);
    }

    $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
        ->execute([password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $id]);
    flash('success', 'Password reset successfully.');
    redirect(BASE_URL . '/admin/users/manage.php');
}

redirect(BASE_URL . '/admin/users/manage.php');
