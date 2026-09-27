<?php
require_once 'config.php';

// Allow dynamic viewing if passed via URL, fallback to 4 for testing
$targetChildId = $_GET['child_id'] ?? 4; 

try {
    // 1. Core Info
    $kidQuery = $conn->prepare("SELECT * FROM child WHERE child_id = :id");
    $kidQuery->execute([':id' => $targetChildId]);
    $kidData = $kidQuery->fetch();

    if (!$kidData) {
        die("<div style='padding:20px; text-align:center;'><h2>Child record not found.</h2></div>");
    }

    // 2. Academic/Learning
    $eduQuery = $conn->prepare("SELECT * FROM learning WHERE child_id = :id");
    $eduQuery->execute([':id' => $targetChildId]);
    $eduLogs = $eduQuery->fetchAll();

    // 3. Nutrition/Meals
    $foodQuery = $conn->prepare("SELECT * FROM meal WHERE child_id = :id");
    $foodQuery->execute([':id' => $targetChildId]);
    $foodLogs = $foodQuery->fetchAll();

    // 4. Transit/Pick-Drop
    $transitQuery = $conn->prepare("SELECT * FROM pickdrop WHERE child_id = :id");
    $transitQuery->execute([':id' => $targetChildId]);
    $transitLogs = $transitQuery->fetchAll();

    // 5. General Activities
    $actQuery = $conn->prepare("SELECT * FROM activity WHERE child_id = :id");
    $actQuery->execute([':id' => $targetChildId]);
    $actLogs = $actQuery->fetchAll();

} catch (PDOException $err) {
    die("Database transaction failed: " . $err->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Dossier | Little Steps</title>
    <link rel="stylesheet" href="assets/css/caregiver_profile.css">
    <link rel="stylesheet" href="assets/css/child_profile.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="profile-container">
        <h1 class="page-title" style="text-align:center;">Comprehensive Child Dossier</h1>

        <div class="dossier-layout">
            
            <!-- Core Information -->
            <section class="dossier-block highlight-block">
                <h2 class="block-title">Demographics & Medical</h2>
                <div class="data-grid">
                    <div class="data-card primary-card">
                        <ul style="list-style:none; padding:0; line-height:1.8;">
                            <li><strong>Legal Name:</strong> <?= htmlspecialchars($kidData['name']); ?></li>
                            <li><strong>DOB:</strong> <?= htmlspecialchars($kidData['date_of_birth']); ?></li>
                            <li><strong>Sex:</strong> <?= ucfirst(htmlspecialchars($kidData['gender'])); ?></li>
                            <li><strong>Medical History:</strong> <?= htmlspecialchars($kidData['medical_history'] ?: 'Clear'); ?></li>
                            <li><strong>Special Needs:</strong> <?= htmlspecialchars($kidData['special_needs']); ?></li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Learning Logs -->
            <section class="dossier-block">
                <h2 class="block-title">Academic Tracking</h2>
                <div class="data-grid">
                    <?php if (count($eduLogs) > 0): ?>
                        <?php foreach ($eduLogs as $edu): ?>
                            <div class="data-card">
                                <h4><?= htmlspecialchars($edu['learning_type']); ?></h4>
                                <p><em><?= htmlspecialchars($edu['date']); ?></em></p>
                                <p><?= htmlspecialchars($edu['progress_notes']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No academic logs recorded yet.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Meal Logs -->
            <section class="dossier-block">
                <h2 class="block-title">Dietary Records</h2>
                <div class="data-grid">
                    <?php if (count($foodLogs) > 0): ?>
                        <?php foreach ($foodLogs as $meal): ?>
                            <div class="data-card">
                                <h4><?= htmlspecialchars($meal['meal_name']); ?></h4>
                                <p><em><?= htmlspecialchars($meal['date']); ?></em></p>
                                <p><strong>Allergens:</strong> <?= htmlspecialchars($meal['allergies'] ?: 'None'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No dietary logs recorded yet.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Pick/Drop Logs -->
            <section class="dossier-block">
                <h2 class="block-title">Transit Logs</h2>
                <div class="data-grid">
                    <?php if (count($transitLogs) > 0): ?>
                        <?php foreach ($transitLogs as $transit): ?>
                            <div class="data-card">
                                <p><strong>Status:</strong> <?= htmlspecialchars($transit['status']); ?></p>
                                <p><strong>Timestamp:</strong> <?= htmlspecialchars($transit['time']); ?></p>
                                <p><strong>Alert:</strong> <?= htmlspecialchars($transit['notification_status']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No transit logs available.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Daily Activities -->
            <section class="dossier-block">
                <h2 class="block-title">Activity Engagement</h2>
                <div class="data-grid">
                    <?php if (count($actLogs) > 0): ?>
                        <?php foreach ($actLogs as $act): ?>
                            <div class="data-card">
                                <h4><?= htmlspecialchars($act['activity_type']); ?></h4>
                                <p><em><?= htmlspecialchars($act['date']); ?> @ <?= htmlspecialchars($act['time']); ?></em></p>
                                <p><?= htmlspecialchars($act['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No activity data logged.</p>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>