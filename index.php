<?php
session_start();
require_once 'config.php';

// Handle hidden popup login form if it gets submitted here
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $loginEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $loginPass = $_POST['password'];

    try {
        $loginStmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $loginStmt->execute([':email' => $loginEmail, ':password' => $loginPass]);

        if ($loginStmt->rowCount() > 0) {
            $accountData = $loginStmt->fetch();
            $_SESSION['user_id'] = $accountData['user_id'];
            $_SESSION['role'] = $accountData['role'];

            switch(strtolower($accountData['role'])) {
                case 'admin':
                    header("Location: admin/admin_dashboard.php");
                    break;
                case 'parent':
                    header("Location: parent_profile.php");
                    break;
                case 'caregiver':
                    header("Location: caregiver_profile.php");
                    break;
            }
            exit();
        } else {
            echo "<script>alert('Invalid login credentials.');</script>";
        }
    } catch (PDOException $e) {
        die("System error occurred.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Little Steps | Home</title>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
</head>
<body>
    <?php include('header.php'); ?>

    <main class="hero-section" id="home">
        <div class="hero-content">
            <h1 class="hero-title" data-aos="fade-right" data-aos-offset="300" data-aos-easing="ease-in-sine">
                Welcome to <span>Little Steps Day Care Centre</span>
            </h1>
            <p class="hero-text" data-aos="fade-right" data-aos-offset="300" data-aos-easing="ease-in-sine">
                We’re here to provide a safe, joyful, and nurturing space where your child can thrive.<br>
                Let’s create a world of smiles and cherished memories together!
            </p>
            <a href="register.php" class="cta-button">Register Today</a>
        </div>
        
        <div class="hero-visual" data-aos="zoom-in" data-aos-duration="2000">
            <img src="assets/media/hero-banner.png" alt="Happy kids at daycare">
        </div>
    </main>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1200, once: true });
    </script>
</body>
</html>