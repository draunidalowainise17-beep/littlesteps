<?php
session_start();
include '../config.php';

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message[] = 'Invalid session token. Please refresh and try again.';
    } else {

        $name             = trim($_POST['name'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $qualification    = trim($_POST['qualification'] ?? '');
        $experience       = trim($_POST['experience'] ?? '');
        $special_training = trim($_POST['special_training'] ?? '');
        $password         = $_POST['password'] ?? '';

        $old = compact('name','email','qualification','experience','special_training');

        // Validation
        if ($name === '' || mb_strlen($name) < 3 || mb_strlen($name) > 100) {
            $message[] = 'Name must be between 3 and 100 characters.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
            $message[] = 'Please enter a valid email address.';
        }
        if ($qualification === '' || mb_strlen($qualification) > 100) {
            $message[] = 'Qualification is required (max 100 chars).';
        }
        if ($experience === '' || !ctype_digit((string)$experience) || (int)$experience < 0 || (int)$experience > 50) {
            $message[] = 'Experience must be a number between 0 and 50.';
        }
        if (mb_strlen($special_training) > 200) {
            $message[] = 'Special training must be 200 characters or less.';
        }
        if (strlen($password) < 8) {
            $message[] = 'Password must be at least 8 characters.';
        }

        // Unique email
        if (empty($message)) {
            $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $message[] = 'This email is already registered.';
            }
        }

        // Image upload
        $profile_image = null;
        if (empty($message) && !empty($_FILES['profile_image']['name'])) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $_FILES['profile_image']['tmp_name']);
            finfo_close($finfo);

            if (!isset($allowed[$mime])) {
                $message[] = 'Profile image must be JPG, PNG, GIF, or WEBP.';
            } elseif ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
                $message[] = 'Profile image must be under 2 MB.';
            } else {
                $profile_image = 'cg_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
                $target = '../uploaded_files/' . $profile_image;
                if (!is_dir('../uploaded_files')) {
                    mkdir('../uploaded_files', 0777, true);
                }
                if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                    $message[] = 'Failed to save uploaded image.';
                    $profile_image = null;
                }
            }
        }

        // Insert
        if (empty($message)) {
            try {
                $conn->beginTransaction();

                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $ins_user = $conn->prepare("INSERT INTO users (name,email,role,password,contact_info,profile_image) VALUES (?,?,'Caregiver',?,'N/A',?)");
                $ins_user->execute([$name, $email, $hashed, $profile_image]);
                $user_id = $conn->lastInsertId();

                $ins_cg = $conn->prepare("INSERT INTO caregivers (caregiver_id,name,qualification,experience,special_training,profile_image) VALUES (?,?,?,?,?,?)");
                $ins_cg->execute([$user_id, $name, $qualification, (int)$experience, $special_training, $profile_image]);

                $conn->commit();
                $message[] = '✅ Caregiver registered successfully! They can now log in.';
                $old = [];
            } catch (PDOException $e) {
                $conn->rollBack();
                $message[] = 'Database error. Please try again.';
            }
        }
    }
}

$dashboard_file = file_exists('admin_dashboard.php') ? 'admin_dashboard.php' : 'admin_dashboard_1.php';
$is_success = !empty($message) && strpos($message[0], '✅') !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Caregiver - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/caregiver_registration.css">
    <style>
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: #333; }
        .form-group label i { margin-right: 8px; color: #17a2b8; }
        .form-group .input-box {
            width: 100%; padding: 12px 15px; border: 1px solid #ddd;
            border-radius: 8px; font-size: 1rem; transition: 0.3s;
        }
        .form-group .input-box:focus {
            border-color: #17a2b8; box-shadow: 0 0 0 3px rgba(23,162,184,0.2); outline: none;
        }
        .form-group .helper-text { font-size: 0.85rem; color: #6c757d; margin-top: 0.3rem; }
        .inline-btn {
            background: #17a2b8; color: #fff; border: none; padding: 12px 30px;
            border-radius: 50px; font-size: 1.1rem; font-weight: 600; cursor: pointer;
            transition: 0.3s; display: inline-block; text-decoration: none; text-align: center;
        }
        .inline-btn:hover { background: #138496; transform: translateY(-2px); }
        .inline-btn-secondary {
            background: #6c757d; color: #fff; border: none; padding: 12px 30px;
            border-radius: 50px; font-size: 1.1rem; font-weight: 600; cursor: pointer;
            transition: 0.3s; display: inline-block; text-decoration: none; text-align: center;
        }
        .inline-btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .message {
            background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px;
            margin-bottom: 20px; border-left: 4px solid #f5c6cb;
        }
        .message p { margin: 0; }
        .message-success { background: #d4edda; color: #155724; border-left: 4px solid #c3e6cb; }
        .role-info {
            text-align: center; font-size: 1.1rem; color: #555; background: #e9f7fe;
            padding: 10px; border-radius: 8px; margin-bottom: 1.5rem;
        }
        .login-option { text-align: center; margin-top: 20px; }
        .login-option p { margin-bottom: 10px; color: #555; }
    </style>
</head>
<body>

<?php include '../header.php'; ?>

<div class="wrapper">
    <section class="caregiver_registration">
        <div class="row">
            <form action="" method="POST" class="caregiver_registration-form" enctype="multipart/form-data">

                <h3><i class="fas fa-user-plus" style="color:#17a2b8;"></i> Register New Caregiver</h3>
                <p class="role-info"><i class="fas fa-shield-alt"></i> Admin: Add a caregiver to the system</p>

                <?php if (!empty($message)): ?>
                    <div class="message <?= $is_success ? 'message-success' : '' ?>">
                        <?php foreach ($message as $msg): ?>
                            <p><?= htmlspecialchars($msg) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Full Name <span style="color:red;">*</span></label>
                    <input type="text" id="name" name="name" required class="input-box" maxlength="100"
                           value="<?= htmlspecialchars($old['name'] ?? '') ?>" placeholder="e.g., Jane Doe">
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address <span style="color:red;">*</span></label>
                    <input type="email" id="email" name="email" required class="input-box" maxlength="100"
                           value="<?= htmlspecialchars($old['email'] ?? '') ?>" placeholder="caregiver@example.com">
                </div>

                <div class="form-group">
                    <label for="qualification"><i class="fas fa-graduation-cap"></i> Qualification <span style="color:red;">*</span></label>
                    <input type="text" id="qualification" name="qualification" required class="input-box" maxlength="100"
                           value="<?= htmlspecialchars($old['qualification'] ?? '') ?>" placeholder="e.g., Early Childhood Education">
                </div>

                <div class="form-group">
                    <label for="experience"><i class="fas fa-calendar-alt"></i> Experience (years) <span style="color:red;">*</span></label>
                    <input type="number" id="experience" name="experience" required class="input-box" min="0" max="50"
                           value="<?= htmlspecialchars($old['experience'] ?? '') ?>" placeholder="e.g., 5">
                </div>

                <div class="form-group">
                    <label for="special_training"><i class="fas fa-certificate"></i> Special Training</label>
                    <input type="text" id="special_training" name="special_training" class="input-box" maxlength="200"
                           value="<?= htmlspecialchars($old['special_training'] ?? '') ?>" placeholder="e.g., First Aid, Montessori">
                    <div class="helper-text">Optional – list any additional certifications.</div>
                </div>

                <div class="form-group">
                    <label for="profile_image"><i class="fas fa-camera"></i> Profile Image</label>
                    <input type="file" id="profile_image" name="profile_image" class="input-box" accept="image/*">
                    <div class="helper-text">JPG, PNG, GIF or WEBP. Max 2 MB.</div>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Temporary Password <span style="color:red;">*</span></label>
                    <input type="password" id="password" name="password" required class="input-box" minlength="8"
                           placeholder="Min 8 characters">
                    <div class="helper-text">The caregiver can change this after first login.</div>
                </div>

                <div class="form-group">
                    <input type="submit" value="Register Caregiver" class="inline-btn" name="submit" style="width:100%;">
                </div>

                <div class="login-option">
                    <p><i class="fas fa-arrow-left"></i> Back to Admin Dashboard</p>
                    <a href="<?= $dashboard_file ?>" class="inline-btn-secondary">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                </div>

            </form>
        </div>
    </section>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>