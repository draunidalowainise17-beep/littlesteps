<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: caregivers.php');
    exit();
}

$alerts = $_SESSION['flash_notification'] ?? [];
unset($_SESSION['flash_notification']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfHash = $_POST['csrf_hash'] ?? '';
    
    if (!isset($_SESSION['csrf_hash']) || !hash_equals($_SESSION['csrf_hash'], $csrfHash)) {
        unset($_SESSION['csrf_hash']);
        $_SESSION['flash_notification'] = ['Form expired or already submitted. Please reload.'];
        header('Location: caregiver_registration.php');
        exit();
    }
    unset($_SESSION['csrf_hash']);

    $fullName    = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $emailAddr   = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $contactNum  = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $creds       = filter_input(INPUT_POST, 'qualification', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $expYears    = filter_input(INPUT_POST, 'experience', FILTER_SANITIZE_NUMBER_INT);
    $extraSkills = filter_input(INPUT_POST, 'special_training', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $rawPass     = trim($_POST['password'] ?? '');
    
    $processLog = [];

    $stmtCheck = $conn->prepare("SELECT user_id, role FROM users WHERE email = :email");
    $stmtCheck->execute([':email' => $emailAddr]);
    $foundUser = $stmtCheck->fetch();

    if ($foundUser) {
        $processLog[] = "Error: Email is already tied to a {$foundUser['role']} account.";
    } else {
        $imgName = null;
        if (!empty($_FILES['profile_image']['name'])) {
            $imgName = time() . '_' . basename($_FILES['profile_image']['name']);
            $dest = "uploads/avatars/" . $imgName;
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $dest)) {
                $processLog[] = 'Avatar upload failed. Proceeding without image.';
                $imgName = null;
            }
        }

        if ($fullName && filter_var($emailAddr, FILTER_VALIDATE_EMAIL) && $rawPass) {
            try {
                $conn->beginTransaction();
                $securedPass = password_hash($rawPass, PASSWORD_DEFAULT);
                $phoneVal = $contactNum ?: 'N/A';

                $userInsertStmt = $conn->prepare("INSERT INTO users (name, email, role, password, contact_info, profile_image) VALUES (?, ?, 'Caregiver', ?, ?, ?)");
                $userInsertStmt->execute([$fullName, $emailAddr, $securedPass, $phoneVal, $imgName]);
                $newUserId = $conn->lastInsertId();

                $staffInsertStmt = $conn->prepare("INSERT INTO caregivers (caregiver_id, name, email, qualification, experience, special_training, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $staffInsertStmt->execute([$newUserId, $fullName, $emailAddr, $creds, $expYears, $extraSkills, $imgName]);

                $conn->commit();
                $processLog[] = 'Success: New staff member has been fully onboarded.';
            } catch (PDOException $err) {
                $conn->rollBack();
                $processLog[] = 'Database integration failed. ' . $err->getMessage();
            }
        } else {
            $processLog[] = 'Validation failed. Check your required inputs.';
        }
    }

    $_SESSION['flash_notification'] = $processLog;
    header('Location: caregiver_registration.php');
    exit();
}

$_SESSION['csrf_hash'] = bin2hex(random_bytes(24));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onboard Staff - Admin Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/caregiver_registration.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="page-wrapper">
        <section class="registration-wrapper" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
            <form id="onboardForm" action="" method="POST" enctype="multipart/form-data" style="background:#fff; padding:30px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                <h3 style="text-align: center; color: #17a2b8;"><i class="fas fa-id-badge"></i> System Onboarding</h3>
                
                <?php if (!empty($alerts)): ?>
                    <div style="background: #e9ecef; padding: 15px; border-left: 4px solid #17a2b8; margin-bottom: 20px;">
                        <?php foreach ($alerts as $a): ?>
                            <p style="margin:0; font-weight:bold; color:#333;"><?= htmlspecialchars($a) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="csrf_hash" value="<?= htmlspecialchars($_SESSION['csrf_hash']) ?>">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Full Legal Name</label>
                    <input type="text" name="name" required class="form-control" style="width:100%; padding:10px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Work Email</label>
                    <input type="email" name="email" required class="form-control" style="width:100%; padding:10px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Contact Number (Optional)</label>
                    <input type="tel" name="phone_number" class="form-control" style="width:100%; padding:10px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Highest Credential</label>
                    <input type="text" name="qualification" required class="form-control" style="width:100%; padding:10px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Years of Experience</label>
                    <input type="number" name="experience" required class="form-control" style="width:100%; padding:10px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Additional Certifications</label>
                    <input type="text" name="special_training" class="form-control" style="width:100%; padding:10px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Upload Avatar</label>
                    <input type="file" name="profile_image" accept="image/*" class="form-control" style="width:100%;">
                </div>

                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Initial System Password</label>
                    <input type="password" name="password" required class="form-control" style="width:100%; padding:10px;">
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; background: #17a2b8; color: white; border: none; border-radius: 5px; cursor: pointer;">Finalize Account Creation</button>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="admin/admin_dashboard.php" style="color: #6c757d; text-decoration: none;"><i class="fas fa-arrow-left"></i> Return to Admin Panel</a>
                </div>
            </form>
        </section>
    </div>
    <script>
        document.getElementById('onboardForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type=submit]');
            if (btn.disabled) { e.preventDefault(); return; }
            btn.disabled = true;
            btn.innerText = 'Processing Request...';
        });
    </script>
</body>
</html>