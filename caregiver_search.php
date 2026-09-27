<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caregiver Directory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/caregivers.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="directory-section">
        <h1 class="page-title" style="text-align: center;">Staff Directory</h1>

        <form action="" method="POST" class="search-bar-wrapper" style="text-align: center; margin-bottom: 40px;">
            <input 
                type="text" 
                name="filter_name" 
                placeholder="Lookup by name..." 
                value="<?= htmlspecialchars($_POST['filter_name'] ?? ''); ?>" 
                style="padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc;"
            >
            <button type="submit" style="padding: 10px 15px; background: #f9ba60; border: none; border-radius: 5px; cursor: pointer;"><i class="fas fa-search"></i></button>
        </form>

        <div class="card-grid" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
            <?php
            try {
                // Dynamic SQL Generation (Replaces the repetitive if/else structure)
                $sql = "SELECT * FROM caregivers";
                $params = [];

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['filter_name'])) {
                    $sql .= " WHERE name LIKE :term";
                    $params[':term'] = trim($_POST['filter_name']) . '%';
                }

                $stmtData = $conn->prepare($sql);
                $stmtData->execute($params);
                $records = $stmtData->fetchAll(PDO::FETCH_ASSOC);

                if (count($records) > 0) {
                    foreach ($records as $person) {
                        $imgSource = !empty($person['profile_image']) ? 'uploads/avatars/' . htmlspecialchars($person['profile_image']) : 'assets/media/default-avatar.jpg';
                        ?>
                        <div class="profile-card" style="border: 1px solid #eee; padding: 20px; border-radius: 8px; width: 280px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                            <img src="<?= $imgSource; ?>" alt="<?= htmlspecialchars($person['name']); ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 15px;"/>
                            <h3 style="margin-bottom: 5px;"><?= htmlspecialchars($person['name']); ?></h3>
                            <span style="color: #666; font-size: 0.9rem; display: block; margin-bottom: 15px;"><?= htmlspecialchars($person['qualification']); ?></span>
                            
                            <p style="font-size: 0.9rem; margin-bottom: 5px;"><strong>Experience:</strong> <?= htmlspecialchars($person['experience']); ?> yrs</p>
                            <p style="font-size: 0.9rem; margin-bottom: 15px;"><strong>Skills:</strong> <?= htmlspecialchars($person['special_training']); ?></p>
                            
                            <form action="caregiver_profile.php" method="POST">
                                <input type="hidden" name="caregiver_id" value="<?= $person['caregiver_id']; ?>">
                                <button type="submit" name="caregiver_fetch" class="btn-primary" style="width: 100%; padding: 10px; background: #e9768f; color: white; border: none; border-radius: 5px; cursor: pointer;">View Profile</button>
                            </form>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p style="text-align: center; width: 100%; color: #666;">No staff members matched your criteria.</p>';
                }
            } catch (Exception $e) {
                echo '<p style="color: red; text-align: center;">System warning: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            ?>
        </div>
    </section>
</body>
</html>