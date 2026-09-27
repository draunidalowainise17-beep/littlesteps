<?php
// Check if a delete request has been made
if (isset($_GET['delete_category'])) {
    $category_id = $_GET['delete_category'];

    // Ensure category ID is a valid integer
    if (filter_var($category_id, FILTER_VALIDATE_INT)) {
        // Perform delete operation
        $sql = "DELETE FROM category WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $category_id);
        
        if ($stmt->execute()) {
            echo '<script>alert("Category deleted successfully."); window.location.href="admin_dashboard.php";</script>';
        } else {
            echo '<script>alert("Error deleting category."); window.location.href="admin_dashboard.php";</script>';
        }
    }
}
?>
