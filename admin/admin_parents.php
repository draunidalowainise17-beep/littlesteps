<?php
session_start();
include '../config.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch all parents with their children (names and count)
$stmt = $conn->prepare("
    SELECT 
        u.user_id, 
        u.name, 
        u.email, 
        u.contact_info, 
        u.profile_image,
        COUNT(c.child_id) AS child_count,
        GROUP_CONCAT(c.name SEPARATOR ', ') AS child_names
    FROM users u
    LEFT JOIN child c ON u.user_id = c.user_id
    WHERE u.role = 'Parent'
    GROUP BY u.user_id
    ORDER BY u.user_id DESC
");
$stmt->execute();
$parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Parents | Admin</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Iconscout (Required for the sidebar toggle icon) -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    
    <!-- Admin Dashboard CSS -->
    <link rel="stylesheet" href="admin_css/dashboard.css">

    <style>
        .table-wrapper { 
            background: #fff; 
            border-radius: 12px; 
            padding: 1.5rem; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            overflow-x: auto; 
            margin-top: 15px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; color: #333; }
        .avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid #17a2b8; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; color: #fff; font-size: 0.9rem; display: inline-block; }
        .btn-edit { background: #17a2b8; }
        .btn-delete { background: #dc3545; }
        .btn-back { background: #6c757d; padding: 8px 16px; border-radius: 6px; font-weight: 600; }
        .btn-back:hover { background: #5a6268; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .top-bar h1 { margin: 0; font-size: 1.8rem; color: #333; }
        .top-bar h1 i { color: #17a2b8; margin-right: 8px; }
        .badge { background: #17a2b8; color: #fff; padding: 2px 10px; border-radius: 20px; font-size: 0.85rem; display: inline-block; margin-right: 8px; }
        .child-names { font-size: 0.9rem; color: #444; max-width: 250px; display: inline-block; vertical-align: middle; }
        .no-children { color: #999; font-style: italic; }
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
    
    <!-- 2. Main Dashboard Section that shifts when sidebar toggles -->
    <section class="dashboard">
        <!-- Top bar with the toggle icon -->
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
        </div>

        <div class="dash-content">
            <div class="top-bar">
                <h1><i class="fas fa-users"></i> Manage Parents</h1>
                <a href="admin_dashboard.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
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
                            <th>Children</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($parents)): ?>
                            <?php foreach ($parents as $parent): ?>
                            <tr>
                                <td><?= (int)$parent['user_id'] ?></td>
                                <td>
                                    <img src="<?= !empty($parent['profile_image']) ? '../uploaded_files/' . htmlspecialchars($parent['profile_image']) : '../assets/media/parent_1.png' ?>" 
                                         class="avatar" 
                                         alt="Avatar"
                                         onerror="this.src='../assets/media/parent_1.png'">
                                </td>
                                <td><strong><?= htmlspecialchars($parent['name']) ?></strong></td>
                                <td><?= htmlspecialchars($parent['email']) ?></td>
                                <td><?= htmlspecialchars($parent['contact_info'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge"><?= (int)$parent['child_count'] ?></span>
                                    <?php if ($parent['child_count'] > 0): ?>
                                        <span class="child-names"><?= htmlspecialchars($parent['child_names']) ?></span>
                                    <?php else: ?>
                                        <span class="no-children">No children</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_parent.php?user_id=<?= (int)$parent['user_id'] ?>" class="btn btn-edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete_parent.php?user_id=<?= (int)$parent['user_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this parent?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding: 20px; color: #888;">No registered parents found.</td>
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