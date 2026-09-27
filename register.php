<?php
session_start();
require_once 'config.php';

// Pull a one-time confirmation message set by a previous successful submission (PRG pattern)
$flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$authAlerts = [];

// Generate a CSRF token for this form load
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- CSRF check ---
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $authAlerts[] = 'Your session expired. Please try submitting the form again.';
    } else {

        $fullName   = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
        $emailAddr  = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
        $contactNum = trim(filter_input(INPUT_POST, 'contact_info', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
        $rawPass    = trim($_POST['password'] ?? '');
        $confirmPass = trim($_POST['confirm_password'] ?? '');

        // --- Field-by-field validation, collecting every problem instead of stopping at the first ---

        // Name: letters, spaces, hyphens and apostrophes only, 2-100 chars
        if (!preg_match("/^[A-Za-z'\-\s]{2,100}$/", $fullName)) {
            $authAlerts[] = 'Please enter a valid full name (letters only, at least 2 characters).';
        }

        // Email: proper format
        if (!filter_var($emailAddr, FILTER_VALIDATE_EMAIL)) {
            $authAlerts[] = 'Please enter a valid email address.';
        }

        // Phone: digits, spaces, +, - only, 7-20 chars
        if (!preg_match("/^[0-9+\-\s]{7,20}$/", $contactNum)) {
            $authAlerts[] = 'Please enter a valid phone number.';
        }

        // Password: at least 8 characters, at least one letter and one number
        if (strlen($rawPass) < 8 || !preg_match('/[A-Za-z]/', $rawPass) || !preg_match('/[0-9]/', $rawPass)) {
            $authAlerts[] = 'Password must be at least 8 characters and include both letters and numbers.';
        } elseif ($rawPass !== $confirmPass) {
            $authAlerts[] = 'Password and confirmation do not match.';
        }

        // Only hit the database if the basic fields are valid, to avoid wasted queries
        if (empty($authAlerts)) {
            $emailCheckQry = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
            $emailCheckQry->execute([':email' => $emailAddr]);

            if ($emailCheckQry->fetch()) {
                $authAlerts[] = 'An account with this email address already exists.';
            }
        }

        // --- Avatar upload: only proceed if there is no error so far ---
        $avatarName = null;
        if (empty($authAlerts) && !empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {

            $allowedExt  = ['jpg', 'jpeg', 'png', 'gif'];
            $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
            $maxBytes    = 2 * 1024 * 1024; // 2MB

            $tmpPath = $_FILES['profile_image']['tmp_name'];
            $origExt = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            $imgInfo = @getimagesize($tmpPath); // false if it is not really an image

            if ($_FILES['profile_image']['size'] > $maxBytes) {
                $authAlerts[] = 'Profile image must be smaller than 2MB.';
            } elseif (!in_array($origExt, $allowedExt) || $imgInfo === false || !in_array($imgInfo['mime'], $allowedMime)) {
                $authAlerts[] = 'Profile image must be a real JPG, PNG or GIF file.';
            } else {
                $avatarName = bin2hex(random_bytes(8)) . '_' . time() . '.' . $origExt;
                $targetPath = "uploads/avatars/" . $avatarName;

                if (!move_uploaded_file($tmpPath, $targetPath)) {
                    $authAlerts[] = 'Avatar upload failed. Please try again.';
                    $avatarName = null;
                }
            }
        }

        // --- Insert only if everything passed ---
        if (empty($authAlerts)) {
            $securedPass = password_hash($rawPass, PASSWORD_DEFAULT);

            $insertStmt = $conn->prepare("INSERT INTO users (name, email, role, password, contact_info, profile_image) VALUES (?, ?, 'Parent', ?, ?, ?)");
            if ($insertStmt->execute([$fullName, $emailAddr, $securedPass, $contactNum, $avatarName])) {

                // Post/Redirect/Get: prevents duplicate submission on refresh and
                // gives an unmistakable, distinct confirmation on the next page load.
                unset($_SESSION['csrf_token']);
                $_SESSION['flash_success'] = 'Account created successfully! You can now log in.';
                header('Location: login.php');
                exit;
            } else {
                $authAlerts[] = 'System error: could not create account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/caregiver_registration.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="auth-page-wrapper" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <section class="auth-card" style="background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 500px;">

            <?php if ($flashSuccess): ?>
                <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                    <p style="margin: 0;"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($flashSuccess) ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="standard-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <h2 style="text-align:center; color:#333; margin-bottom: 10px;">Create Account</h2>
                <div style="background:#e9f7fe; padding:10px; text-align:center; border-radius:5px; color:#2c3e50; font-weight:bold; margin-bottom:20px;">
                    <i class="fas fa-user-friends"></i> Registering as Parent/Guardian
                </div>

                <?php if (!empty($authAlerts)): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #f5c6cb;">
                        <?php foreach ($authAlerts as $alert): ?>
                            <p style="margin: 0;"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($alert) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#555;">Full Legal Name</label>
                    <input type="text" name="name" required pattern="[A-Za-z'\-\s]{2,100}"
                           title="Letters, spaces, hyphens and apostrophes only"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" maxlength="100">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#555;">Email Address</label>
                    <input type="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" maxlength="100">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#555;">Primary Phone Number</label>
                    <input type="text" name="contact_info" required pattern="[0-9+\-\s]{7,20}"
                           title="Digits, spaces, + and - only"
                           value="<?= htmlspecialchars($_POST['contact_info'] ?? '') ?>"
                           style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;" maxlength="20">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#555;">Upload Photo (Optional, JPG/PNG/GIF, max 2MB)</label>
                    <input type="file" name="profile_image" accept="image/png, image/jpeg, image/gif" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; background:#f9f9f9;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#555;">Secure Password</label>
                    <input type="password" name="password" required minlength="8"
                           title="At least 8 characters, with letters and numbers"
                           style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                    <small style="color:#888;">At least 8 characters, including letters and numbers.</small>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:#555;">Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="8"
                           style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px;">
                </div>

                <button type="submit" style="width:100%; padding:14px; background:#17a2b8; color:white; border:none; border-radius:5px; font-size:16px; font-weight:bold; cursor:pointer; transition:0.3s;">Complete Registration</button>

                <div style="text-align:center; margin-top:20px; font-size: 0.95rem;">
                    <p style="color:#666; margin-bottom:5px;">Already registered?</p>
                    <a href="login.php" style="color:#17a2b8; text-decoration:none; font-weight:bold;">Log in to Portal</a>
                </div>
            </form>
        </section>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
