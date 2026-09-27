<?php
include_once "../config.php";

if (isset($_POST['child_id'])) {
    $child_id = $_POST['child_id'];

    // Check if the child exists before deleting
    $sql_check = "SELECT COUNT(*) FROM child WHERE child_id = :child_id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':child_id', $child_id, PDO::PARAM_INT);
    $stmt_check->execute();
    $child_count = $stmt_check->fetchColumn();

    if ($child_count == 0) {
        echo "Child not found!";
        exit();
    }

    // Prepare the SQL query to delete the child
    $sql = "DELETE FROM child WHERE child_id = :child_id";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':child_id', $child_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo 'Success';
        } else {
            echo 'Error: Delete failed';
        }
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();  // Display actual error message
    }
}
?>
