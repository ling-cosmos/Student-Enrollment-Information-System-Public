<?php
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
requireRole('admin');
validateCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/settings/academic_year.php');
}

$pdo    = getPDO();
$action = $_POST['action'] ?? '';

// ── Add academic year ──
if ($action === 'add_year') {
    $label    = trim($_POST['year_label'] ?? '');
    $isActive = (int)($_POST['is_active'] ?? 0);

    if ($label === '') {
        flash('danger', 'Year label is required.');
        redirect(BASE_URL . '/admin/settings/academic_year.php');
    }

    try {
        if ($isActive) {
            $pdo->query("UPDATE academic_years SET is_active = 0");
        }
        $pdo->prepare("INSERT INTO academic_years (year_label, is_active) VALUES (?, ?)")
            ->execute([$label, $isActive]);
        flash('success', "Academic year '{$label}' added.");
    } catch (PDOException $e) {
        if (in_array($e->getCode(), ['23000', '23505'], true)) {
            flash('danger', 'That year label already exists.');
        } else {
            flash('danger', 'Error: ' . $e->getMessage());
        }
    }
    redirect(BASE_URL . '/admin/settings/academic_year.php');
}

// ── Set active year ──
if ($action === 'set_active_year') {
    $yearId = (int)($_POST['year_id'] ?? 0);
    if ($yearId) {
        $pdo->query("UPDATE academic_years SET is_active = 0");
        $pdo->prepare("UPDATE academic_years SET is_active = 1 WHERE id = ?")->execute([$yearId]);
        flash('success', 'Active academic year updated.');
    }
    redirect(BASE_URL . '/admin/settings/academic_year.php');
}

// ── Add semester ──
if ($action === 'add_semester') {
    $yearId   = (int)($_POST['academic_year_id'] ?? 0);
    $name     = trim($_POST['name'] ?? '');
    $start    = trim($_POST['start_date'] ?? '');
    $end      = trim($_POST['end_date']   ?? '');
    $isActive = (int)($_POST['is_active'] ?? 0);

    if (!$yearId || $name === '' || $start === '' || $end === '') {
        flash('danger', 'All semester fields are required.');
        redirect(BASE_URL . '/admin/settings/semester.php');
    }

    try {
        if ($isActive) {
            $pdo->query("UPDATE semesters SET is_active = 0");
        }
        $pdo->prepare(
            "INSERT INTO semesters (academic_year_id, name, start_date, end_date, is_active)
             VALUES (?, ?, ?, ?, ?)"
        )->execute([$yearId, $name, $start, $end, $isActive]);
        flash('success', "Semester '{$name}' added.");
    } catch (PDOException $e) {
        flash('danger', 'Error: ' . $e->getMessage());
    }
    redirect(BASE_URL . '/admin/settings/semester.php');
}

// ── Set active semester ──
if ($action === 'set_active_semester') {
    $semId = (int)($_POST['semester_id'] ?? 0);
    if ($semId) {
        $pdo->query("UPDATE semesters SET is_active = 0");
        $pdo->prepare("UPDATE semesters SET is_active = 1 WHERE id = ?")->execute([$semId]);
        flash('success', 'Active semester updated.');
    }
    redirect(BASE_URL . '/admin/settings/semester.php');
}

redirect(BASE_URL . '/admin/settings/academic_year.php');