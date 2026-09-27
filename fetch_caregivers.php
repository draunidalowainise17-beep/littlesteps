<?php
require_once 'config.php';

if (isset($_POST['query'])) {
    $searchTerm = trim($_POST['query']);

    try {
        $sql = "SELECT * FROM caregivers";
        $params = [];

        if (!empty($searchTerm)) {
            $sql .= " WHERE name LIKE :term";
            $params[':term'] = "%" . $searchTerm . "%";
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        if (count($results) > 0) {
            foreach ($results as $row) {
                ?>
                <div class="ajax-card">
                    <h3 class="ajax-title"><?= htmlspecialchars($row['name']); ?></h3>
                    <ul class="ajax-details">
                        <li><strong>Credential:</strong> <?= htmlspecialchars($row['qualification']); ?></li>
                        <li><strong>Tenure:</strong> <?= htmlspecialchars($row['experience']); ?> years</li>
                        <li><strong>Specialty:</strong> <?= htmlspecialchars($row['special_training']); ?></li>
                    </ul>
                </div>
                <?php
            }
        } else {
            echo '<p class="text-muted">No matching staff found.</p>';
        }
    } catch (PDOException $err) {
        echo '<p class="text-danger">Search error occurred.</p>';
    }
}
?>