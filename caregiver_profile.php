<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['caregiver_id'])) {
    header('Location: caregiver_login.php');
    exit();
}

$targetProfileId = (!empty($_POST['caregiver_id']) && isset($_POST['caregiver_fetch'])) 
    ? filter_var($_POST['caregiver_id'], FILTER_SANITIZE_NUMBER_INT) 
    : $_SESSION['caregiver_id'];

$profileQuery = $conn->prepare("SELECT * FROM caregivers WHERE caregiver_id = :id");
$profileQuery->execute([':id' => $targetProfileId]);
$staffData = $profileQuery->fetch(PDO::FETCH_ASSOC);

if (!$staffData) {
    echo "<script>alert('Record unavailable.'); window.location.href = 'caregivers.php';</script>";
    exit();
}

// Fixed path to uploaded_files/ and fallback to caregiver_1.png
$avatarPath = (!empty($staffData['profile_image']) && file_exists('uploaded_files/' . $staffData['profile_image']))
    ? 'uploaded_files/' . htmlspecialchars($staffData['profile_image']) 
    : 'assets/media/caregiver_1.png';

$dependentQuery = $conn->prepare("
    SELECT c.* FROM child c
    INNER JOIN child_caregiver cc ON c.child_id = cc.child_id
    WHERE cc.caregiver_id = :cid
");
$dependentQuery->execute([':cid' => $targetProfileId]);
$assignedChildren = $dependentQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Profile Overview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/caregiver_profile.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="staff-profile-section">
        <h1 class="page-title">Staff Profile Overview</h1>

        <div class="profile-header-card" style="text-align: center;">
            <img src="<?= $avatarPath; ?>" 
                 alt="<?= htmlspecialchars($staffData['name']); ?>" 
                 class="profile-avatar"
                 onerror="this.src='assets/media/caregiver_1.png'"/>
            <h2 class="staff-name"><?= htmlspecialchars($staffData['name']); ?></h2>
            <ul class="staff-stats" style="list-style: none; padding: 0;">
                <li><strong>Credential:</strong> <?= htmlspecialchars($staffData['qualification']); ?></li>
                <li><strong>Years Active:</strong> <?= htmlspecialchars($staffData['experience']); ?></li>
                <li><strong>Certifications:</strong> <?= htmlspecialchars($staffData['special_training'] ?: 'None recorded'); ?></li>
            </ul>

            <a href="logout.php" class="btn-danger" style="display:inline-block; margin-top:20px; background:#dc3545; color:#fff; padding:10px 25px; border-radius:6px; text-decoration:none;">
                <i class="fas fa-sign-out-alt"></i> Secure Logout
            </a>
        </div>

        <div class="dependents-roster" style="margin-top: 40px;">
            <h2 style="border-bottom: 2px solid #ddd; padding-bottom: 10px;">Currently Assigned Children</h2>
            
            <?php if (count($assignedChildren) > 0): ?>
                <div class="dependent-grid" style="display:flex; gap: 15px; flex-wrap:wrap; margin-top:20px;">
                    <?php foreach ($assignedChildren as $child): ?>
                        <div class="dependent-card" style="border: 1px solid #eee; padding: 15px; border-radius: 8px; width: 300px;">
                            <h3><?= htmlspecialchars($child['name']); ?></h3>
                            <p><strong>DOB:</strong> <?= htmlspecialchars($child['date_of_birth']); ?></p>
                            <p><strong>Sex:</strong> <?= ucfirst(htmlspecialchars($child['gender'])); ?></p>
                            <a href="child_profile.php?child_id=<?= $child['child_id']; ?>" class="btn-primary" style="display:block; margin-top:10px; text-align:center;">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="margin-top: 15px; color: #666;">There are currently no active assignments for this staff member.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>