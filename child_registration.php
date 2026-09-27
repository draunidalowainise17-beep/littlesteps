<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$activeUserId = $_SESSION['user_id'];
$systemAlerts = [];
$isEnrolled = false;
$enrollmentDetails = [];

// CSRF token for this form load
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// --- Pull a one-time confirmation from a previous successful submission (PRG pattern) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_SESSION['flash_enrollment'])) {
    $isEnrolled = true;
    $enrollmentDetails = $_SESSION['flash_enrollment'];
    unset($_SESSION['flash_enrollment']);
}

// Verifies the uploaded file's real content, not just its extension/name
function processDocument($inputName, $dir, $prefix, &$errors, $label, $required) {
    if (empty($_FILES[$inputName]['name']) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        if ($required) {
            $errors[] = "$label is required.";
        }
        return null;
    }

    $maxBytes = 5 * 1024 * 1024; // 5MB
    if ($_FILES[$inputName]['size'] > $maxBytes) {
        $errors[] = "$label must be smaller than 5MB.";
        return null;
    }

    $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
    $tmpPath = $_FILES[$inputName]['tmp_name'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $allowed = [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
    ];

    if (!isset($allowed[$ext]) || $realMime !== $allowed[$ext]) {
        $errors[] = "$label must be a genuine PDF, JPG or PNG file.";
        return null;
    }

    $secureName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($tmpPath, $dir . $secureName)) {
        $errors[] = "$label failed to upload. Please try again.";
        return null;
    }

    return $secureName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $systemAlerts[] = 'Your session expired. Please try submitting the form again.';
    } else {

        $fullName   = trim($_POST['child_name'] ?? '');
        $birthDate  = $_POST['dob'] ?? '';
        $sex        = $_POST['gender'] ?? '';
        $medHistory = trim($_POST['medical_history'] ?? '');
        $reqSpecial = $_POST['special_needs'] ?? 'No';

        // --- Name ---
        if (!preg_match("/^[A-Za-z'\-\s]{2,100}$/", $fullName)) {
            $systemAlerts[] = 'Please enter a valid child name (letters only, at least 2 characters).';
        }

        // --- Gender ---
        if (!in_array($sex, ['male', 'female'])) {
            $systemAlerts[] = 'Please select the child\'s sex.';
        }

        // --- Special needs ---
        if (!in_array($reqSpecial, ['Yes', 'No'])) {
            $reqSpecial = 'No';
        }

        // --- Date of birth: must be a real, past date, and the child must be 4 or under ---
        $childAge = null;
        $dobObj = DateTime::createFromFormat('Y-m-d', $birthDate);
        $dobErrors = DateTime::getLastErrors();

        if (!$dobObj || ($dobErrors && ($dobErrors['warning_count'] > 0 || $dobErrors['error_count'] > 0))) {
            $systemAlerts[] = 'Please enter a valid date of birth.';
        } else {
            $today = new DateTime('today');
            if ($dobObj > $today) {
                $systemAlerts[] = 'Date of birth cannot be in the future.';
            } else {
                $childAge = $today->diff($dobObj)->y;
                if ($childAge > 4) {
                    $systemAlerts[] = 'This centre only enrols children aged 4 and under. Please double-check the date of birth.';
                }
            }
        }

        // --- Duplicate enrolment check (same name + DOB under this parent account) ---
        if (empty($systemAlerts)) {
            $dupCheck = $conn->prepare("SELECT child_id FROM child WHERE user_id = ? AND name = ? AND date_of_birth = ?");
            $dupCheck->execute([$activeUserId, $fullName, $birthDate]);
            if ($dupCheck->fetch()) {
                $systemAlerts[] = 'A child with this name and date of birth is already enrolled on your account.';
            }
        }

        // --- Document uploads (only bother processing if everything else passed) ---
        $fileBC = $fileMedRec = $fileMedRep = null;
        if (empty($systemAlerts)) {
            $docPath = 'uploads/documents/';
            if (!is_dir($docPath)) {
                mkdir($docPath, 0755, true);
            }

            $fileBC     = processDocument('birth_certificate', $docPath, 'cert', $systemAlerts, 'Birth Certificate', true);
            $fileMedRec = processDocument('medical_records', $docPath, 'meds', $systemAlerts, 'Medical Records', true);
            $fileMedRep = processDocument('medical_reports', $docPath, 'reps', $systemAlerts, 'Doctor Report', false);
        }

        // --- Save ---
        if (empty($systemAlerts)) {
            try {
                $enrollQuery = $conn->prepare("
                    INSERT INTO child (
                        name, date_of_birth, gender, medical_history, special_needs, user_id,
                        birth_certificate, medical_records, medical_reports
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $enrollQuery->execute([
                    $fullName, $birthDate, $sex, $medHistory, $reqSpecial, $activeUserId,
                    $fileBC, $fileMedRec, $fileMedRep
                ]);

                // Post/Redirect/Get: stops a page refresh from re-submitting and
                // duplicating the child record, and gives a clean confirmation URL.
                unset($_SESSION['csrf_token']);
                $_SESSION['flash_enrollment'] = [
                    'id' => $conn->lastInsertId(),
                    'name' => $fullName,
                    'dob' => $birthDate,
                    'age' => $childAge,
                    'gender' => $sex,
                    'docs' => [
                        'BC' => $fileBC,
                        'Rec' => $fileMedRec,
                        'Rep' => $fileMedRep ?: 'N/A',
                    ],
                ];
                header('Location: ' . basename($_SERVER['PHP_SELF']));
                exit;
            } catch (PDOException $e) {
                $systemAlerts[] = 'System fault: could not save child record. Please try again.';
            }
        }
    }

    // Refresh the CSRF token for the re-rendered form after a failed submission
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Enrollment | Little Steps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/caregiver_registration.css">
    <style>
        .enroll-wrapper { display: flex; justify-content: center; padding: 3rem 1rem; background: #f4f7f6; min-height: 90vh; }
        .enroll-panel { background: #fff; width: 100%; max-width: 650px; padding: 2.5rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .form-control { width: 100%; padding: 12px; border: 1px solid #ccd1d9; border-radius: 6px; margin-top: 5px; }
        .form-group { margin-bottom: 20px; }
        .btn-submit { background: #28a745; color: white; border: none; padding: 15px; width: 100%; border-radius: 6px; font-size: 1.1rem; cursor: pointer; }
        .receipt-panel { background: #fff; max-width: 600px; margin: 0 auto; padding: 2rem; border-radius: 10px; border-top: 5px solid #28a745; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="enroll-wrapper">
    <?php if ($isEnrolled): ?>
        <div class="receipt-panel">
            <h2 style="text-align:center; color:#28a745;"><i class="fas fa-check-circle"></i> Enrollment Confirmed</h2>
            <hr style="margin:20px 0; border:0; border-top:1px solid #eee;">
            <p><strong>System ID:</strong> <?= htmlspecialchars($enrollmentDetails['id']); ?></p>
            <p><strong>Full Name:</strong> <?= htmlspecialchars($enrollmentDetails['name']); ?></p>
            <p><strong>DOB:</strong> <?= htmlspecialchars($enrollmentDetails['dob']); ?> (Age: <?= htmlspecialchars((string) $enrollmentDetails['age']); ?>)</p>
            <p><strong>Sex:</strong> <?= ucfirst(htmlspecialchars($enrollmentDetails['gender'])); ?></p>
            <div style="background:#f8f9fa; padding:15px; border-radius:5px; margin-top:15px;">
                <p style="margin-top:0;"><strong>Archived Files:</strong></p>
                <ul style="margin-bottom:0;">
                    <li>BC: <?= htmlspecialchars($enrollmentDetails['docs']['BC']); ?></li>
                    <li>Med Record: <?= htmlspecialchars($enrollmentDetails['docs']['Rec']); ?></li>
                    <li>Med Report: <?= htmlspecialchars($enrollmentDetails['docs']['Rep']); ?></li>
                </ul>
            </div>
            <div style="text-align:center; margin-top: 25px;">
                <button onclick="window.print()" style="padding:10px 20px; background:#17a2b8; color:#fff; border:none; border-radius:5px; cursor:pointer;"><i class="fas fa-print"></i> Print Copy</button>
                <a href="parent_profile.php" style="padding:10px 20px; background:#6c757d; color:#fff; text-decoration:none; border-radius:5px; margin-left:10px;">Return Home</a>
            </div>
        </div>
    <?php else: ?>
        <div class="enroll-panel">
            <h2 style="text-align:center; margin-bottom: 20px;">New Child Enrollment</h2>
            <p style="text-align:center; color:#888; margin-top:-10px; margin-bottom:20px;">This centre enrols children aged 4 and under.</p>

            <?php if (!empty($systemAlerts)): ?>
                <div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:5px; margin-bottom:20px;">
                    <ul style="margin:0; padding-left:20px;">
                        <?php foreach ($systemAlerts as $alert): ?>
                            <li><?= htmlspecialchars($alert) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="form-group">
                    <label>Legal Name *</label>
                    <input type="text" name="child_name" required pattern="[A-Za-z'\-\s]{2,100}"
                           title="Letters, spaces, hyphens and apostrophes only"
                           value="<?= htmlspecialchars($_POST['child_name'] ?? '') ?>"
                           class="form-control" placeholder="First and Last Name">
                </div>

                <div style="display:flex; gap:15px;">
                    <div class="form-group" style="flex:1;">
                        <label>Date of Birth *</label>
                        <input type="date" name="dob" required class="form-control"
                               max="<?= date('Y-m-d') ?>"
                               value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Sex *</label>
                        <select name="gender" required class="form-control">
                            <option value="">-- Choose --</option>
                            <option value="male" <?= (($_POST['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= (($_POST['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Known Medical Conditions</label>
                    <textarea name="medical_history" rows="3" class="form-control" placeholder="Allergies, asthma, etc."><?= htmlspecialchars($_POST['medical_history'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Requires Special Accommodations?</label>
                    <select name="special_needs" class="form-control">
                        <option value="No" <?= (($_POST['special_needs'] ?? 'No') === 'No') ? 'selected' : '' ?>>No</option>
                        <option value="Yes" <?= (($_POST['special_needs'] ?? '') === 'Yes') ? 'selected' : '' ?>>Yes</option>
                    </select>
                </div>

                <div style="background:#e9ecef; padding:15px; border-radius:5px; margin-bottom:20px;">
                    <h4 style="margin-top:0; margin-bottom:15px;">Required Documentation</h4>
                    <div class="form-group">
                        <label>Birth Certificate (PDF/IMG) *</label>
                        <input type="file" name="birth_certificate" accept=".pdf,.jpg,.jpeg,.png" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Immunization/Medical Records *</label>
                        <input type="file" name="medical_records" accept=".pdf,.jpg,.jpeg,.png" required class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Additional Doctor Reports (Optional)</label>
                        <input type="file" name="medical_reports" accept=".pdf,.jpg,.jpeg,.png" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Submit Enrollment Application</button>
            </form>
        </div>
    <?php endif; ?>
</main>

<script src="assets/js/script.js"></script>
</body>
</html>
