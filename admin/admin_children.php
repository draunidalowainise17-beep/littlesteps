<?php
session_start();
include '../config.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch all children with parent details and document info
$stmt = $conn->prepare("
    SELECT 
        c.child_id,
        c.name,
        c.date_of_birth,
        c.gender,
        c.medical_history,
        c.special_needs,
        c.child_image,
        c.birth_certificate,
        c.medical_records,
        c.medical_reports,
        u.name AS parent_name,
        u.user_id AS parent_id
    FROM child c
    LEFT JOIN users u ON c.user_id = u.user_id
    ORDER BY c.child_id DESC
");
$stmt->execute();
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data for the "Assign Caregiver" modal
$caregivers_stmt = $conn->prepare("SELECT caregiver_id, name FROM caregivers ORDER BY name ASC");
$caregivers_stmt->execute();
$all_caregivers = $caregivers_stmt->fetchAll(PDO::FETCH_ASSOC);

$assignments_stmt = $conn->prepare("SELECT child_id, caregiver_id FROM child_caregiver");
$assignments_stmt->execute();
$assignments_by_child = [];
foreach ($assignments_stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $assignments_by_child[$row['child_id']][] = (int) $row['caregiver_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Children | Admin</title>

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
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; color: #fff; font-size: 0.9rem; display: inline-block; margin: 2px; border: none; cursor: pointer; font-family: inherit; }
        .btn-edit { background: #17a2b8; }
        .btn-delete { background: #dc3545; }
        .btn-assign { background: #ffc107; color: #212529; }
        .btn-back { background: #6c757d; padding: 8px 16px; border-radius: 6px; font-weight: 600; }
        .btn-back:hover { background: #5a6268; }
        .btn-view { background: #28a745; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .top-bar h1 { margin: 0; font-size: 1.8rem; color: #333; }
        .top-bar h1 i { color: #17a2b8; margin-right: 8px; }
        .badge { background: #17a2b8; color: #fff; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; }
        .no-doc { color: #999; font-style: italic; }

        /* Assign Caregiver modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem 2rem;
            width: 90%;
            max-width: 420px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-box h3 { margin-top: 0; color: #333; }
        .caregiver-checkbox-list { display: flex; flex-direction: column; gap: 10px; margin: 1rem 0; }
        .caregiver-checkbox-list label { display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer; color: #444; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 1rem; }
        .btn-cancel { background: #6c757d; }
        .btn-save { background: #17a2b8; }
        .no-caregivers { color: #999; font-style: italic; }
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
                <h1><i class="fas fa-child"></i> Manage Children</h1>
                <a href="admin_dashboard.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Date of Birth</th>
                            <th>Gender</th>
                            <th>Medical History</th>
                            <th>Special Needs</th>
                            <th>Parent</th>
                            <th>Documents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($children)): ?>
                            <?php foreach ($children as $child): ?>
                            <?php
                                $assigned_ids = $assignments_by_child[$child['child_id']] ?? [];
                                $assigned_json = htmlspecialchars(json_encode($assigned_ids), ENT_QUOTES);
                            ?>
                            <tr>
                                <td><?= (int)$child['child_id'] ?></td>
                                <td>
                                    <img src="<?= !empty($child['child_image']) ? '../uploaded_files/' . htmlspecialchars($child['child_image']) : '../assets/media/caregiver_1.png' ?>" 
                                         class="avatar" 
                                         alt="Child Photo"
                                         onerror="this.src='../assets/media/caregiver_1.png'">
                                </td>
                                <td><strong><?= htmlspecialchars($child['name']) ?></strong></td>
                                <td><?= htmlspecialchars($child['date_of_birth']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($child['gender'])) ?></td>
                                <td><?= htmlspecialchars($child['medical_history'] ?: 'None') ?></td>
                                <td><?= htmlspecialchars($child['special_needs'] ?: 'No') ?></td>
                                <td>
                                    <a href="admin_parents.php?parent_id=<?= (int)$child['parent_id'] ?>" style="color:#17a2b8; text-decoration:none; font-weight:600;">
                                        <?= htmlspecialchars($child['parent_name'] ?? 'Unknown') ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($child['birth_certificate']) || !empty($child['medical_records']) || !empty($child['medical_reports'])): ?>
                                        <?php if (!empty($child['birth_certificate'])): ?>
                                            <span class="badge" style="background:#28a745;">BC</span>
                                        <?php endif; ?>
                                        <?php if (!empty($child['medical_records'])): ?>
                                            <span class="badge" style="background:#17a2b8;">MR</span>
                                        <?php endif; ?>
                                        <?php if (!empty($child['medical_reports'])): ?>
                                            <span class="badge" style="background:#ffc107; color:#333;">Rep</span>
                                        <?php endif; ?>
                                        <a href="view_documents.php?child_id=<?= (int)$child['child_id'] ?>" class="btn btn-view" style="font-size:0.7rem; padding:2px 8px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="no-doc">No docs</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="update_child.php?child_id=<?= (int)$child['child_id'] ?>" class="btn btn-edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete_child.php?child_id=<?= (int)$child['child_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this child?')"><i class="fas fa-trash"></i></a>
                                    <button type="button"
                                            class="btn btn-assign"
                                            title="Assign Caregivers"
                                            onclick='openAssignModal(<?= (int)$child['child_id'] ?>, <?= json_encode($child['name']) ?>, <?= $assigned_json ?>)'>
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align:center; padding:20px; color:#888;">No children registered in the system.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Assign Caregiver Modal -->
    <div class="modal-overlay" id="assignModalOverlay">
        <div class="modal-box">
            <h3><i class="fas fa-user-plus"></i> Assign Caregivers to <span id="assignChildName"></span></h3>
            <form action="assign_caregiver.php" method="POST">
                <input type="hidden" name="child_id" id="assignChildId" value="">
                <div class="caregiver-checkbox-list">
                    <?php if (empty($all_caregivers)): ?>
                        <p class="no-caregivers">No caregivers registered yet.</p>
                    <?php else: ?>
                        <?php foreach ($all_caregivers as $caregiver): ?>
                            <label>
                                <input type="checkbox"
                                       class="caregiver-checkbox"
                                       name="caregiver_ids[]"
                                       value="<?= (int) $caregiver['caregiver_id'] ?>">
                                <?= htmlspecialchars($caregiver['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-cancel" onclick="closeAssignModal()">Cancel</button>
                    <button type="submit" class="btn btn-save">Save Assignments</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        function openAssignModal(childId, childName, assignedCaregiverIds) {
            document.getElementById('assignChildId').value = childId;
            document.getElementById('assignChildName').textContent = childName;

            document.querySelectorAll('.caregiver-checkbox').forEach(function (box) {
                box.checked = assignedCaregiverIds.includes(parseInt(box.value, 10));
            });

            document.getElementById('assignModalOverlay').classList.add('active');
        }

        function closeAssignModal() {
            document.getElementById('assignModalOverlay').classList.remove('active');
        }

        document.getElementById('assignModalOverlay').addEventListener('click', function (e) {
            if (e.target === this) {
                closeAssignModal();
            }
        });
    </script>
    <script src="admin_js/dashboard.js"></script>
</body>
</html>