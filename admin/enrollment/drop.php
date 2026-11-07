<?php
// admin/enrollment/drop.php — processes a drop action then redirects
require_once dirname(dirname(__DIR__)) . '/includes/header.php';
requireLogin();

$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);
$ref = $_GET['ref'] ?? '';   // 'profile' or blank

if (!$id) {
    flash('danger', 'Invalid enrollment ID.');
    redirect(BASE_URL . '/admin/enrollment/view.php');
}

$stmt = $pdo->prepare("SELECT id, student_id, status FROM enrollments WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$enroll = $stmt->fetch();

if (!$enroll) {
    flash('danger', 'Enrollment not found.');
    redirect(BASE_URL . '/admin/enrollment/view.php');
}

if ($enroll['status'] !== 'Enrolled') {
    flash('warning', 'This enrollment cannot be dropped (status: ' . $enroll['status'] . ').');
    redirect(BASE_URL . '/admin/enrollment/view.php');
}

$pdo->prepare(
    "UPDATE enrollments SET status = 'Dropped', dropped_at = NOW() WHERE id = ?"
)->execute([$id]);

flash('success', 'Enrollment dropped successfully.');

if ($ref === 'profile') {
    redirect(BASE_URL . '/admin/students/profile.php?id=' . (int)($_GET['student_id'] ?? 0));
}
redirect(BASE_URL . '/admin/enrollment/view.php');