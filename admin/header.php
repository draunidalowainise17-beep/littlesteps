<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Little Steps Daycare</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />

    <style>
        /* ----- Reset & Base ----- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ----- Header Container ----- */
        .header {
            background-color: #f8f9fa;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            height: 80px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .header .logo-area {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header .logo-area img {
            height: 50px;
            width: auto;
        }

        .header .logo-area .site-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .header .logo-area .site-name span {
            color: #17a2b8;
        }

        /* ----- Navigation ----- */
        .header .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            list-style: none;
        }

        .header .nav-links li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            padding: 5px 0;
            transition: 0.3s;
            border-bottom: 2px solid transparent;
        }

        .header .nav-links li a:hover,
        .header .nav-links li a.active {
            color: #17a2b8;
            border-bottom-color: #17a2b8;
        }

        .header .nav-links li a i {
            margin-right: 6px;
        }

        /* ----- Login/Logout Button ----- */
        .header .auth-btn {
            background-color: #17a2b8;
            color: #fff;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }

        .header .auth-btn:hover {
            background-color: #138496;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .header .auth-btn.logout {
            background-color: #dc3545;
        }

        .header .auth-btn.logout:hover {
            background-color: #c82333;
        }

        /* ----- Mobile Toggle ----- */
        .header .menu-toggle {
            display: none;
            font-size: 1.8rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #333;
        }

        /* ----- Responsive ----- */
        @media (max-width: 768px) {
            .header {
                height: auto;
                padding: 1rem;
                flex-wrap: wrap;
            }

            .header .logo-area {
                flex: 1;
            }

            .header .menu-toggle {
                display: block;
            }

            .header .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: center;
                gap: 0.8rem;
                padding: 1rem 0;
                border-top: 1px solid #ddd;
                margin-top: 1rem;
            }

            .header .nav-links.open {
                display: flex;
            }

            .header .nav-links li {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<header class="header">
    <!-- Logo & Site Name -->
    <div class="logo-area">
        <!-- Replace the src with your actual logo image -->
        <img src="assets/images/logo.png" alt="Little Steps Daycare Logo" />
        <div class="site-name">Little <span>Steps</span> Daycare</div>
    </div>

    <!-- Mobile Toggle Button -->
    <button class="menu-toggle" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Navigation -->
    <ul class="nav-links" id="navLinks">
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="about.php"><i class="fas fa-info-circle"></i> About</a></li>
        <li><a href="services.php"><i class="fas fa-concierge-bell"></i> Services</a></li>
        <li><a href="caregivers.php"><i class="fas fa-hands-helping"></i> Caregivers</a></li>

        <?php if ($is_logged_in): ?>
            <!-- Show Dashboard link based on role -->
            <?php if ($user_role === 'Admin'): ?>
                <li><a href="admin/admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <?php elseif ($user_role === 'Parent'): ?>
                <li><a href="parent_profile.php"><i class="fas fa-user"></i> Profile</a></li>
            <?php elseif ($user_role === 'Caregiver'): ?>
                <li><a href="caregiver_profile.php"><i class="fas fa-id-badge"></i> Profile</a></li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>

    <!-- Auth Button -->
    <div>
        <?php if ($is_logged_in): ?>
            <a href="logout.php" class="auth-btn logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="login.php" class="auth-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
        <?php endif; ?>
    </div>
</header>

<script>
    // Toggle mobile menu
    function toggleMenu() {
        const nav = document.getElementById('navLinks');
        nav.classList.toggle('open');
    }

    // Close menu when a link is clicked (mobile)
    document.querySelectorAll('#navLinks a').forEach(link => {
        link.addEventListener('click', () => {
            document.getElementById('navLinks').classList.remove('open');
        });
    });
</script>