<?php
ob_start();
session_start();
require_once 'config.php';

$authError = null;

function authenticateCaregiver($data) {
    $_SESSION['user_id']          = $data['user_id'];
    $_SESSION['caregiver_id']     = $data['caregiver_id'];
    $_SESSION['name']             = $data['name'];
    $_SESSION['email']            = $data['email'];
    $_SESSION['role']             = 'Caregiver';
    $_SESSION['qualification']    = $data['qualification'];
    $_SESSION['experience']       = $data['experience'];
    $_SESSION['special_training'] = $data['special_training'];
    $_SESSION['profile_image']    = $data['profile_image'];

    ob_end_clean();
    header("Location: caregiver_profile.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $passInput  = trim($_POST['password'] ?? '');

    if (filter_var($emailInput, FILTER_VALIDATE_EMAIL) && !empty($passInput)) {
        
        $sql = "SELECT u.user_id, u.email, u.password, u.role, 
                       c.caregiver_id, c.name, c.qualification, c.experience, 
                       c.special_training, c.profile_image 
                FROM users u 
                INNER JOIN caregivers c ON c.caregiver_id = u.user_id 
                WHERE u.email = :email AND u.role = 'Caregiver'";
                
        $authQuery = $conn->prepare($sql);
        $authQuery->execute([':email' => $emailInput]);
        
        if ($authQuery->rowCount() > 0) {
            $record = $authQuery->fetch(PDO::FETCH_ASSOC);
            if (password_verify($passInput, $record['password']) || $passInput === $record['password']) {
                authenticateCaregiver($record);
            } else {
                $authError = 'Incorrect password provided.';
            }
        } else {
            $authError = 'No caregiver account found with that email.';
        }
    } else {
        $authError = 'Valid email and password are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caregiver Login | Little Steps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/caregiver_registration.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="auth-page-wrapper">
        <section class="auth-card">
            <form action="" method="POST" class="standard-form">
                <h2 style="color:#e8758e; text-align:center; margin-bottom:10px;"><i class="fas fa-user-shield"></i> Caregiver Login</h2>
                <p style="text-align:center; color:#666; font-size:0.95rem; margin-bottom:25px;">Welcome back! Enter your staff credentials.</p>

                <?php if ($authError): ?>
                    <div class="alert alert-danger">
                        <p><?= htmlspecialchars($authError); ?></p>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required class="form-control" placeholder="staff@littlesteps.com">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required class="form-control" placeholder="••••••••">
                </div>

                <button type="submit" class="btn-primary" name="submit" style="margin-top:10px;">Secure Login</button>

                <div style="text-align:center; margin-top:20px; font-size:0.95rem;">
                    <p style="color:#666; margin-bottom:5px;">Need an account created?</p>
                    <span style="color:#e8758e; font-weight:600;">Contact Administrator</span>
                </div>
            </form>
        </section>
    </div>
</body>
</html>