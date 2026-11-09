<?php
// handlers/student_handler.php — POST only
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
requireLogin();
validateCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/students/view.php');
}

$pdo    = getPDO();
$action = $_POST['action'] ?? '';

// ── Photo upload helper ──
function handlePhotoUpload(): ?string {
    if (getenv('PORTFOLIO_DEMO') === 'true') return null;
    if (empty($_FILES['photo']['name'])) return null;
    $file = $_FILES['photo'];
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if (!in_array($file['type'], ALLOWED_IMG_TYPES)) {
        flash('warning', 'Invalid image type. Only JPG, PNG, WebP allowed.');
        return null;
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        flash('warning', 'Image too large (max 2 MB).');
        return null;
    }
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('photo_', true) . '.' . strtolower($ext);
    move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $name);
    return $name;
}

// ── Add student ──
if ($action === 'add') {
    $photo     = handlePhotoUpload() ?? 'default.png';
    $studentNo = trim($_POST['student_no'] ?? '');

    if ($studentNo === '') {
        flash('danger', 'Student number is required.');
        redirect(BASE_URL . '/admin/students/add.php');
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO students
                (student_no, first_name, middle_name, last_name, email, phone, birthdate, gender, address, photo, status)
             VALUES
                (:student_no, :first_name, :middle_name, :last_name, :email, :phone, :birthdate, :gender, :address, :photo, :status)"
        );
        $stmt->execute([
            ':student_no'  => $studentNo,
            ':first_name'  => trim($_POST['first_name']  ?? ''),
            ':middle_name' => trim($_POST['middle_name'] ?? '') ?: null,
            ':last_name'   => trim($_POST['last_name']   ?? ''),
            ':email'       => trim($_POST['email']       ?? ''),
            ':phone'       => trim($_POST['phone']       ?? '') ?: null,
            ':birthdate'   => trim($_POST['birthdate']   ?? '') ?: null,
            ':gender'      => trim($_POST['gender'] ?? '') ?: null,
            ':address'     => trim($_POST['address'] ?? '') ?: null,
            ':photo'       => $photo,
            ':status'      => $_POST['status'] ?? 'Active',
        ]);
        $newId = $pdo->lastInsertId();
        flash('success', 'Student added successfully.');
        redirect(BASE_URL . '/admin/students/profile.php?id=' . $newId);
    } catch (PDOException $e) {
        if (in_array($e->getCode(), ['23000', '23505'], true)) {
            flash('danger', 'Student number or email already exists.');
        } else {
            flash('danger', 'Database error: ' . $e->getMessage());
        }
        redirect(BASE_URL . '/admin/students/add.php');
    }
}

// ── Edit student ──
if ($action === 'edit') {
    $id        = (int)($_POST['id'] ?? 0);
    $photoFile = handlePhotoUpload();

    if (!$id) {
        flash('danger', 'Invalid student ID.');
        redirect(BASE_URL . '/admin/students/view.php');
    }

    $params = [
        ':student_no'  => trim($_POST['student_no']  ?? ''),
        ':first_name'  => trim($_POST['first_name']  ?? ''),
        ':middle_name' => trim($_POST['middle_name'] ?? '') ?: null,
        ':last_name'   => trim($_POST['last_name']   ?? ''),
        ':email'       => trim($_POST['email']       ?? ''),
        ':phone'       => trim($_POST['phone']       ?? '') ?: null,
        ':birthdate'   => trim($_POST['birthdate']   ?? '') ?: null,
        ':gender'      => trim($_POST['gender'] ?? '') ?: null,
        ':address'     => trim($_POST['address'] ?? '') ?: null,
        ':status'      => $_POST['status'] ?? 'Active',
        ':id'          => $id,
    ];

    $photoSQL = '';
    if ($photoFile) {
        $photoSQL = ', photo = :photo';
        $params[':photo'] = $photoFile;
    }

    try {
        $pdo->prepare(
            "UPDATE students SET
                student_no=:student_no, first_name=:first_name, middle_name=:middle_name,
                last_name=:last_name, email=:email, phone=:phone, birthdate=:birthdate,
                gender=:gender, address=:address, status=:status $photoSQL
             WHERE id=:id"
        )->execute($params);

        flash('success', 'Student updated successfully.');
        redirect(BASE_URL . '/admin/students/profile.php?id=' . $id);
    } catch (PDOException $e) {
        flash('danger', 'Error: ' . $e->getMessage());
        redirect(BASE_URL . '/admin/students/edit.php?id=' . $id);
    }
}

redirect(BASE_URL . '/admin/students/view.php');
