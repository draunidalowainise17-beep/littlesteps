<?php
// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav>
    <div class="logo-name">
        <div><i class="uil uil-bars sidebar-toggle"></i></div>
        <div class="logo-image">
            <img src="../assets/media/admin.png"
                 alt="LittleSteps Admin"
                 style="width:45px;height:45px;object-fit:cover;border-radius:50%;">
        </div>
        <div>
            <span class="logo_name">Admin Dashboard</span>
        </div>
    </div>

    <div class="menu-items">
        <ul class="nav-links">
            <li><a href="admin_dashboard.php"><i class="fas fa-home"></i><span class="link-name">Dashboard</span></a></li>
            <li><a href="admin_parents.php"><i class="fas fa-users"></i><span class="link-name">Parents</span></a></li>
            <li><a href="admin_caregivers.php"><i class="fas fa-hands-helping"></i><span class="link-name">Caregivers</span></a></li>
            <li><a href="admin_children.php"><i class="fas fa-child"></i><span class="link-name">Children</span></a></li>
            <li><a href="admin_payments.php"><i class="fas fa-credit-card"></i><span class="link-name">Payments</span></a></li>
        </ul>

        <ul class="logout-mode">
            <li>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i><span class="link-name">Logout</span>
                </a>
            </li>
            <li class="mode">
                <a href="javascript:void(0)" id="darkModeToggle">
                    <i class="fas fa-moon"></i><span class="link-name">Dark Mode</span>
                </a>
                <div class="mode-toggle" id="darkModeSwitch"><span class="switch"></span></div>
            </li>
        </ul>
    </div>
</nav>