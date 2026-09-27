<?php
session_start();
include '../config.php';

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['child_id'])) {
    header('Location: admin_children.php');
    exit();
}

// CSRF
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    header('Location: admin_children.php?assign=error');
    exit();
}

$child_id      = filter_input(INPUT_POST, 'child_id', FILTER_VALIDATE_INT);
$caregiver_ids = $_POST['caregiver_ids'] ?? [];

if (!$child_id || $child_id < 1 || !is_array($caregiver_ids)) {
    header('Location: admin_children.php?assign=error');
    exit();
}

// Sanitize caregiver IDs
$caregiver_ids = array_values(array_unique(array_filter(
    array_map('intval', $caregiver_ids),
    fn($id) => $id > 0
)));

try {
    $conn->beginTransaction();

    // Confirm child exists
    $chk = $conn->prepare("SELECT child_id FROM child WHERE child_id = ?");
    $chk->execute([$child_id]);
    if (!$chk->fetch()) {
        $conn->rollBack();
        header('Location: admin_children.php?assign=error');
        exit();
    }

    // Keep only caregiver IDs that actually exist
    if (!empty($caregiver_ids)) {
        $in = implode(',', array_fill(0, count($caregiver_ids), '?'));
        $valid = $conn->prepare("SELECT caregiver_id FROM caregivers WHERE caregiver_id IN ($in)");
        $valid->execute($caregiver_ids);
        $caregiver_ids = array_column($valid->fetchAll(PDO::FETCH_ASSOC), 'caregiver_id');
    }

    // Delete old + insert new
    $del = $conn->prepare("DELETE FROM child_caregiver WHERE child_id = ?");
    $del->execute([$child_id]);

    if (!empty($caregiver_ids)) {
        $ins = $conn->prepare("INSERT INTO child_caregiver (child_id, caregiver_id) VALUES (?, ?)");
        foreach ($caregiver_ids as $cid) {
            $ins->execute([$child_id, $cid]);
        }
    }

    $conn->commit();
    header('Location: admin_children.php?assign=ok');
    exit();

} catch (PDOException $e) {
    $conn->rollBack();
    header('Location: admin_children.php?assign=error');
    exit();
}
?>