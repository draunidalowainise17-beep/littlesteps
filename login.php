<?php
session_start();
require_once 'config.php';

$authAlerts = [];

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// --- lightweight brute-force lockout (session-based) ---
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}
const MAX_ATTEMPTS = 5;
const LOCKOUT_SECONDS = 60;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $authAlerts[] = 'Your session expired. Please try submitting the form again.';
    } else {

        $inputEmail = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $inputPass  = trim($_POST['password'] ?? '');
        $attemptKey = strtolower((string) $inputEmail);
        $attempt = $_SESSION['login_attempts'][$attemptKey] ?? ['count' => 0, 'locked_until' => 0];

        if ($attempt['locked_until'] > time()) {
            $waitSecs = $attempt['locked_until'] - time();
            $authAlerts[] = "Too many failed attempts. Please try again in {$waitSecs} seconds.";
        } elseif (!filter_var($inputEmail, FILTER_VALIDATE_EMAIL) || !$inputPass) {
            $authAlerts[] = 'All fields are required. Ensure email is valid.';
        } else {

            $queryUser = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $queryUser->execute([':email' => $inputEmail]);
            $foundRecord = $queryUser->fetch();

            $passwordOk = false;

            if ($foundRecord) {
                if (password_verify($inputPass, $foundRecord['password'])) {
                    $passwordOk = true;
                } elseif (hash_equals($foundRecord['password'], $inputPass)) {
                    // Legacy plain-text account: log in this once, then silently upgrade
                    // it to a proper hash so it never has to be checked as plain text again.
                    $passwordOk = true;
                    $newHash = password_hash($inputPass, PASSWORD_DEFAULT);
                    $upgradeStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $upgradeStmt->execute([$newHash, $foundRecord['user_id']]);
                }
            }

            if ($passwordOk) {
                // Successful login: clear the attempt counter and prevent session fixation
                unset($_SESSION['login_attempts'][$attemptKey]);
                session_regenerate_id(true);

                $_SESSION['user_id'] = $foundRecord['user_id'];
                $_SESSION['role']    = $foundRecord['role'];
                unset($_SESSION['csrf_token']);

                switch (strtolower($foundRecord['role'])) {
                    case 'parent':
                        header("Location: parent_profile.php");
                        break;
                    case 'admin':
                        header("Location: admin/admin_dashboard.php");
                        break;
                    case 'caregiver':
                        header("Location: caregiver_profile.php");
                        break;
                    default:
                        $authAlerts[] = 'System error: unknown user role. Please contact an administrator.';
                }
                if (empty($authAlerts)) {
                    exit();
                }
            } else {
                // Track the failed attempt and lock out after too many
                $attempt['count']++;
                if ($attempt['count'] >= MAX_ATTEMPTS) {
                    $attempt['locked_until'] = time() + LOCKOUT_SECONDS;
                    $attempt['count'] = 0;
                    $authAlerts[] = 'Too many failed attempts. Please wait ' . LOCKOUT_SECONDS . ' seconds and try again.';
                } else {
                    // Deliberately vague: doesn't reveal whether the email exists
                    $authAlerts[] = 'Incorrect email or password.';
                }
                $_SESSION['login_attempts'][$attemptKey] = $attempt;
            }
        }
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrfToken = $_SESSION['csrf_token'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/caregiver_registration.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="auth-page-wrapper" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <section class="auth-card" style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 450px;">
        <form action="" method="POST" class="standard-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <h2 style="text-align:center; color:#333; margin-bottom: 20px;">Account Login</h2>

            <?php if (!empty($authAlerts)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <?php foreach ($authAlerts as $alert): ?>
                        <p style="margin: 0;"><?= htmlspecialchars($alert) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px;">Email Address</label>
                <input type="email" name="email" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:5px;">Password</label>
                <input type="password" name="password" required minlength="6"
                       style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
            </div>

            <!--
                Account Type dropdown removed: the database already knows each
                user's role, so we route after checking credentials instead of
                asking the user to declare it (that previously caused a
                "Role mismatch" error even when the password was correct).
            -->

            <button type="submit" style="width:100%; padding:12px; background:#f9ba60; border:none; color:white; font-weight:bold; border-radius:5px; cursor:pointer;">Secure Login</button>

            <div style="text-align:center; margin-top:20px; font-size: 0.9rem;">
                <p>Don't have an account?</p>
                <a href="register.php" style="color: #28a745; text-decoration: none; font-weight: bold;">Sign up as a Parent</a>
            </div>
        </form>
    </section>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>
