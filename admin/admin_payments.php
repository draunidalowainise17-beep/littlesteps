<?php
session_start();
include '../config.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch all bills with parent details
$stmt = $conn->prepare("
    SELECT 
        b.bill_id,
        b.user_id,
        b.section_description,
        b.total_amount,
        b.paid_amount,
        b.payment_status,
        b.payment_date,
        b.receipt_document,
        u.name AS parent_name
    FROM bill_payment b
    LEFT JOIN users u ON b.user_id = u.user_id
    ORDER BY b.bill_id DESC
");
$stmt->execute();
$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments | Admin</title>

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
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; color: #fff; font-size: 0.9rem; display: inline-block; margin: 2px; }
        .btn-edit { background: #17a2b8; }
        .btn-delete { background: #dc3545; }
        .btn-back { background: #6c757d; padding: 8px 16px; border-radius: 6px; font-weight: 600; }
        .btn-back:hover { background: #5a6268; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .top-bar h1 { margin: 0; font-size: 1.8rem; color: #333; }
        .top-bar h1 i { color: #17a2b8; margin-right: 8px; }
        .status-paid { background: #28a745; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; display: inline-block; font-weight:600; }
        .status-pending { background: #ffc107; color: #212529; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; display: inline-block; font-weight:600; }
        .status-unpaid { background: #dc3545; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; display: inline-block; font-weight:600; }
        .receipt-link { color: #17a2b8; text-decoration: none; font-weight: 600; }
        .receipt-link:hover { text-decoration: underline; }
        .no-receipt { color: #999; font-style: italic; }
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
                <h1><i class="fas fa-credit-card"></i> Manage Payments</h1>
                <a href="admin_dashboard.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Bill #</th>
                            <th>Parent</th>
                            <th>Description</th>
                            <th>Total Due</th>
                            <th>Paid So Far</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Receipt</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bills)): ?>
                            <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><strong>#<?= (int)$bill['bill_id'] ?></strong></td>
                                <td><?= htmlspecialchars($bill['parent_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars($bill['section_description']) ?></td>
                                <td><strong>$<?= number_format($bill['total_amount'], 2) ?></strong></td>
                                <td>$<?= number_format($bill['paid_amount'] ?? 0, 2) ?></td>
                                <td>
                                    <?php 
                                    $status = strtolower(trim($bill['payment_status']));
                                    if ($status === 'paid'): ?>
                                        <span class="status-paid">Paid</span>
                                    <?php elseif ($status === 'pending'): ?>
                                        <span class="status-pending">Pending</span>
                                    <?php else: ?>
                                        <span class="status-unpaid"><?= htmlspecialchars($bill['payment_status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(date('Y-m-d', strtotime($bill['payment_date']))) ?></td>
                                <td>
                                    <?php if (!empty($bill['receipt_document'])): ?>
                                        <a href="../uploaded_files/<?= htmlspecialchars($bill['receipt_document']) ?>" target="_blank" class="receipt-link">
                                            <i class="fas fa-file-pdf"></i> View File
                                        </a>
                                    <?php else: ?>
                                        <span class="no-receipt">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="update_payment.php?bill_id=<?= (int)$bill['bill_id'] ?>" class="btn btn-edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete_payment.php?bill_id=<?= (int)$bill['bill_id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this payment record?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align:center; padding:20px; color:#888;">No payment records located.</td>
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