<?php
session_start();
require_once 'config.php';

$activeUser = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Caregivers | Little Steps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/caregivers.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="staff-directory">
        <h1 class="page-title">Meet Our Staff</h1>

        <form action="caregiver_search.php" method="POST" class="search-form-wrapper">
            <input type="text" name="filter_name" maxlength="100" placeholder="Find a caregiver..." required class="form-control">
            <button type="submit" name="search_btn" class="btn-search"><i class="fas fa-search"></i></button>
        </form>

        <div class="card-grid">
            <!-- Join Team Card -->
            <div class="staff-card highlight-card">
                <h3>Join Our Team</h3>
                <p>Are you a certified caregiver? Log in to your portal.</p>
                <a href="caregiver_login.php" class="btn-primary" style="margin-top:15px;">Staff Portal</a>
            </div>

            <?php
            try {
                $staffQuery = $conn->query("SELECT * FROM caregivers");
                $staffList = $staffQuery->fetchAll();

                if (count($staffList) > 0) {
                    foreach ($staffList as $person) {
                        // Point directly to uploaded_files/ where the images live
                        $avatarPath = (!empty($person['profile_image']) && file_exists('uploaded_files/' . $person['profile_image']))
                            ? 'uploaded_files/' . htmlspecialchars($person['profile_image'])
                            : 'assets/media/caregiver_1.png';
            ?>
                        <div class="staff-card">
                            <div class="avatar-wrap">
                                <img src="<?= $avatarPath; ?>" 
                                     alt="<?= htmlspecialchars($person['name']); ?>" 
                                     class="avatar-img"
                                     onerror="this.src='assets/media/caregiver_1.png'" />
                            </div>
                            <div class="header-info">
                                <h3><?= htmlspecialchars($person['name']); ?></h3>
                                <span class="badge"><?= htmlspecialchars($person['qualification']); ?></span>
                            </div>
                            <div class="card-body">
                                <p><strong>Experience:</strong> <?= htmlspecialchars($person['experience']); ?> years</p>
                                <p><strong>Expertise:</strong> <?= htmlspecialchars($person['special_training'] ?: 'Early Childhood'); ?></p>
                            </div>
                        </div>
            <?php
                    }
                } else {
                    echo '<p class="alert-empty">Currently no staff records available.</p>';
                }
            } catch (PDOException $err) {
                echo '<p class="alert-error">Data retrieval failed.</p>';
            }
            ?>
        </div>
    </section>
</body>
</html>