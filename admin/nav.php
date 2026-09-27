<?php
// admin_nav.php
// Shared sidebar navigation for every admin page. Include this instead of
// duplicating the <nav> markup, so a change here updates every admin page at once.
//
// Requires: session already started, and $conn (PDO) already available,
// by the including page. Also expects an auth/role check to have already run
// in the including page (see the snippet in the integration notes).

$currentFile = basename($_SERVER['PHP_SELF']);

// These counts are optional decoration on the nav links; if a page doesn't
// already fetch them, this include fetches them itself so the numbers never
// silently disappear.
if (!isset($total_parents, $total_caregivers, $total_children, $total_bills_issued)) {
    try {
        $total_parents = (int) $conn->query("SELECT COUNT(user_id) FROM users WHERE role = 'Parent'")->fetchColumn();
        $total_caregivers = (int) $conn->query("SELECT COUNT(caregiver_id) FROM caregivers")->fetchColumn();
        $total_children = (int) $conn->query("SELECT COUNT(child_id) FROM child")->fetchColumn();
        $total_bills_issued = (int) $conn->query("SELECT COUNT(bill_id) FROM bill_payment")->fetchColumn();
    } catch (PDOException $e) {
        error_log('admin_nav count error: ' . $e->getMessage());
        $total_parents = $total_caregivers = $total_children = $total_bills_issued = 0;
    }
}

function navActive($file, $current) {
    return $file === $current ? ' class="active"' : '';
}
?>
<nav>
    <div class="logo-name">
        <div class="logo-image">
           <img src="../assets/media/admin.png" alt="Logo">
        </div>
        <div>
            <span class="logo_name">Admin Dashboard</span>
        </div>
    </div>

    <div class="menu-items">
        <ul class="nav-links">
            <li<?= navActive('admin_dashboard.php', $currentFile) ?>>
                <a href="admin_dashboard.php"><i class="fa fa-home"></i><span class="link-name">Dashboard</span></a>
            </li>
            <li<?= navActive('admin_parents.php', $currentFile) ?>>
                <a href="admin_parents.php"><i class="fa fa-users"></i><span class="link-name">Parents (<?= $total_parents ?>)</span></a>
            </li>
            <li<?= navActive('admin_caregivers.php', $currentFile) ?>>
                <a href="admin_caregivers.php"><i class="fa fa-hands-helping"></i><span class="link-name">Caregivers (<?= $total_caregivers ?>)</span></a>
            </li>
            <li<?= navActive('admin_children.php', $currentFile) ?>>
                <a href="admin_children.php"><i class="fa fa-child"></i><span class="link-name">Children (<?= $total_children ?>)</span></a>
            </li>
            <li<?= navActive('admin_payments.php', $currentFile) ?>>
                <a href="admin_payments.php"><i class="fa fa-credit-card"></i><span class="link-name">Payments (<?= $total_bills_issued ?>)</span></a>
            </li>
        </ul>

        <ul class="logout-mode">
            <li><a href="admin_dashboard.php?logout=1"><i class="uil uil-signout"></i><span class="link-name">Logout</span></a></li>
            <li class="mode">
                <a href="#"><i class="uil uil-moon"></i><span class="link-name">Dark Mode</span></a>
                <div class="mode-toggle"><span class="switch"></span></div>
            </li>
        </ul>
    </div>
</nav>
