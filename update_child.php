<?php
session_start();
require_once 'config.php';

$targetChildId = $_GET['child_id'] ?? null;
$targetField = $_GET['field'] ?? null;

if (!$targetChildId || !$targetField) {
    die("Error: Invalid system request.");
}

$stmt = $conn->prepare("SELECT * FROM child WHERE child_id = :id");
$stmt->execute([':id' => $targetChildId]);
$childRecord = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$childRecord) {
    die("Error: Child record not located.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($targetField === 'name') {
        $newName = trim($_POST['name']);

        $updateStmt = $conn->prepare("UPDATE child SET name = :name WHERE child_id = :id");
        $updateStmt->execute([
            ':name' => $newName, 
            ':id' => $targetChildId
        ]);

        header("Location: parent_profile.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Record</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7f6; display: flex; justify-content: center; padding-top: 50px; }
        .update-panel { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .form-control { width: 100%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 4px; }
        .btn-save { background: #28a745; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; }
        .btn-save:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="update-panel">
        <h3 style="text-align: center; color: #333; margin-top: 0;">Update Child Details</h3>
        <form method="POST">
            <?php if ($targetField === 'name'): ?>
                <label for="name" style="font-weight: bold; color: #555;">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($childRecord['name']); ?>" required>
                <button type="submit" class="btn-save">Save Changes</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>