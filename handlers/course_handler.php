<?php
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
session_start();
requireLogin();
validateCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/courses/view.php');
}

$pdo    = getPDO();
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    try {
        $pdo->prepare(
            "INSERT INTO courses (course_code, course_name, units, capacity, description, is_active)
             VALUES (:code, :name, :units, :cap, :desc, :active)"
        )->execute([
            ':code'   => strtoupper(trim($_POST['course_code'] ?? '')),
            ':name'   => trim($_POST['course_name'] ?? ''),
            ':units'  => (int)($_POST['units'] ?? 3),
            ':cap'    => (int)($_POST['capacity'] ?? 40),
            ':desc'   => trim($_POST['description'] ?? '') ?: null,
            ':active' => (int)($_POST['is_active'] ?? 1),
        ]);
        flash('success', 'Course added successfully.');
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            flash('danger', 'Course code already exists.');
        } else {
            flash('danger', 'Error: ' . $e->getMessage());
        }
    }
    redirect(BASE_URL . '/admin/courses/view.php');
}

if ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { flash('danger','Invalid ID.'); redirect(BASE_URL.'/admin/courses/view.php'); }
    try {
        $pdo->prepare(
            "UPDATE courses SET
                course_code=:code, course_name=:name, units=:units,
                capacity=:cap, description=:desc, is_active=:active
             WHERE id=:id"
        )->execute([
            ':code'   => strtoupper(trim($_POST['course_code'] ?? '')),
            ':name'   => trim($_POST['course_name'] ?? ''),
            ':units'  => (int)($_POST['units'] ?? 3),
            ':cap'    => (int)($_POST['capacity'] ?? 40),
            ':desc'   => trim($_POST['description'] ?? '') ?: null,
            ':active' => (int)($_POST['is_active'] ?? 1),
            ':id'     => $id,
        ]);
        flash('success', 'Course updated successfully.');
    } catch (PDOException $e) {
        flash('danger', 'Error: ' . $e->getMessage());
    }
    redirect(BASE_URL . '/admin/courses/view.php');
}

redirect(BASE_URL . '/admin/courses/view.php');