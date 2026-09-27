<?php
session_start();
include_once "../config.php";

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['child_id'])) {
    header('Location: ../admin_children.php?child=error');
    exit();
}

// CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    header('Location: ../admin_children.php?child=error');
    exit();
}

// Collect
$child_id        = filter_input(INPUT_POST, 'child_id', FILTER_VALIDATE_INT);
$name            = trim($_POST['name'] ?? '');
$date_of_birth   = $_POST['date_of_birth'] ?? '';
$gender          = $_POST['gender'] ?? '';
$medical_history = trim($_POST['medical_history'] ?? '');
$special_needs   = trim($_POST['special_needs'] ?? '');

// Validate
$errors = [];

if (!$child_id || $child_id < 1) {
    $errors[] = 'Invalid child ID.';
}
if ($name === '' || mb_strlen($name) > 100) {
    $errors[] = 'Invalid name.';
}
if (!in_array($gender, ['Male', 'Female', 'Other'], true)) {
    $errors[] = 'Invalid gender.';
}

$d = DateTime::createFromFormat('Y-m-d', $date_of_birth);
if (!$d || $d->format('Y-m-d') !== $date_of_birth) {
    $errors[] = 'Invalid date of birth.';
} elseif ($d > new DateTime()) {
    $errors[] = 'Date of birth cannot be in the future.';
}

if (mb_strlen($medical_history) > 1000) {
    $errors[] = 'Medical history too long.';
}
if (mb_strlen($special_needs) > 1000) {
    $errors[] = 'Special needs too long.';
}

if (!empty($errors)) {
    header('Location: ../admin_children.php?child=error');
    exit();
}

// Confirm child exists
$chk = $conn->prepare("SELECT child_id FROM child WHERE child_id = ?");
$chk->execute([$child_id]);
if (!$chk->fetch()) {
    header('Location: ../admin_children.php?child=error');
    exit();
}

// Update
try {
    $sql = "UPDATE child SET 
            name = :name, 
            date_of_birth = :date_of_birth, 
            gender = :gender, 
            medical_history = :medical_history, 
            special_needs = :special_needs 
            WHERE child_id = :child_id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':name'           => $name,
        ':date_of_birth'  => $date_of_birth,
        ':gender'         => $gender,
        ':medical_history'=> $medical_history,
        ':special_needs'  => $special_needs,
        ':child_id'       => $child_id,
    ]);

    header("Location: ../admin_children.php?child=updated");
    exit();

} catch (PDOException $e) {
    header("Location: ../admin_children.php?child=error");
    exit();
}
?>