<?php
session_start();
require_once '../config.php';

// Admin access only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

try {
    // 1. Total Children
    $stmtKids = $conn->query("SELECT COUNT(*) FROM child");
    $totalChildren = (int)$stmtKids->fetchColumn();

    // 2. Total Caregivers
    $stmtCaregivers = $conn->query("SELECT COUNT(*) FROM caregivers");
    $totalCaregivers = (int)$stmtCaregivers->fetchColumn();

    // 3. Financial Totals
    $stmtFinance = $conn->query("
        SELECT 
            COALESCE(SUM(total_amount), 0) AS total_billed,
            COALESCE(SUM(paid_amount), 0) AS total_collected
        FROM bill_payment
    ");
    $financeData = $stmtFinance->fetch(PDO::FETCH_ASSOC);

    $totalBilled      = (float)$financeData['total_billed'];
    $totalCollected   = (float)$financeData['total_collected'];
    $totalOutstanding = max(0, $totalBilled - $totalCollected);
    $collectionRate   = ($totalBilled > 0) ? round(($totalCollected / $totalBilled) * 100, 1) : 0;

    $stats = [
        'total_children'    => $totalChildren,
        'total_caregivers'  => $totalCaregivers,
        'total_billed'      => $totalBilled,
        'total_collected'   => $totalCollected,
        'total_outstanding' => $totalOutstanding,
        'collection_rate'   => $collectionRate
    ];

} catch (PDOException $e) {
    $stats = [
        'total_children'    => 0,
        'total_caregivers'  => 0,
        'total_billed'      => 0,
        'total_collected'   => 0,
        'total_outstanding' => 0,
        'collection_rate'   => 0
    ];
}

// =========================================================================
// Fetch Recent Activity
// =========================================================================

// 1. Recent Bills (Fixed: removed non-existent child_id column)
$recentBills = [];
try {
    $recentBills = $conn->query("
        SELECT bill_id, total_amount, paid_amount, payment_status
        FROM bill_payment
        ORDER BY bill_id DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recentBills = [];
}

// 2. Recent Caregivers
$recentCaregivers = [];
try {
    $recentCaregivers = $conn->query("
        SELECT caregiver_id, name, qualification, experience
        FROM caregivers
        ORDER BY caregiver_id DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recentCaregivers = [];
}

// 3. Recent Children
$recentChildren = [];
try {
    $recentChildren = $conn->query("
        SELECT child_id, name, date_of_birth, gender
        FROM child
        ORDER BY child_id DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recentChildren = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Little Steps</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="admin_css/sidebar.css">

    <style>
        body {
            background: #f4f7fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
        }

        /* Dark mode */
        body.dark {
            background: #1e1e2d;
            color: #e4e4e4;
        }
        body.dark .stat-card,
        body.dark .panel,
        body.dark .action-btn {
            background: #2a2a3c;
            color: #e4e4e4;
        }
        body.dark .stat-info small { color: #aaa; }
        body.dark .stat-info h3 { color: #fff; }
        body.dark .page-title { color: #fff; }
        body.dark .section-title { color: #e4e4e4; }
        body.dark .panel table td,
        body.dark .panel table th { color: #ddd; border-color: #3a3a4c; }

        .dashboard-container {
            padding: 30px;
            max-width: 100%;
            box-sizing: border-box;
        }

        .page-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 20px;
        }
        .page-title i { color: #17a2b8; }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #fff;
            flex-shrink: 0;
        }
        .stat-info small {
            color: #777;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-info h3 {
            margin: 4px 0 0;
            font-size: 1.4rem;
            color: #222;
        }
        .bg-teal   { background: #17a2b8; }
        .bg-green  { background: #28a745; }
        .bg-yellow { background: #ffc107; }
        .bg-blue   { background: #007bff; }
        .bg-red    { background: #dc3545; }
        .bg-purple { background: #6f42c1; }

        .section-title {
            font-size: 1.2rem;
            color: #333;
            margin: 30px 0 15px;
            font-weight: 600;
        }
        .section-title i { color: #17a2b8; margin-right: 8px; }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .action-btn {
            background: #fff;
            padding: 18px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: 0.2s;
            border: 1px solid transparent;
        }
        .action-btn:hover {
            border-color: #17a2b8;
            color: #17a2b8;
            transform: translateY(-2px);
        }
        .action-btn i {
            display: block;
            font-size: 1.6rem;
            margin-bottom: 8px;
            color: #17a2b8;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 20px;
        }
        .panel {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .panel h3 {
            margin-top: 0;
            font-size: 1.05rem;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .panel h3 i { color: #17a2b8; margin-right: 8px; }
        .panel table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .panel th {
            text-align: left;
            color: #777;
            font-weight: 600;
            padding: 8px 4px;
            border-bottom: 1px solid #eee;
        }
        .panel td {
            padding: 8px 4px;
            border-bottom: 1px solid #f5f5f5;
            color: #333;
        }
        .status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-paid    { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-overdue { background: #f8d7da; color: #721c24; }
        .empty {
            text-align: center;
            color: #999;
            padding: 20px 0;
            font-style: italic;
        }
    </style>
</head>
<body>

<?php 
// Safely load sidebar if the file exists
if (file_exists('admin_sidebar.php')) {
    include 'admin_sidebar.php';
}
?>

<div class="dashboard-container">

    <h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>

    <!-- STATS CARDS -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon bg-teal"><i class="fas fa-child"></i></div>
            <div class="stat-info">
                <small>Total Children</small>
                <h3><?= $stats['total_children'] ?></h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fas fa-user-nurse"></i></div>
            <div class="stat-info">
                <small>Total Caregivers</small>
                <h3><?= $stats['total_caregivers'] ?></h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-yellow"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="stat-info">
                <small>Total Billed</small>
                <h3>$<?= number_format($stats['total_billed'], 2) ?></h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="stat-info">
                <small>Collected</small>
                <h3>$<?= number_format($stats['total_collected'], 2) ?></h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-red"><i class="fas fa-exclamation-circle"></i></div>
            <div class="stat-info">
                <small>Outstanding</small>
                <h3>$<?= number_format($stats['total_outstanding'], 2) ?></h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-purple"><i class="fas fa-percentage"></i></div>
            <div class="stat-info">
                <small>Collection Rate</small>
                <h3><?= $stats['collection_rate'] ?>%</h3>
            </div>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <h2 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
    <div class="quick-actions">
        <a href="../caregiver_registration.php" class="action-btn">
            <i class="fas fa-user-plus"></i> Register Caregiver
        </a>
        <a href="admin_children.php" class="action-btn">
            <i class="fas fa-child"></i> Manage Children
        </a>
        <a href="admin_caregivers.php" class="action-btn">
            <i class="fas fa-users"></i> Manage Caregivers
        </a>
        <a href="admin_payments.php" class="action-btn">
            <i class="fas fa-money-bill-wave"></i> Payments
        </a>
        <a href="admin_parents.php" class="action-btn">
            <i class="fas fa-user-friends"></i> Parents
        </a>
    </div>

    <!-- RECENT ACTIVITY -->
    <h2 class="section-title"><i class="fas fa-clock"></i> Recent Activity</h2>
    <div class="dashboard-grid">

        <!-- Recent Bills -->
        <div class="panel">
            <h3><i class="fas fa-file-invoice"></i> Recent Bills</h3>
            <?php if (empty($recentBills)): ?>
                <p class="empty">No bills yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Bill #</th><th>Amount</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBills as $b): ?>
                            <?php
                                $status = strtolower($b['payment_status'] ?? 'pending');
                                $class  = $status === 'paid' ? 'status-paid'
                                        : ($status === 'overdue' ? 'status-overdue' : 'status-pending');
                            ?>
                            <tr>
                                <td>
                                    <a href="admin_update_payment.php?bill_id=<?= (int)$b['bill_id'] ?>"
                                       style="color:#17a2b8;text-decoration:none;font-weight:bold;">
                                        #<?= (int)$b['bill_id'] ?>
                                    </a>
                                </td>
                                <td>$<?= number_format($b['total_amount'], 2) ?></td>
                                <td><span class="status <?= $class ?>"><?= htmlspecialchars($b['payment_status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Recent Caregivers -->
        <div class="panel">
            <h3><i class="fas fa-user-nurse"></i> Recent Caregivers</h3>
            <?php if (empty($recentCaregivers)): ?>
                <p class="empty">No caregivers yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Name</th><th>Qualification</th><th>Exp.</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentCaregivers as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['name']) ?></td>
                                <td><?= htmlspecialchars($c['qualification']) ?></td>
                                <td><?= (int)$c['experience'] ?>y</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Recent Children -->
        <div class="panel">
            <h3><i class="fas fa-child"></i> Recent Children</h3>
            <?php if (empty($recentChildren)): ?>
                <p class="empty">No children yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Name</th><th>Gender</th><th>DOB</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentChildren as $ch): ?>
                            <tr>
                                <td><?= htmlspecialchars($ch['name']) ?></td>
                                <td><?= htmlspecialchars($ch['gender']) ?></td>
                                <td><?= htmlspecialchars($ch['date_of_birth']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

</div>

<script src="admin_js/dashboard.js"></script>

</body>
</html>