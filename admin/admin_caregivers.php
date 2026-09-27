<?php
session_start();
include '../config.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch all caregivers with their user details
$stmt = $conn->prepare("
    SELECT 
        c.caregiver_id,
        c.name,
        c.qualification,
        c.experience,
        c.special_training,
        c.profile_image,
        u.email,
        u.contact_info
    FROM caregivers c
    LEFT JOIN users u ON c.caregiver_id = u.user_id
    ORDER BY c.caregiver_id DESC
");
$stmt->execute();
$caregivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Caregivers | Admin</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Iconscout (Required for the sidebar toggle icon) -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    
    <!-- Admin Dashboard CSS -->
    <link rel="stylesheet" href="admin_css/dashboard.css">

    <style>
        .table-wrapper { background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow-x: auto; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        .avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; background: #f0f0f0; border: 2px solid #17a2b8; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; color: #fff; font-size: 0.9rem; display: inline-block; margin: 2px; }
        .btn-edit { background: #17a2b8; }
        .btn-delete { background: #dc3545; }
        .btn-back { background: #6c757d; padding: 8px 16px; border-radius: 6px; font-weight: 600; }
        .btn-back:hover { background: #5a6268; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .top-bar h1 { margin: 0; font-size: 1.8rem; color: #333; }
        .top-bar h1 i { color: #17a2b8; margin-right: 8px; }
    </style>
</head>
<body>
    <!-- 1. Include Sidebar -->
    <?php 
    if (file_exists('admin_header.php')) {
        include 'admin_header.php';
    } elseif (file_exists('admin_sidebar.php')) {
        include 'admin_sidebar.php';
    }
    ?>
    
    <!-- 2. Responsive Foldable Dashboard Section -->
    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
        </div>

        <div class="dash-content">
            <div class="top-bar">
                <h1><i class="fas fa-hands-helping"></i> Manage Caregivers</h1>
                <div>
                    <a href="../caregiver_registration.php" class="btn" style="background:#17a2b8; padding:8px 16px; border-radius:6px; font-weight:600;">
                        <i class="fas fa-plus"></i> Add New Caregiver
                    </a>
                    <a href="admin_dashboard.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Avatar</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Qualification</th>
                            <th>Experience</th>
                            <th>Special Training</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($caregivers)): ?>
                            <?php foreach ($caregivers as $caregiver): ?>
                            <tr>
                                <td><?= (int)$caregiver['caregiver_id'] ?></td>
                                <td>
                                    <img src="<?= !empty($caregiver['profile_image']) ? '../uploaded_files/' . htmlspecialchars($caregiver['profile_image']) : '../assets/media/caregiver_1.png' ?>" 
                                         class="avatar" 
                                         alt="Avatar"
                                         onerror="this.src='../assets/media/caregiver_1.png'">
                                </td>
                                <td><strong><?= htmlspecialchars($caregiver['name']) ?></strong></td>
                                <td><?= htmlspecialchars($caregiver['email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($caregiver['contact_info'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($caregiver['qualification']) ?></td>
                                <td><?= (int)$caregiver['experience'] ?> years</td>
                                <td><?= htmlspecialchars($caregiver['special_training'] ?: 'N/A') ?></td>
                                <td>
                                    <a href="edit_caregiver.php?caregiver_id=<?= (int)$caregiver['caregiver_id'] ?>" class="btn btn-edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete_caregiver.php?caregiver_id=<?= (int)$caregiver['caregiver_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this caregiver?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align:center; padding:20px; color:#888;">No caregivers registered yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- 3. Sidebar Toggle Script -->
    <script src="admin_js/dashboard.js"></script>
</body>
</html>