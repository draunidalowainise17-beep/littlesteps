<?php
$unreadAlerts = $_SESSION['notifications'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/style.css">
    
    <link rel="stylesheet" href="assets/css/header.css">
</head>
<body>

    <header class="main-header">
        <a href="index.php" class="brand-logo">
            <img src="assets/media/logo.png" alt="Little Steps Logo"> 
            Little Steps Daycare
        </a>

        <nav class="primary-navigation">
            <a href="index.php">Home</a>
            <a href="about_us.php">About</a>
            <a href="services.php">Services</a>
            <a href="caregivers.php">Caregivers</a>
            <a href="register.php">Registration</a>
        </nav>

        <div class="header-actions">
            <!-- Notification wrapper -->
            <div class="alert-bell-wrapper" id="notification-icon" style="cursor: pointer;">
                <i class="fas fa-bell"></i>
                <?php if(count($unreadAlerts) > 0): ?>
                    <span class="badge-count"><?= count($unreadAlerts) ?></span>
                <?php endif; ?>
            </div>    
        </div>
    </header>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const bellIcon = document.getElementById('notification-icon');
        const alertModal = document.getElementById('notification-modal');
        const bgOverlay = document.getElementById('modal-overlay');
        
        if(bellIcon && alertModal && bgOverlay) {
            const closeBtn = alertModal.querySelector('.close');

            bellIcon.addEventListener('click', () => {
                alertModal.style.display = 'block';
                bgOverlay.style.display = 'block';
            });

            closeBtn.addEventListener('click', () => {
                alertModal.style.display = 'none';
                bgOverlay.style.display = 'none';
            });
            
            bgOverlay.addEventListener('click', () => {
                alertModal.style.display = 'none';
                bgOverlay.style.display = 'none';
            });
        }
    });
    </script>
</body>
</html>