<?php
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
requireLogin();
validateCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/enrollment/view.php');
}

$pdo    = getPDO();
$action = $_POST['action'] ?? '';

if ($action === 'enroll') {
    $studentId  = (int)($_POST['student_id']  ?? 0);
    $courseId   = (int)($_POST['course_id']   ?? 0);
    $semesterId = (int)($_POST['semester_id'] ?? 0);

    if (!$studentId || !$courseId || !$semesterId) {
        flash('danger', 'Please select a student, course, and semester.');
        redirect(BASE_URL . '/admin/enrollment/enroll.php');
    }

    // Check capacity
    $cap = $pdo->prepare(
        "SELECT c.capacity,
                (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.semester_id = ? AND e.status = 'Enrolled') AS current
         FROM courses c WHERE c.id = ?"
    );
    $cap->execute([$semesterId, $courseId]);
    $capRow = $cap->fetch();
    if ($capRow && $capRow['current'] >= $capRow['capacity']) {
        flash('danger', 'This course is already at full capacity for the selected semester.');
        redirect(BASE_URL . '/admin/enrollment/enroll.php');
    }

    // Check duplicate
    $dup = $pdo->prepare(
        "SELECT id FROM enrollments WHERE student_id=? AND course_id=? AND semester_id=? AND status='Enrolled'"
    );
    $dup->execute([$studentId, $courseId, $semesterId]);
    if ($dup->fetch()) {
        flash('warning', 'This student is already enrolled in that course for the selected semester.');
        redirect(BASE_URL . '/admin/enrollment/enroll.php');
    }

    try {
        $pdo->prepare(
            "INSERT INTO enrollments (student_id, course_id, semester_id, status)
             VALUES (?, ?, ?, 'Enrolled')"
        )->execute([$studentId, $courseId, $semesterId]);

        flash('success', 'Enrollment added successfully.');
        redirect(BASE_URL . '/admin/enrollment/view.php');
    } catch (PDOException $e) {
        flash('danger', 'Error: ' . $e->getMessage());
        redirect(BASE_URL . '/admin/enrollment/enroll.php');
    }
}

redirect(BASE_URL . '/admin/enrollment/view.php');